<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class InstallPrebuiltBlocksCommand extends Command
{
    protected $signature = 'install:prebuilt-blocks {--list : Show which blocks will be installed and exit}';
    protected $description = 'Install existing prebuilt blocks from stubs/blocks into the project.';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): int
    {
        $stubsPath = base_path('stubs/blocks');

        if (!$this->files->isDirectory($stubsPath)) {
            $this->error("❌ Stub folder not found: {$stubsPath}");
            return self::FAILURE;
        }

        $blocks = array_filter(scandir($stubsPath), fn($item) => $item !== '.' && $item !== '..');

        if ($this->option('list')) {
            $this->line("\n🧱 Prebuilt blocks available:");
            foreach ($blocks as $block) {
                $this->line("  • {$block}");
            }
            return self::SUCCESS;
        }

        if (empty($blocks)) {
            $this->warn("⚠️  No prebuilt blocks found in {$stubsPath}");
            return self::SUCCESS;
        }

        foreach ($blocks as $blockDir) {
            $blockPath = "{$stubsPath}/{$blockDir}";
            $blockJsonPath = "{$blockPath}/block.json";

            if (!$this->files->exists($blockJsonPath)) {
                $this->warn("⚠️  Skipping '{$blockDir}' — missing block.json");
                continue;
            }

            $meta = json_decode($this->files->get($blockJsonPath), true);
            if (!$meta || empty($meta['name']) || empty($meta['title'])) {
                $this->warn("⚠️  Skipping '{$blockDir}' — invalid block.json");
                continue;
            }

            $slug = Str::after($meta['name'], '/');
            $title = $meta['title'];
            $bladeFile = $meta['blade'] ?? 'template.blade.php';
            $bladeSource = "{$blockPath}/{$bladeFile}";

            $this->line("Installing block: {$title} ({$slug})");

            // Destination paths
            $blockFolder = resource_path("blocks/{$slug}");
            $viewsPath = resource_path("views/blocks");

            // Ensure directories exist
            $this->files->ensureDirectoryExists($blockFolder);
            $this->files->ensureDirectoryExists($viewsPath);

            // Copy block files
            foreach (['block.json', 'block.php', 'block.jsx', 'block.css'] as $file) {
                $src = "{$blockPath}/{$file}";
                if ($this->files->exists($src)) {
                    $this->files->copy($src, "{$blockFolder}/{$file}");
                }
            }

            // Copy Blade template
            if ($this->files->exists($bladeSource)) {
                $this->files->copy($bladeSource, "{$viewsPath}/{$slug}.blade.php");
            } else {
                $this->warn("⚠️  Blade file not found for '{$slug}' ({$bladeFile})");
            }

            // Update JS imports
            $this->updateBlocksJs($slug);

            // Update BlockManager
            $this->updateBlockManager($slug);

            $this->info("✅ Installed block: {$title}");
        }

        $this->line('');
        $this->info('🎉 All prebuilt blocks installed successfully!');
        return self::SUCCESS;
    }

    protected function updateBlocksJs(string $slug): void
    {
        $blocksJsPath = resource_path('js/blocks.js');

        if (!$this->files->exists($blocksJsPath)) {
            $initialContent = "// Auto Blocks\n// AUTO-IMPORTS: Created blocks are automatically imported below this line\n";
            $this->files->put($blocksJsPath, $initialContent);
            $this->line("✅ Created: blocks.js");
        }

        $content = $this->files->get($blocksJsPath);
        $importLine = "import '../blocks/{$slug}/block.jsx';";

        if (strpos($content, $importLine) !== false) {
            $this->line("⚠️  '{$slug}' already imported in blocks.js");
            return;
        }

        $content = str_replace(
            "// AUTO-IMPORTS: Created blocks are automatically imported below this line",
            "// AUTO-IMPORTS: Created blocks are automatically imported below this line\n{$importLine}",
            $content
        );

        $this->files->put($blocksJsPath, $content);
        $this->line("✅ Added '{$slug}' import to blocks.js");
    }

    protected function updateBlockManager(string $slug): void
    {
        $managerPath = app_path('Blocks/BlockManager.php');

        if (!$this->files->exists($managerPath)) {
            $this->error("❌ BlockManager.php not found");
            return;
        }

        $content = $this->files->get($managerPath);

        if (strpos($content, "'{$slug}'") !== false) {
            $this->line("⚠️  '{$slug}' already registered in BlockManager");
            return;
        }

        $pattern = '/(protected\s+array\s+\$blocks\s*=\s*\[\s*)(.*?)(\s*];)/s';

        if (preg_match($pattern, $content, $matches)) {
            $arrayStart = $matches[1];
            $arrayContent = $matches[2];
            $arrayEnd = $matches[3];

            $newArrayContent = $arrayContent . "\n        '{$slug}',";

            $newContent = str_replace(
                $matches[0],
                $arrayStart . $newArrayContent . $arrayEnd,
                $content
            );

            $this->files->put($managerPath, $newContent);
            $this->line("✅ Registered '{$slug}' in BlockManager");
        } else {
            $this->error("❌ Could not find \$blocks array in BlockManager.php");
        }
    }
}
