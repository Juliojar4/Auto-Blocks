<?php

namespace App\Blocks;

class BlockManager
{
    /**
     * Folders under resources/blocks (each must contain a block.json).
     */
    protected array $blocks = [
        // Add your blocks here
    ];

    /**
     * Block namespace (e.g., "sage/slide-cards").
     */
    protected string $namespace = 'sage';

    /**
     * Register all listed blocks.
     */
    public function register(): void
    {
        foreach ($this->blocks as $blockName) {
            $this->registerSingleBlock($blockName);
        }
    }

    /**
     * Register a single block from its directory if block.json exists.
     */
    protected function registerSingleBlock(string $blockName): void
    {
        $blockPath = get_template_directory() . "/resources/blocks/{$blockName}";
        $blockJson = "{$blockPath}/block.json";

        if (!is_dir($blockPath)) {
            return;
        }

        if (!file_exists($blockJson)) {
            return;
        }

        register_block_type($blockPath);
    }
 
    public function addBlock(string $blockName): void
    {
        if (!in_array($blockName, $this->blocks, true)) {
            $this->blocks[] = $blockName;
        }
    }

    /**
     * Get the configured block folders.
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }

    /**
     * Get the namespace (for integrations or validation).
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }
}
