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
                $action = self::determineFileAction($sourcePath, $destPath, $destination);
                
                switch ($action) {
                    case 'copy':
                        if (copy($sourcePath, $destPath)) {
                            $io->write("  ✅ Copied: {$destination}");
                        } else {
                            $io->writeError("  ❌ Error copying: {$destination}");
                        }
                        break;
                        
                    case 'update':
                        if (self::updateExistingFile($sourcePath, $destPath, $destination)) {
                            $io->write("  🔄 Updated: {$destination}");
                        } else {
                            $io->write("  ⚠️  Could not safely update: {$destination} (manual review needed)");
                        }
                        break;
                        
                    case 'skip':
                        $io->write("  ⏭️  Skipped (user modified): {$destination}");
                        break;
                        
                    case 'backup':
                        $backupPath = $destPath . '.backup.' . date('Y-m-d-H-i-s');
                        if (copy($destPath, $backupPath) && copy($sourcePath, $destPath)) {
                            $io->write("  🔄 Updated with backup: {$destination} (backup: " . basename($backupPath) . ")");
                        } else {
                            $io->writeError("  ❌ Error creating backup for: {$destination}");
                        }
                        break;
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
     * Determine what action to take with a file
     */
    protected static function determineFileAction(string $sourcePath, string $destPath, string $destination): string
    {
        // If file doesn't exist, copy it
        if (!file_exists($destPath)) {
            return 'copy';
        }
        
        // Check if it's a critical system file that should be updated
        $systemFiles = [
            'app/Console/Commands/MakeBlockCommand.php',
            'app/Console/Commands/SyncBlocksCommand.php',
            'resources/js/blocks.js'
        ];
        
        if (in_array($destination, $systemFiles)) {
            return 'backup'; // Always backup and update system files
        }
        
        // Special handling for BlockManager - preserve user's block list
        if ($destination === 'app/Blocks/BlockManager.php') {
            return self::fileWasModifiedByUser($destPath) ? 'update' : 'copy';
        }
        
        // Special handling for vite.config.js - merge configuration
        if ($destination === 'vite.config.js') {
            return self::fileWasModifiedByUser($destPath) ? 'update' : 'backup';
        }
        
        // For other files, check if user modified them
        if (self::fileWasModifiedByUser($destPath)) {
            return 'skip';
        }
        
        // If source is newer, update
        if (filemtime($sourcePath) > filemtime($destPath)) {
            return 'copy';
        }
        
        return 'skip';
    }
    
    /**
     * Check if file was modified by user (basic heuristic)
     */
    protected static function fileWasModifiedByUser(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }
        
        $content = file_get_contents($filePath);
        
        // Check for common user modifications
        $userModificationSignals = [
            '// Custom',
            '// User',
            '// Modified',
            '// Added by',
            'TODO:',
            'FIXME:',
            '// My',
        ];
        
        foreach ($userModificationSignals as $signal) {
            if (stripos($content, $signal) !== false) {
                return true;
            }
        }
        
        // Check if BlockManager has custom blocks
        if (strpos($filePath, 'BlockManager.php') !== false) {
            // If there are blocks in the array, user probably added them
            if (preg_match('/protected\s+array\s+\$blocks\s*=\s*\[\s*[\'"][^\'"]+/', $content)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Update existing file while preserving user modifications
     */
    protected static function updateExistingFile(string $sourcePath, string $destPath, string $destination): bool
    {
        if ($destination === 'app/Blocks/BlockManager.php') {
            return self::updateBlockManager($sourcePath, $destPath);
        }
        
        if ($destination === 'vite.config.js') {
            return self::updateViteConfig($sourcePath, $destPath);
        }
        
        return false; // For other files, manual review needed
    }
    
    /**
     * Update BlockManager while preserving user's block list
     */
    protected static function updateBlockManager(string $sourcePath, string $destPath): bool
    {
        $sourceContent = file_get_contents($sourcePath);
        $destContent = file_get_contents($destPath);
        
        // Extract user's block list
        if (preg_match('/protected\s+array\s+\$blocks\s*=\s*(\[.*?\]);/s', $destContent, $matches)) {
            $userBlocks = $matches[1];
            
            // Replace the blocks array in source with user's blocks
            $updatedContent = preg_replace(
                '/protected\s+array\s+\$blocks\s*=\s*\[.*?\];/s',
                "protected array \$blocks = $userBlocks;",
                $sourceContent
            );
            
            return file_put_contents($destPath, $updatedContent) !== false;
        }
        
        return false;
    }
    
    /**
     * Update vite.config.js while preserving user modifications
     */
    protected static function updateViteConfig(string $sourcePath, string $destPath): bool
    {
        // For vite.config.js, it's safer to create a backup and let user merge manually
        // since Vite configs can be highly customized
        return false;
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
