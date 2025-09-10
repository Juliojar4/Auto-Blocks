<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class MakeBlockCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:block {name : The block name}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new custom Gutenberg block';

    /**
     * Filesystem instance
     */
    protected Filesystem $files;

    /**
     * Create a new command instance.
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $blockSlug = Str::kebab($name);
        $blockTitle = Str::title(str_replace(['-', '_'], ' ', $name));
        
        $this->info("🎨 Creating block: {$blockTitle}");

        try {
            $this->createBlock($blockSlug, $blockTitle);
            $this->updateBlocksJs($blockSlug);
            $this->updateBlockManager($blockSlug);
            
            $this->line("");
            $this->info("✅ Block '{$blockTitle}' created successfully!");
            $this->line("📁 Location: resources/blocks/{$blockSlug}");
            $this->line("🏗️  Next: Run 'npm run build' to compile");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("❌ Error creating block: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Create the block structure
     */
    protected function createBlock(string $slug, string $title): void
    {
        $blockPath = resource_path("blocks/{$slug}");
        
        // Create block directory
        if (!$this->files->isDirectory($blockPath)) {
            $this->files->makeDirectory($blockPath, 0755, true);
        }

        // Create block.json
        $blockJson = [
            'name' => "sage/{$slug}",
            'title' => $title,
            'category' => 'design',
            'icon' => 'block-default',
            'description' => "Custom {$title} block",
            'textdomain' => 'sage',
            'editorScript' => 'file:./block.jsx',
            'style' => 'file:./block.css',
            'render' => 'file:./block.php'
        ];

        $this->files->put(
            "{$blockPath}/block.json", 
            json_encode($blockJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        // Create block.jsx
        $jsContent = "import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText } from '@wordpress/block-editor';

registerBlockType('sage/{$slug}', {
    edit: ({ attributes, setAttributes }) => {
        const { content } = attributes;
        const blockProps = useBlockProps();
        
        return (
            <div {...blockProps} className=\"{$slug}-block-editor bg-gradient-to-br from-blue-50 to-purple-50 border-2 border-dashed border-purple-200 rounded-xl p-8 \">
                <h3 className=\"text-lg font-bold mb-2\">{$title}</h3>
                <RichText
                    tagName=\"div\"
                    value={content}
                    onChange={(newContent) => setAttributes({ content: newContent })}
                    placeholder=\"Enter your content...\"
                />
            </div>
        );
    },
    
    save: () => null // Server-side rendering
});";

        $this->files->put("{$blockPath}/block.jsx", $jsContent);

        // Create block.php
        $phpContent = "<?php
// Server-side rendering for {$title} block

\$content = \$attributes['content'] ?? '';
\$block_data = [
    'title' => '{$title}',
    'content' => \$content,
    'slug' => '{$slug}'
];

echo view('blocks.{$slug}', \$block_data)->render();";

        $this->files->put("{$blockPath}/block.php", $phpContent);

        // Create block.css
        $cssContent = ".{$slug}-block {
    padding: 1.5rem;
    margin: 1rem 0;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.{$slug}-block h3 {
    margin: 0 0 1rem 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #374151;
}

.{$slug}-block-editor {
    background: #f9fafb;
    border: 2px dashed #d1d5db !important;
}

.{$slug}-block-editor:hover {
    border-color: #3b82f6 !important;
}";

        $this->files->put("{$blockPath}/block.css", $cssContent);

        // Create Blade template
        $bladePath = resource_path("views/blocks");
        if (!$this->files->isDirectory($bladePath)) {
            $this->files->makeDirectory($bladePath, 0755, true);
        }

        $bladeContent = "<div class=\"{$slug}-block\">
    <h3>{{ \$title }}</h3>
    <div class=\"block-content\">
        {!! wp_kses_post(\$content) !!}
    </div>
</div>";

        $this->files->put("{$bladePath}/{$slug}.blade.php", $bladeContent);
    }

    /**
     * Update blocks.js to include new block import
     */
    protected function updateBlocksJs(string $slug): void
    {
        $blocksJsPath = resource_path('js/blocks.js');
        
        if (!$this->files->exists($blocksJsPath)) {
            // Create blocks.js if it doesn't exist
            $initialContent = "// Auto Blocks - Block imports are automatically added here\n// AUTO-IMPORTS: Created blocks are automatically imported below this line\n";
            $this->files->put($blocksJsPath, $initialContent);
            $this->line("✅ Created: blocks.js");
        }
        
        $content = $this->files->get($blocksJsPath);
        $importLine = "import '../blocks/{$slug}/block.jsx';";
        
        // Check if import already exists
        if (strpos($content, $importLine) !== false) {
            $this->line("⚠️  Import for '{$slug}' already exists in blocks.js");
            return;
        }
        
        // Add import after AUTO-IMPORTS comment
        if (strpos($content, '// AUTO-IMPORTS:') !== false) {
            $content = str_replace(
                "// AUTO-IMPORTS: Created blocks are automatically imported below this line",
                "// AUTO-IMPORTS: Created blocks are automatically imported below this line\n{$importLine}",
                $content
            );
        } else {
            // If no AUTO-IMPORTS comment, add it and the import
            $autoImportSection = "\n// AUTO-IMPORTS: Created blocks are automatically imported below this line\n{$importLine}\n";
            $content = $content . $autoImportSection;
        }
        
        $this->files->put($blocksJsPath, $content);
        $this->line("✅ Added import for '{$slug}' to blocks.js");
    }

    /**
     * Update BlockManager to include new block
     */
    protected function updateBlockManager(string $slug): void
    {
        $managerPath = app_path('Blocks/BlockManager.php');
        
        if (!$this->files->exists($managerPath)) {
            $this->error("❌ BlockManager.php not found");
            return;
        }
        
        $content = $this->files->get($managerPath);
        
        // Check if block already exists
        if (strpos($content, "'{$slug}'") !== false) {
            $this->line("⚠️  Block '{$slug}' already exists in BlockManager");
            return;
        }
        
        // Pattern to find the blocks array
        $pattern = '/(protected\s+array\s+\$blocks\s*=\s*\[\s*)(.*?)(\s*];)/s';
        
        if (preg_match($pattern, $content, $matches)) {
            $arrayStart = $matches[1];
            $arrayContent = $matches[2];
            $arrayEnd = $matches[3];
            
            // Remove the comment and add the new block
            $cleanContent = trim(str_replace(['// Add your blocks here', '// Add new blocks here'], '', $arrayContent));
            
            if (empty($cleanContent)) {
                // First block
                $newArrayContent = "\n        '{$slug}',\n    ";
            } else {
                // Add to existing blocks
                $newArrayContent = $arrayContent . "\n        '{$slug}',";
            }
            
            $newContent = str_replace(
                $matches[0],
                $arrayStart . $newArrayContent . $arrayEnd,
                $content
            );
            
            $this->files->put($managerPath, $newContent);
            $this->line("✅ Added '{$slug}' to BlockManager.php");
        } else {
            $this->error("❌ Could not find blocks array in BlockManager.php");
        }
    }
}
