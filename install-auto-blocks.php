<?php
/**
 * Script para instalação manual do Auto Blocks
 * Execute este arquivo no diretório raiz do seu tema Sage/Acorn
 */

echo "🎨 Executando instalação manual do Auto Blocks...\n\n";

// Executar instalação simplificada diretamente
installSimplified();

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
        'stubs/blocks.js' => 'resources/js/blocks.js',
        'stubs/blocks.css' => 'resources/css/blocks.css',
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
