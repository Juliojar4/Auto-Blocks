<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class SyncBlocksCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'blocks:sync {--force : Forçar sincronização mesmo se já existir}';

    /**
     * The console command description.
     */
    protected $description = 'Sincronizar blocos existentes com BlockManager e blocks.js';

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
        $this->info("🔄 Sincronizando blocos existentes...");
        
        // Descobrir blocos na pasta resources/blocks
        $blocksPath = resource_path('blocks');
        
        if (!$this->files->exists($blocksPath)) {
            $this->error("❌ Pasta de blocos não encontrada: {$blocksPath}");
            return 1;
        }

        $blockFolders = $this->files->directories($blocksPath);
        $blockNames = [];
        
        foreach ($blockFolders as $folder) {
            $blockName = basename($folder);
            $blockNames[] = $blockName;
        }
        
        if (empty($blockNames)) {
            $this->warn("⚠️  Nenhum bloco encontrado para sincronizar");
            return 0;
        }
        
        $this->line("📂 Blocos encontrados: " . implode(', ', $blockNames));
        $this->newLine();
        
        // Sincronizar BlockManager
        $this->syncBlockManager($blockNames);
        
        // Sincronizar blocks.js
        $this->syncBlocksJs($blockNames);
        
        $this->newLine();
        $this->info("✅ Sincronização concluída!");
        
        return 0;
    }

    /**
     * Sincronizar BlockManager.php
     */
    protected function syncBlockManager(array $blockNames): void
    {
        $managerPath = app_path('Blocks/BlockManager.php');
        $content = $this->files->get($managerPath);
        
        // Extrair blocos atuais do array
        $pattern = '/protected\s+array\s+\$blocks\s*=\s*\[(.*?)\];/s';
        
        if (preg_match($pattern, $content, $matches)) {
            $arrayContent = $matches[1];
            
            // Extrair blocos existentes
            preg_match_all("/'([^']+)'/", $arrayContent, $existingMatches);
            $existingBlocks = $existingMatches[1];
            
            // Determinar blocos a adicionar
            $blocksToAdd = array_diff($blockNames, $existingBlocks);
            
            if (empty($blocksToAdd) && !$this->option('force')) {
                $this->line("✅ BlockManager.php já está sincronizado");
                return;
            }
            
            // Reorganizar array completo
            $allBlocks = array_unique(array_merge($existingBlocks, $blockNames));
            sort($allBlocks);
            
            // Gerar novo array
            $newArrayContent = "\n";
            foreach ($allBlocks as $block) {
                $newArrayContent .= "        '{$block}',\n";
            }
            $newArrayContent .= "        // Adicione novos blocos aqui - apenas o nome da pasta!\n    ";
            
            $newContent = str_replace(
                $matches[0],
                "protected array \$blocks = [{$newArrayContent}];",
                $content
            );
            
            $this->files->put($managerPath, $newContent);
            
            if (!empty($blocksToAdd)) {
                $this->line("✅ BlockManager.php atualizado com: " . implode(', ', $blocksToAdd));
            } else {
                $this->line("✅ BlockManager.php reorganizado");
            }
        } else {
            $this->error("❌ Padrão do array \$blocks não encontrado no BlockManager.php");
        }
    }

    /**
     * Sincronizar blocks.js
     */
    protected function syncBlocksJs(array $blockNames): void
    {
        $blocksJsPath = resource_path('js/blocks.js');
        $content = $this->files->get($blocksJsPath);
        
        // Extrair imports existentes
        preg_match_all("/import\s+['\"]\.\.\/blocks\/([^\/]+)\/block\.jsx['\"]/", $content, $matches);
        $existingImports = $matches[1];
        
        // Determinar imports a adicionar
        $importsToAdd = array_diff($blockNames, $existingImports);
        
        if (empty($importsToAdd) && !$this->option('force')) {
            $this->line("✅ blocks.js já está sincronizado");
            return;
        }
        
        // Reorganizar imports
        $allImports = array_unique(array_merge($existingImports, $blockNames));
        sort($allImports);
        
        // Gerar novos imports
        $newImports = "// Importar blocos automaticamente\n";
        foreach ($allImports as $block) {
            $newImports .= "import '../blocks/{$block}/block.jsx';\n";
        }
        
        // Substituir seção de imports
        $pattern = '/(\/\/ Importar blocos automaticamente\s*\n)(.*?)(\/\/ Adicionar estilos globais)/s';
        
        if (preg_match($pattern, $content, $matches)) {
            $newContent = str_replace(
                $matches[0],
                $newImports . "\n" . $matches[3],
                $content
            );
            
            $this->files->put($blocksJsPath, $newContent);
            
            if (!empty($importsToAdd)) {
                $this->line("✅ blocks.js atualizado com: " . implode(', ', $importsToAdd));
            } else {
                $this->line("✅ blocks.js reorganizado");
            }
        } else {
            $this->error("❌ Padrão de imports não encontrado no blocks.js");
        }
    }
}
