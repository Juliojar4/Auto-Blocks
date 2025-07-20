#!/bin/bash

echo "🔍 Auto Blocks - Detector de Ambiente"
echo "====================================="
echo ""

# Detectar se está usando Lando
if command -v lando &> /dev/null && [ -f ".lando.yml" ]; then
    echo "✅ Ambiente LANDO detectado!"
    echo ""
    echo "🚀 Para criar um bloco, use:"
    echo "   lando wp acorn make:block nome-do-bloco --with-js --with-css"
    echo ""
    echo "📋 Lista de comandos disponíveis:"
    echo "   lando wp acorn list"
    echo ""
    echo "🔧 Exemplo completo:"
    echo "   1. lando wp acorn make:block banner-promocional --with-js --with-css"
    echo "   2. yarn build"
    echo "   3. Verificar no WordPress"
    
elif command -v wp &> /dev/null; then
    echo "✅ Ambiente WP-CLI detectado!"
    echo ""
    echo "🚀 Para criar um bloco, use:"
    echo "   wp acorn make:block nome-do-bloco --with-js --with-css"
    echo ""
    echo "📋 Lista de comandos disponíveis:"
    echo "   wp acorn list"
    echo ""
    echo "🔧 Exemplo completo:"
    echo "   1. wp acorn make:block banner-promocional --with-js --with-css"
    echo "   2. yarn build"
    echo "   3. Verificar no WordPress"
    
else
    echo "❌ Nenhum ambiente WordPress detectado!"
    echo ""
    echo "🔧 Possíveis soluções:"
    echo "   1. Se usar Lando: certifique-se de que está no diretório com .lando.yml"
    echo "   2. Se usar WP-CLI: instale o WP-CLI primeiro"
    echo "   3. Verifique se está no diretório correto do tema"
fi

echo ""
echo "⚠️  IMPORTANTE: NUNCA use 'php artisan' em WordPress!"
echo "   Use sempre 'lando wp acorn' ou 'wp acorn'"
echo ""
