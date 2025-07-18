<?php

namespace Juliojar4\AutoBlocks;

use Composer\Script\Event;
use Composer\IO\IOInterface;

/**
 * Instalador Automático do Sistema Auto Blocks
 * 
 * Copia automaticamente todos os arquivos necessários para o tema Sage/Acorn
 */
class Installer
{
    /**
     * Executar após instalação
     */
    public static function install(Event $event): void
    {
        $io = $event->getIO();
        $composer = $event->getComposer();
        
        $io->write('<info>🎨 Instalando Auto Blocks - Sistema de Blocos Gutenberg...</info>');
        
        // Detectar diretório do tema
        $themeDir = self::detectThemeDirectory();
        
        if (!$themeDir) {
            $io->writeError('<error>❌ Diretório do tema não encontrado. Execute dentro de um tema Sage/Acorn.</error>');
            $io->writeError('<error>   Certifique-se de estar no diretório raiz do tema (onde está o functions.php).</error>');
            return;
        }
        
        $io->write("<info>✅ Tema detectado: {$themeDir}</info>");
        
        $packageDir = self::getPackageDirectory($composer);
        
        // Copiar arquivos
        self::copyFiles($packageDir, $themeDir, $io);
        
        // Criar diretórios
        self::createDirectories($themeDir, $io);
        
        // Atualizar arquivos do tema
        self::updateThemeFiles($themeDir, $io);
        
        $io->write('');
        $io->write('<info>✅ Auto Blocks instalado com sucesso!</info>');
        $io->write('');
        $io->write('<comment>📋 Próximos passos:</comment>');
        $io->write('<comment>  1. npm install</comment>');
        $io->write('<comment>  2. npm run build</comment>');
        $io->write('<comment>  3. php artisan make:block meu-primeiro-bloco --with-js --with-css</comment>');
        $io->write('<comment>  4. npm run build</comment>');
        $io->write('<comment>  5. Verificar no editor WordPress</comment>');
        $io->write('');
    }
    
    /**
     * Executar após atualização
     */
    public static function update(Event $event): void
    {
        $io = $event->getIO();
        $io->write('<info>🔄 Atualizando Auto Blocks...</info>');
        
        self::install($event);
    }
    
    /**
     * Detectar diretório do tema
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
     * Verificar se é um diretório de tema válido
     */
    protected static function isThemeDirectory(string $path): bool
    {
        return file_exists($path . '/style.css') && 
               file_exists($path . '/functions.php') &&
               is_dir($path . '/app');
    }
    
    /**
     * Obter diretório do pacote
     */
    protected static function getPackageDirectory($composer): string
    {
        $vendorDir = $composer->getConfig()->get('vendor-dir');
        return $vendorDir . '/juliojar4/auto-blocks';
    }
    
    /**
     * Copiar arquivos necessários
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
            
            // Criar diretório se não existir
            $destDir = dirname($destPath);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            
            if (file_exists($sourcePath)) {
                if (!file_exists($destPath) || self::shouldOverwrite($sourcePath, $destPath)) {
                    if (copy($sourcePath, $destPath)) {
                        $io->write("  ✅ Copiado: {$destination}");
                    } else {
                        $io->writeError("  ❌ Erro ao copiar: {$destination}");
                    }
                } else {
                    $io->write("  ⏭️ Pulado (já existe e é mais recente): {$destination}");
                }
            } else {
                $io->writeError("  ❌ Arquivo fonte não encontrado: {$source}");
            }
        }
    }
    
    /**
     * Verificar se deve sobrescrever arquivo
     */
    protected static function shouldOverwrite(string $sourcePath, string $destPath): bool
    {
        if (!file_exists($destPath)) {
            return true;
        }
        
        // Sobrescrever se o arquivo fonte for mais recente
        return filemtime($sourcePath) > filemtime($destPath);
    }
    
    /**
     * Criar diretórios necessários
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
                    $io->write("  📁 Criado diretório: {$dir}");
                }
            }
        }
    }
    
    /**
     * Atualizar arquivos do tema
     */
    protected static function updateThemeFiles(string $themeDir, IOInterface $io): void
    {
        // Atualizar functions.php
        self::updateFunctionsPhp($themeDir, $io);
        
        // Atualizar ThemeServiceProvider.php
        self::updateThemeServiceProvider($themeDir, $io);
    }
    
    /**
     * Atualizar functions.php
     */
    protected static function updateFunctionsPhp(string $themeDir, IOInterface $io): void
    {
        $functionsPath = $themeDir . '/functions.php';
        
        if (!file_exists($functionsPath)) {
            $io->writeError("  ❌ functions.php não encontrado");
            return;
        }
        
        $content = file_get_contents($functionsPath);
        
        // Verificar se já tem 'blocks' no collect
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
                    $io->write("  ✅ Atualizado: functions.php (adicionado 'blocks')");
                } else {
                    $io->writeError("  ❌ Erro ao atualizar functions.php");
                }
            }
        } else {
            $io->write("  ⏭️ functions.php já configurado");
        }
    }
    
    /**
     * Atualizar ThemeServiceProvider.php
     */
    protected static function updateThemeServiceProvider(string $themeDir, IOInterface $io): void
    {
        $providerPath = $themeDir . '/app/Providers/ThemeServiceProvider.php';
        
        if (!file_exists($providerPath)) {
            $io->write("  ⚠️ ThemeServiceProvider.php não encontrado (normal em alguns temas)");
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
        
        // Registrar o gerenciador de blocos no container
        $this->app->singleton(BlockManager::class);';
                
            $content = str_replace($registerMethod, $newRegisterMethod, $content);
            $updated = true;
        }
        
        // Adicionar comandos
        if (strpos($content, 'MakeBlockCommand::class') === false) {
            $commandsSection = '
        // Registrar comandos personalizados
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
                $io->write("  ✅ Atualizado: ThemeServiceProvider.php");
            } else {
                $io->writeError("  ❌ Erro ao atualizar ThemeServiceProvider.php");
            }
        } else {
            $io->write("  ⏭️ ThemeServiceProvider.php já configurado");
        }
    }
}
