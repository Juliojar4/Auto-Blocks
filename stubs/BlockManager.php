<?php

namespace App\Blocks;

/**
 * Gerenciador de Blocos Customizados
 * 
 * Sistema simplificado: apenas liste os nomes dos blocos aqui
 * e ele automaticamente descobrirá todos os arquivos necessários
 */
class BlockManager
{
    /**
     * Lista simples de blocos para registrar
     * Apenas adicione o nome da pasta do bloco aqui!
     */
    protected array $blocks = [
        // Adicione novos blocos aqui - apenas o nome da pasta!
        // Exemplo: 'meu-bloco', 'text-block', 'hero-section'
    ];

    /**
     * Namespace dos blocos
     */
    protected string $namespace = 'doctailwind';

    /**
     * Registrar todos os blocos automaticamente
     */
    public function register(): void
    {
        // Registrar blocos no WordPress
        foreach ($this->blocks as $blockName) {
            $this->registerSingleBlock($blockName);
        }
        
        // Enfileirar assets
        add_action('enqueue_block_editor_assets', [$this, 'enqueueAssets']);
    }

    /**
     * Registrar um bloco individual
     */
    protected function registerSingleBlock(string $blockName): void
    {
        $blockPath = get_template_directory() . "/resources/blocks/{$blockName}";
        
        // Verificar se a pasta do bloco existe
        if (!is_dir($blockPath)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("BlockManager: Pasta do bloco '{$blockName}' não encontrada em {$blockPath}");
            }
            return;
        }

        // Verificar se block.json existe
        $blockJsonPath = "{$blockPath}/block.json";
        if (!file_exists($blockJsonPath)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("BlockManager: block.json não encontrado para '{$blockName}' em {$blockJsonPath}");
            }
            return;
        }

        // Registrar o bloco usando o diretório
        $result = register_block_type($blockPath);
        
        // Carregar assets específicos do bloco
        $this->enqueueBlockSpecificAssets($blockName);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            if ($result) {
                error_log("BlockManager: Bloco '{$blockName}' registrado com sucesso");
            } else {
                error_log("BlockManager: Falha ao registrar bloco '{$blockName}'");
            }
        }
    }

    /**
     * Carregar assets globais dos blocos
     */
    public function enqueueAssets(): void
    {
        // JavaScript global dos blocos
        $this->enqueueAsset('js', 'blocks', [
            'wp-blocks', 
            'wp-element', 
            'wp-block-editor', 
            'wp-components', 
            'wp-i18n'
        ]);

        // CSS global dos blocos
        $this->enqueueAsset('css', 'blocks');
    }

    /**
     * Carregar um asset (JS ou CSS)
     */
    protected function enqueueAsset(string $type, string $name, array $dependencies = []): void
    {
        $manifestPath = get_template_directory() . '/public/build/manifest.json';
        
        if (!file_exists($manifestPath)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("BlockManager: manifest.json não encontrado");
            }
            return;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $assetKey = "resources/{$type}/{$name}.{$type}";
        
        if (!isset($manifest[$assetKey])) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("BlockManager: Asset '{$assetKey}' não encontrado no manifest");
            }
            return;
        }

        $assetInfo = $manifest[$assetKey];
        $assetUrl = get_template_directory_uri() . '/public/build/' . $assetInfo['file'];
        $version = $this->getAssetVersion($assetInfo);

        if ($type === 'js') {
            wp_enqueue_script(
                "custom-blocks-{$name}",
                $assetUrl,
                $dependencies,
                $version,
                true
            );
        } else {
            wp_enqueue_style(
                "custom-blocks-{$name}",
                $assetUrl,
                [],
                $version
            );
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("BlockManager: Asset {$type} '{$name}' carregado: {$assetUrl}");
        }
    }

    /**
     * Obter versão do asset
     */
    protected function getAssetVersion(array $assetInfo): string
    {
        // Usar hash do arquivo se disponível
        if (isset($assetInfo['file'])) {
            return hash('crc32', $assetInfo['file']);
        }
        
        // Fallback para versão do tema
        return wp_get_theme()->get('Version') ?: '1.0.0';
    }

    /**
     * Adicionar um novo bloco à lista
     */
    public function addBlock(string $blockName): void
    {
        if (!in_array($blockName, $this->blocks)) {
            $this->blocks[] = $blockName;
        }
    }

    /**
     * Obter lista de blocos registrados
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }

    /**
     * Obter namespace dos blocos
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * Carregar assets específicos de um bloco
     */
    protected function enqueueBlockSpecificAssets(string $blockName): void
    {
        // Hook para carregar assets no editor
        add_action('enqueue_block_editor_assets', function() use ($blockName) {
            $this->loadBlockAssets($blockName, 'editor');
        });
        
        // Hook para carregar assets no frontend
        add_action('wp_enqueue_scripts', function() use ($blockName) {
            $this->loadBlockAssets($blockName, 'frontend');
        });
    }

    /**
     * Carregar assets JS e CSS de um bloco específico
     */
    protected function loadBlockAssets(string $blockName, string $context = 'editor'): void
    {
        $manifestPath = get_template_directory() . '/public/build/manifest.json';
        
        if (!file_exists($manifestPath)) {
            return;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        
        // Carregar JavaScript específico do bloco
        $jsKey = "resources/blocks/{$blockName}/block.js";
        if (isset($manifest[$jsKey])) {
            $assetInfo = $manifest[$jsKey];
            $assetUrl = get_template_directory_uri() . '/public/build/' . $assetInfo['file'];
            
            wp_enqueue_script(
                "block-{$blockName}-js",
                $assetUrl,
                ['wp-blocks', 'wp-element', 'wp-dom-ready'],
                $this->getAssetVersion($assetInfo),
                true
            );
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("BlockManager: JS específico carregado para '{$blockName}': {$assetUrl}");
            }
        }
        
        // Carregar CSS específico do bloco
        $cssKey = "resources/blocks/{$blockName}/block.css";
        if (isset($manifest[$cssKey])) {
            $assetInfo = $manifest[$cssKey];
            $assetUrl = get_template_directory_uri() . '/public/build/' . $assetInfo['file'];
            
            wp_enqueue_style(
                "block-{$blockName}-css",
                $assetUrl,
                [],
                $this->getAssetVersion($assetInfo)
            );
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("BlockManager: CSS específico carregado para '{$blockName}': {$assetUrl}");
            }
        }
    }
}
