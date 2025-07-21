<?php

namespace Juliojar4\AutoBlocks;

use Composer\Script\Event;
use Composer\IO\IOInterface;

/**
 * Auto Blocks System Automatic Installer
 * 
 * Automatically copies all necessary files to the Sage/Acorn theme
 */
class Installer
{
    /**
     * Execute after installation
     */
    public static function install(Event $event): void
    {
        $io = $event->getIO();
        $composer = $event->getComposer();
        
        $io->write('<info>🎨 Installing Auto Blocks - Gutenberg Blocks System...</info>');
        
        // Detect theme directory
        $themeDir = self::detectThemeDirectory();
        
        if (!$themeDir) {
            $io->writeError('<error>❌ Theme directory not found. Run inside a Sage/Acorn theme.</error>');
            $io->writeError('<error>   Make sure you are in the theme root directory (where functions.php is located).</error>');
            return;
        }
        
        $io->write("<info>✅ Theme detected: {$themeDir}</info>");
        
        $packageDir = self::getPackageDirectory($composer);
        
        // Copy files
        self::copyFiles($packageDir, $themeDir, $io);
        
        // Create directories
        self::createDirectories($themeDir, $io);
        
        // Update theme files
        self::updateThemeFiles($themeDir, $io);
        
        $io->write('');
        $io->write('<info>✅ Auto Blocks instalado com sucesso!</info>');
        $io->write('');
        $io->write('<comment>📋 Next steps:</comment>');
        $io->write('<comment>  1. npm install</comment>');
        $io->write('<comment>  2. npm run build</comment>');
        $io->write('<comment>  3. php artisan make:block my-first-block --with-js --with-css</comment>');
        $io->write('<comment>  4. npm run build</comment>');
        $io->write('<comment>  5. Check in WordPress editor</comment>');
        $io->write('');
    }
    
    /**
     * Execute after update
     */
    public static function update(Event $event): void
    {
        $io = $event->getIO();
        $io->write('<info>🔄 Updating Auto Blocks...</info>');
        
        self::install($event);
    }
    
    /**
     * Detect theme directory
     */
    protected static function detectThemeDirectory(): ?string
    {
        $possiblePaths = [
            getcwd(),
            dirname(getcwd()),
            realpath('.'),
        ];
        
        foreach ($possiblePaths as $path) {
            if (self::isThemeDirectory($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Check if it's a valid theme directory
     */
    protected static function isThemeDirectory(string $path): bool
    {
        return file_exists($path . '/style.css') && 
               file_exists($path . '/functions.php') &&
               is_dir($path . '/app');
    }
    
    /**
     * Get package directory
     */
    protected static function getPackageDirectory($composer): string
    {
        $vendorDir = $composer->getConfig()->get('vendor-dir');
        return $vendorDir . '/juliojar4/auto-blocks';
    }
    
    /**
     * Copy necessary files
     */
    protected static function copyFiles(string $packageDir, string $themeDir, IOInterface $io): void
    {
        $filesToCopy = [
            'stubs/BlockManager.php' => 'app/Blocks/BlockManager.php',
            'stubs/MakeBlockCommand.php' => 'app/Console/Commands/MakeBlockCommand.php',
            'stubs/SyncBlocksCommand.php' => 'app/Console/Commands/SyncBlocksCommand.php',
            'stubs/blocks.js' => 'resources/js/blocks.js',
            'stubs/vite.config.js' => 'vite.config.js',
            'stubs/blocks.php' => 'app/blocks.php'
        ];
        
        foreach ($filesToCopy as $source => $destination) {
            $sourcePath = $packageDir . '/' . $source;
            $destPath = $themeDir . '/' . $destination;
            
            // Create directory if it doesn't exist
            $destDir = dirname($destPath);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            
            if (file_exists($sourcePath)) {
                if (!file_exists($destPath) || self::shouldOverwrite($sourcePath, $destPath)) {
                    if (copy($sourcePath, $destPath)) {
                        $io->write("  ✅ Copied: {$destination}");
                    } else {
                        $io->writeError("  ❌ Error copying: {$destination}");
                    }
                } else {
                    $io->write("  ⏭️ Skipped (already exists and is newer): {$destination}");
                }
            } else {
                $io->writeError("  ❌ Source file not found: {$source}");
            }
        }
    }
    
    /**
     * Check if file should be overwritten
     */
    protected static function shouldOverwrite(string $sourcePath, string $destPath): bool
    {
        if (!file_exists($destPath)) {
            return true;
        }
        
        // Overwrite if source file is newer
        return filemtime($sourcePath) > filemtime($destPath);
    }
    
    /**
     * Create necessary directories
     */
    protected static function createDirectories(string $themeDir, IOInterface $io): void
    {
        $directories = [
            'resources/blocks',
            'resources/views/blocks',
            'public/build',
            'app/Blocks',
            'app/Console/Commands'
        ];
        
        foreach ($directories as $dir) {
            $fullPath = $themeDir . '/' . $dir;
            if (!is_dir($fullPath)) {
                if (mkdir($fullPath, 0755, true)) {
                    $io->write("  📁 Created directory: {$dir}");
                }
            }
        }
    }
    
    /**
     * Update theme files
     */
    protected static function updateThemeFiles(string $themeDir, IOInterface $io): void
    {
        // Update functions.php
        self::updateFunctionsPhp($themeDir, $io);
        
        // Update ThemeServiceProvider.php
        self::updateThemeServiceProvider($themeDir, $io);
    }
    
    /**
     * Update functions.php
     */
    protected static function updateFunctionsPhp(string $themeDir, IOInterface $io): void
    {
        $functionsPath = $themeDir . '/functions.php';
        
        if (!file_exists($functionsPath)) {
            $io->writeError("  ❌ functions.php not found");
            return;
        }
        
        $content = file_get_contents($functionsPath);
        
        // Check if 'blocks' is already in collect
        if (strpos($content, "'blocks'") === false) {
            $patterns = [
                "collect(['setup', 'filters'])",
                'collect([\'setup\', \'filters\'])',
                "collect([\"setup\", \"filters\"])"
            ];
            
            $replacements = [
                "collect(['setup', 'filters', 'blocks'])",
                "collect(['setup', 'filters', 'blocks'])",
                "collect([\"setup\", \"filters\", \"blocks\"])"
            ];
            
            $originalContent = $content;
            $content = str_replace($patterns, $replacements, $content);
            
            if ($content !== $originalContent) {
                if (file_put_contents($functionsPath, $content)) {
                    $io->write("  ✅ Updated: functions.php (added 'blocks')");
                } else {
                    $io->writeError("  ❌ Error updating functions.php");
                }
            }
        } else {
            $io->write("  ⏭️ functions.php already configured");
        }
    }
    
    /**
     * Update ThemeServiceProvider.php
     */
    protected static function updateThemeServiceProvider(string $themeDir, IOInterface $io): void
    {
        $providerPath = $themeDir . '/app/Providers/ThemeServiceProvider.php';
        
        if (!file_exists($providerPath)) {
            $io->write("  ⚠️ ThemeServiceProvider.php not found (normal in some themes)");
            return;
        }
        
        $content = file_get_contents($providerPath);
        $originalContent = $content;
        $updated = false;
        
        // Adicionar imports se não existirem
        if (strpos($content, 'use App\\Blocks\\BlockManager;') === false) {
            $content = str_replace(
                'use Roots\\Acorn\\Sage\\SageServiceProvider;',
                "use Roots\\Acorn\\Sage\\SageServiceProvider;\nuse App\\Blocks\\BlockManager;",
                $content
            );
            $updated = true;
        }
        
        if (strpos($content, 'use App\\Console\\Commands\\MakeBlockCommand;') === false) {
            $content = str_replace(
                'use App\\Blocks\\BlockManager;',
                "use App\\Blocks\\BlockManager;\nuse App\\Console\\Commands\\MakeBlockCommand;\nuse App\\Console\\Commands\\SyncBlocksCommand;",
                $content
            );
            $updated = true;
        }
        
        // Adicionar registro do BlockManager
        if (strpos($content, '$this->app->singleton(BlockManager::class);') === false) {
            $registerMethod = 'public function register()
    {
        parent::register();';
            
            $newRegisterMethod = 'public function register()
    {
        parent::register();
        
        // Register the block manager in the container
        $this->app->singleton(BlockManager::class);';
                
            $content = str_replace($registerMethod, $newRegisterMethod, $content);
            $updated = true;
        }
        
        // Add commands
        if (strpos($content, 'MakeBlockCommand::class') === false) {
            $commandsSection = '
        // Register custom commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeBlockCommand::class,
                SyncBlocksCommand::class,
            ]);
        }';
    
            $content = str_replace(
                '$this->app->singleton(BlockManager::class);',
                '$this->app->singleton(BlockManager::class);' . $commandsSection,
                $content
            );
            $updated = true;
        }
        
        if ($updated && $content !== $originalContent) {
            if (file_put_contents($providerPath, $content)) {
                $io->write("  ✅ Updated: ThemeServiceProvider.php");
            } else {
                $io->writeError("  ❌ Error updating ThemeServiceProvider.php");
            }
        } else {
            $io->write("  ⏭️ ThemeServiceProvider.php already configured");
        }
    }
}
