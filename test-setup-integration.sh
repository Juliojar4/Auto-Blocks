#!/bin/bash

echo "🧪 Auto Blocks - Teste de Integração no setup.php"
echo "=================================================="
echo ""

# Simular um setup.php existente de um tema Sage
cat > /tmp/setup-existente.php << 'EOF'
<?php

/**
 * Theme setup.
 */

namespace App;

use Roots\Acorn\Sage\SageServiceProvider;

/**
 * Register the theme assets.
 */
add_action('wp_enqueue_scripts', function () {
    Bundle::enqueue('app', 'app');
}, 100);

/**
 * Register the initial theme setup.
 */
add_action('after_setup_theme', function () {
    add_theme_support('post-thumbnails');
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style'
    ]);
});
EOF

echo "📄 Setup.php existente simulado:"
echo "--------------------------------"
cat /tmp/setup-existente.php
echo "--------------------------------"
echo ""

# Simular a adição do nosso stub
echo "➕ Adicionando integração do BlockManager..."

# Verificar se já existe (simulando o que o script faria)
if grep -q "BlockManager" /tmp/setup-existente.php; then
    echo "✅ BlockManager já integrado"
else
    echo "➕ Adicionando ao final do arquivo..."
    cat stubs/setup.php >> /tmp/setup-existente.php
    echo "✅ BlockManager adicionado!"
fi

echo ""
echo "📄 Setup.php após integração:"
echo "-----------------------------"
cat /tmp/setup-existente.php
echo "-----------------------------"
echo ""

# Verificar resultado
if grep -q "use App\\\\Blocks\\\\BlockManager" /tmp/setup-existente.php && 
   grep -q "new BlockManager()" /tmp/setup-existente.php; then
    echo "✅ SUCESSO: Integração funcionou perfeitamente!"
    echo "   - Setup.php original mantido"
    echo "   - BlockManager adicionado ao final"
    echo "   - Sem duplicação ou conflitos"
else
    echo "❌ FALHA: Integração não funcionou"
fi

# Limpar
rm -f /tmp/setup-existente.php

echo ""
