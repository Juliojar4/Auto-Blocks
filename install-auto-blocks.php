<?php
/**
 * Script para instalação manual do Auto Blocks
 * Execute este arquivo no diretório raiz do seu tema Sage/Acorn
 */

// Verificar se o composer autoload existe
$autoloadPaths = [
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
    __DIR__ . '/../../../../vendor/autoload.php',
];

$autoloadFound = false;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $autoloadFound = true;
        break;
    }
}

if (!$autoloadFound) {
    echo "❌ Autoload do Composer não encontrado!\n";
    echo "Certifique-se de que o composer install foi executado.\n";
    exit(1);
}

echo "🎨 Executando instalação manual do Auto Blocks...\n\n";

try {
    // Simular evento do Composer
    $composer = new \Composer\Composer();
    $io = new \Composer\IO\ConsoleIO(
        new \Symfony\Component\Console\Input\ArrayInput([]),
        new \Symfony\Component\Console\Output\ConsoleOutput(),
        new \Symfony\Component\Console\Helper\HelperSet()
    );
    
    $event = new \Composer\Script\Event(
        'post-install-cmd',
        $composer,
        $io
    );
    
    // Executar o installer
    \Juliojar4\AutoBlocks\Installer::install($event);
    
} catch (Exception $e) {
    echo "❌ Erro durante a instalação: " . $e->getMessage() . "\n";
    echo "\nTentando instalação simplificada...\n";
    
    // Instalação simplificada
    installSimplified();
}

function installSimplified() {
    echo "🔧 Iniciando instalação simplificada...\n";
    
    // Verificar se estamos em um tema válido
    if (!file_exists('style.css') || !file_exists('functions.php') || !is_dir('app')) {
        echo "❌ Este não parece ser um diretório de tema Sage/Acorn válido.\n";
        echo "Certifique-se de estar no diretório raiz do tema (onde estão style.css e functions.php).\n";
        return;
    }
    
    echo "✅ Tema Sage/Acorn detectado!\n";
    
    // Encontrar o diretório do pacote auto-blocks
    $packageDir = null;
    $vendorPaths = [
        'vendor/juliojar4/auto-blocks',
        '../vendor/juliojar4/auto-blocks',
        '../../vendor/juliojar4/auto-blocks',
        '../../../vendor/juliojar4/auto-blocks',
    ];
    
    foreach ($vendorPaths as $path) {
        if (is_dir($path)) {
            $packageDir = realpath($path);
            break;
        }
    }
    
    if (!$packageDir) {
        echo "❌ Pacote auto-blocks não encontrado no vendor!\n";
        echo "Execute: composer require juliojar4/auto-blocks:dev-master\n";
        return;
    }
    
    echo "✅ Pacote encontrado em: $packageDir\n";
    
    // Criar diretórios necessários
    $directories = [
        'resources/blocks',
        'resources/views/blocks', 
        'public/build',
        'app/Blocks',
        'app/Console/Commands'
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "📁 Criado diretório: $dir\n";
        } else {
            echo "📁 Diretório já existe: $dir\n";
        }
    }
    
    // Copiar arquivos
    $filesToCopy = [
        'stubs/BlockManager.php' => 'app/Blocks/BlockManager.php',
        'stubs/MakeBlockCommand.php' => 'app/Console/Commands/MakeBlockCommand.php',
        'stubs/SyncBlocksCommand.php' => 'app/Console/Commands/SyncBlocksCommand.php',
        'stubs/blocks.js' => 'resources/blocks.js',
        'stubs/vite.config.js' => 'vite.config.js',
        'stubs/blocks.php' => 'resources/blocks.php'
    ];
    
    foreach ($filesToCopy as $source => $destination) {
        $sourcePath = $packageDir . '/' . $source;
        if (file_exists($sourcePath)) {
            copy($sourcePath, $destination);
            echo "📄 Copiado: $destination\n";
        } else {
            echo "⚠️  Arquivo não encontrado: $sourcePath\n";
        }
    }
    
    echo "\n✅ Instalação simplificada concluída!\n";
    echo "\n📋 Próximos passos:\n";
    echo "  1. npm install\n";
    echo "  2. npm run build\n";
    echo "  3. php artisan make:block meu-primeiro-bloco --with-js --with-css\n";
    echo "  4. npm run build\n";
    echo "  5. Verificar no editor WordPress\n\n";
}
