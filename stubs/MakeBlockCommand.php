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
    protected $signature = 'make:block {name : O nome do bloco} 
                            {--description= : Descrição do bloco}
                            {--category=design : Categoria do bloco}
                            {--icon=block-default : Ícone do bloco}
                            {--with-js : Incluir arquivo JavaScript específico}
                            {--with-css : Incluir arquivo CSS específico}';

    /**
     * The console command description.
     */
    protected $description = 'Criar um novo bloco customizado com toda a estrutura necessária';

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
        $className = Str::studly($name);
        
        $this->info("🚀 Criando bloco: {$blockTitle}");
        
        // Criar diretório do bloco
        $blockPath = resource_path("blocks/{$blockSlug}");
        if ($this->files->exists($blockPath)) {
            $this->error("❌ Bloco '{$blockSlug}' já existe!");
            return 1;
        }
        
        $this->files->makeDirectory($blockPath, 0755, true);
        
        // Criar arquivos do bloco
        $this->createBlockJson($blockPath, $blockSlug, $blockTitle);
        $this->createBlockJsx($blockPath, $blockSlug, $blockTitle, $className);
        $this->createBlockPhp($blockPath, $blockSlug, $blockTitle);
        $this->createBladeTemplate($blockSlug, $blockTitle);
        
        // Criar arquivos opcionais
        if ($this->option('with-js')) {
            $this->createBlockJs($blockPath, $blockSlug, $className);
        }
        
        if ($this->option('with-css')) {
            $this->createBlockCss($blockPath, $blockSlug);
        }
        
        // Atualizar arquivos de configuração
        $this->updateBlockManager($blockSlug);
        $this->updateBlocksJs($blockSlug);
        
        $this->displaySummary($blockSlug, $blockTitle);
        
        return 0;
    }

    /**
     * Criar arquivo block.json
     */
    protected function createBlockJson(string $path, string $slug, string $title): void
    {
        $description = $this->option('description') ?: "Bloco customizado {$title}";
        $category = $this->option('category');
        $icon = $this->option('icon');
        
        $content = [
            '$schema' => 'https://schemas.wp.org/trunk/block.json',
            'apiVersion' => 3,
            'name' => "doctailwind/{$slug}",
            'version' => '1.0.0',
            'title' => $title,
            'category' => $category,
            'icon' => $icon,
            'description' => $description,
            'keywords' => [Str::lower($title), 'bloco', 'customizado'],
            'textdomain' => 'doctailwind',
            'attributes' => [
                'content' => [
                    'type' => 'string',
                    'default' => 'Conteúdo do bloco...'
                ]
            ],
            'render' => 'file:./block.php'
        ];
        
        $this->files->put(
            "{$path}/block.json",
            json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        
        $this->line("✅ Criado: block.json");
    }

    /**
     * Criar arquivo block.jsx
     */
    protected function createBlockJsx(string $path, string $slug, string $title, string $className): void
    {
        $content = $this->getStub('block.jsx.stub', [
            'BLOCK_SLUG' => $slug,
            'BLOCK_TITLE' => $title,
            'CLASS_NAME' => $className,
            'NAMESPACE' => 'doctailwind'
        ]);
        
        $this->files->put("{$path}/block.jsx", $content);
        $this->line("✅ Criado: block.jsx");
    }

    /**
     * Criar arquivo block.php
     */
    protected function createBlockPhp(string $path, string $slug, string $title): void
    {
        $content = $this->getStub('block.php.stub', [
            'BLOCK_SLUG' => $slug,
            'BLOCK_TITLE' => $title
        ]);
        
        $this->files->put("{$path}/block.php", $content);
        $this->line("✅ Criado: block.php");
    }

    /**
     * Criar template Blade
     */
    protected function createBladeTemplate(string $slug, string $title): void
    {
        $bladePath = resource_path("views/blocks");
        
        // Criar diretório blocks se não existir
        if (!$this->files->exists($bladePath)) {
            $this->files->makeDirectory($bladePath, 0755, true);
        }
        
        $content = $this->getBladeStub([
            'BLOCK_SLUG' => $slug,
            'BLOCK_TITLE' => $title
        ]);
        
        $this->files->put("{$bladePath}/{$slug}.blade.php", $content);
        $this->line("✅ Criado: resources/views/blocks/{$slug}.blade.php");
    }

    /**
     * Criar arquivo block.js
     */
    protected function createBlockJs(string $path, string $slug, string $className): void
    {
        $content = $this->getStub('block.js.stub', [
            'BLOCK_SLUG' => $slug,
            'CLASS_NAME' => $className
        ]);
        
        $this->files->put("{$path}/block.js", $content);
        $this->line("✅ Criado: block.js");
    }

    /**
     * Criar arquivo block.css
     */
    protected function createBlockCss(string $path, string $slug): void
    {
        $content = $this->getStub('block.css.stub', [
            'BLOCK_SLUG' => $slug
        ]);
        
        $this->files->put("{$path}/block.css", $content);
        $this->line("✅ Criado: block.css");
    }

    /**
     * Atualizar BlockManager
     */
    protected function updateBlockManager(string $slug): void
    {
        $managerPath = app_path('Blocks/BlockManager.php');
        $content = $this->files->get($managerPath);
        
        // Verificar se o bloco já existe
        if (strpos($content, "'$slug'") !== false) {
            $this->warn("⚠️  Bloco '{$slug}' já existe no BlockManager.php");
            return;
        }
        
        // Procurar pelo array $blocks e adicionar o novo bloco
        $pattern = '/(protected\s+array\s+\$blocks\s*=\s*\[)(.*?)(\/\/\s*Adicione novos blocos aqui.*?\n\s*\];)/s';
        
        if (preg_match($pattern, $content, $matches)) {
            $beforeComment = $matches[2];
            $comment = $matches[3];
            
            // Adicionar o novo bloco antes do comentário
            $newEntry = "        '{$slug}',\n        ";
            $newContent = str_replace(
                $matches[0],
                $matches[1] . $beforeComment . $newEntry . $comment,
                $content
            );
            
            $this->files->put($managerPath, $newContent);
            $this->line("✅ Atualizado: BlockManager.php");
        } else {
            $this->error("❌ Não foi possível atualizar BlockManager.php - padrão não encontrado");
        }
    }

    /**
     * Atualizar blocks.js
     */
    protected function updateBlocksJs(string $slug): void
    {
        $blocksJsPath = resource_path('js/blocks.js');
        $content = $this->files->get($blocksJsPath);
        
        // Linha de import para adicionar
        $importLine = "import '../blocks/{$slug}/block.jsx';";
        
        // Verificar se o import já existe
        if (strpos($content, $importLine) !== false) {
            $this->warn("⚠️  Import para '{$slug}' já existe no blocks.js");
            return;
        }
        
        // Método simples: adicionar antes do console.log
        if (strpos($content, $importLine) === false) {
            // Adicionar o import antes do console.log
            $content = str_replace(
                "console.log('🎨 Auto Blocks - Sistema carregado!');",
                $importLine . "\n\nconsole.log('🎨 Auto Blocks - Sistema carregado!');",
                $content
            );
            
            $this->files->put($blocksJsPath, $content);
            $this->line("✅ Atualizado: blocks.js");
        } else {
            $this->line("✅ Import já existe no blocks.js");
        }
    }

    /**
     * Obter conteúdo do stub
     */
    protected function getStub(string $stub, array $replacements = []): string
    {
        $stubPath = __DIR__ . "/stubs/{$stub}";
        
        if (!$this->files->exists($stubPath)) {
            // Criar stub dinamicamente se não existir
            return $this->generateStubContent($stub, $replacements);
        }
        
        $content = $this->files->get($stubPath);
        
        foreach ($replacements as $search => $replace) {
            $content = str_replace("{{ {$search} }}", $replace, $content);
        }
        
        return $content;
    }

    /**
     * Gerar conteúdo do stub dinamicamente
     */
    protected function generateStubContent(string $stub, array $replacements): string
    {
        switch ($stub) {
            case 'block.jsx.stub':
                return $this->getBlockJsxStub($replacements);
            case 'block.php.stub':
                return $this->getBlockPhpStub($replacements);
            case 'block.js.stub':
                return $this->getBlockJsStub($replacements);
            case 'block.css.stub':
                return $this->getBlockCssStub($replacements);
            default:
                return '';
        }
    }

    /**
     * Template para block.jsx
     */
    protected function getBlockJsxStub(array $data): string
    {
        return <<<JSX
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

registerBlockType('{$data['NAMESPACE']}/{$data['BLOCK_SLUG']}', {
    edit: ({ attributes, setAttributes }) => {
        const { content } = attributes;
        const blockProps = useBlockProps();
        
        return (
            <div {...blockProps}>
                <div className="{$data['BLOCK_SLUG']}-editor p-4 border-2 border-dashed border-gray-300 rounded-lg">
                    <h3 className="text-lg font-bold mb-2">{$data['BLOCK_TITLE']}</h3>
                    <RichText
                        tagName="div"
                        value={content}
                        onChange={(newContent) => setAttributes({ content: newContent })}
                        placeholder={__('Digite o conteúdo do bloco...', 'doctailwind')}
                        className="min-h-16"
                    />
                </div>
            </div>
        );
    },
    
    save: () => null
});
JSX;
    }

    /**
     * Template para block.php
     */
    protected function getBlockPhpStub(array $data): string
    {
        return <<<PHP
<?php
/**
 * Renderização do bloco {$data['BLOCK_TITLE']} usando Blade
 */

\$content = \$attributes['content'] ?? 'Conteúdo do bloco...';
\$block_slug = '{$data['BLOCK_SLUG']}';
\$block_title = '{$data['BLOCK_TITLE']}';

// Dados para o template Blade
\$view_data = compact('content', 'block_slug', 'block_title', 'attributes');

// Renderizar usando Blade
echo view('blocks.{$data['BLOCK_SLUG']}', \$view_data)->render();
PHP;
    }

    /**
     * Template para arquivo Blade
     */
    protected function getBladeStub(array $data): string
    {
        return <<<BLADE
{{--
  Template Blade para o bloco {$data['BLOCK_TITLE']}
  Variáveis disponíveis: \$content, \$block_slug, \$block_title, \$attributes
--}}

<div class="custom-block-wrapper my-6 {$data['BLOCK_SLUG']}-block" data-block="{$data['BLOCK_SLUG']}">
    <div class="custom-block p-6 bg-white rounded-lg shadow-sm border border-gray-200 transition-all duration-300 hover:shadow-md">
        <h3 class="text-xl font-bold mb-4 text-gray-900">{$data['BLOCK_TITLE']}</h3>
        
        <div class="block-content prose prose-gray max-w-none">
            {!! wp_kses_post(\$content) !!}
        </div>
    </div>
</div>
BLADE;
    }

    /**
     * Template para block.js
     */
    protected function getBlockJsStub(array $data): string
    {
        return <<<JS
/**
 * JavaScript específico do bloco {$data['CLASS_NAME']}
 */

document.addEventListener('DOMContentLoaded', function() {
    const blocks = document.querySelectorAll('.{$data['BLOCK_SLUG']}-block');
    
    blocks.forEach(function(block) {
        // Adicionar funcionalidades específicas do bloco
        initBlock{$data['CLASS_NAME']}(block);
    });
});

/**
 * Inicializar funcionalidades do bloco
 */
function initBlock{$data['CLASS_NAME']}(block) {
    // Adicionar interatividade aqui
    block.addEventListener('click', function() {
        console.log('Bloco {$data['CLASS_NAME']} clicado!');
    });
    
    // Exemplo: efeito hover
    block.addEventListener('mouseenter', function() {
        block.style.transform = 'scale(1.02)';
        block.style.transition = 'transform 0.3s ease';
    });
    
    block.addEventListener('mouseleave', function() {
        block.style.transform = 'scale(1)';
    });
}
JS;
    }

    /**
     * Template para block.css
     */
    protected function getBlockCssStub(array $data): string
    {
        return <<<CSS
/**
 * CSS específico do bloco {$data['BLOCK_SLUG']}
 */

.{$data['BLOCK_SLUG']}-block {
    margin: 2rem 0;
    padding: 1.5rem;
    border-radius: 8px;
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    transition: all 0.3s ease;
}

.{$data['BLOCK_SLUG']}-block:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.{$data['BLOCK_SLUG']}-block h3 {
    color: #374151;
    margin-bottom: 1rem;
}

.{$data['BLOCK_SLUG']}-block .block-content {
    color: #6b7280;
    line-height: 1.6;
}

/* Responsividade */
@media (max-width: 768px) {
    .{$data['BLOCK_SLUG']}-block {
        padding: 1rem;
        margin: 1rem 0;
    }
}

/* Estilo para o editor */
.{$data['BLOCK_SLUG']}-editor {
    border: 2px dashed #d1d5db;
    background: #f9fafb;
}

.{$data['BLOCK_SLUG']}-editor:hover {
    border-color: #3b82f6;
    background: #eff6ff;
}
CSS;
    }

    /**
     * Exibir resumo do que foi criado
     */
    protected function displaySummary(string $slug, string $title): void
    {
        $this->newLine();
        $this->info("🎉 Bloco '{$title}' criado com sucesso!");
        $this->newLine();
        
        $this->comment("📁 Arquivos criados:");
        $this->line("   resources/blocks/{$slug}/block.json");
        $this->line("   resources/blocks/{$slug}/block.jsx");
        $this->line("   resources/blocks/{$slug}/block.php");
        $this->line("   resources/views/blocks/{$slug}.blade.php");
        
        if ($this->option('with-js')) {
            $this->line("   resources/blocks/{$slug}/block.js");
        }
        
        if ($this->option('with-css')) {
            $this->line("   resources/blocks/{$slug}/block.css");
        }
        
        $this->newLine();
        $this->comment("⚙️  Arquivos atualizados:");
        $this->line("   app/Blocks/BlockManager.php");
        $this->line("   resources/js/blocks.js");
        
        $this->newLine();
        $this->comment("🔄 Próximos passos:");
        $this->line("   1. npm run build");
        $this->line("   2. Verificar o bloco no editor WordPress");
        $this->line("   3. Personalizar conforme necessário");
        
        $this->newLine();
        $this->warn("💡 Dica: Use --with-js e --with-css para incluir assets específicos!");
    }
}
