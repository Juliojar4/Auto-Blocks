#!/bin/bash

echo "🎨 Auto Blocks - Instalação Manual"
echo "=================================="
echo ""

# Verificar se estamos em um tema Sage/Acorn
if [ ! -f "style.css" ] || [ ! -f "functions.php" ] || [ ! -d "app" ]; then
    echo "❌ Este não parece ser um diretório de tema Sage/Acorn válido."
    echo "Certifique-se de estar no diretório raiz do tema (onde estão style.css e functions.php)."
    exit 1
fi

echo "✅ Tema Sage/Acorn detectado!"

# Encontrar o diretório do pacote
PACKAGE_DIR=""
for path in "vendor/juliojar4/auto-blocks" "../vendor/juliojar4/auto-blocks" "../../vendor/juliojar4/auto-blocks" "../../../vendor/juliojar4/auto-blocks"; do
    if [ -d "$path" ]; then
        PACKAGE_DIR=$(realpath "$path")
        break
    fi
done

if [ -z "$PACKAGE_DIR" ]; then
    echo "❌ Pacote auto-blocks não encontrado no vendor!"
    echo "Execute: composer require juliojar4/auto-blocks:dev-master"
    exit 1
fi

echo "✅ Pacote encontrado em: $PACKAGE_DIR"

# Criar diretórios necessários
echo ""
echo "📁 Criando diretórios..."
mkdir -p resources/blocks
mkdir -p resources/views/blocks
mkdir -p public/build
mkdir -p app/Blocks
mkdir -p app/Console/Commands

echo "✅ Diretórios criados!"

# Copiar arquivos
echo ""
echo "📄 Copiando arquivos..."

if [ -f "$PACKAGE_DIR/stubs/BlockManager.php" ]; then
    cp "$PACKAGE_DIR/stubs/BlockManager.php" "app/Blocks/BlockManager.php"
    echo "✅ BlockManager.php copiado"
else
    echo "⚠️  BlockManager.php não encontrado"
fi

if [ -f "$PACKAGE_DIR/stubs/MakeBlockCommand.php" ]; then
    cp "$PACKAGE_DIR/stubs/MakeBlockCommand.php" "app/Console/Commands/MakeBlockCommand.php"
    echo "✅ MakeBlockCommand.php copiado"
else
    echo "⚠️  MakeBlockCommand.php não encontrado"
fi

if [ -f "$PACKAGE_DIR/stubs/SyncBlocksCommand.php" ]; then
    cp "$PACKAGE_DIR/stubs/SyncBlocksCommand.php" "app/Console/Commands/SyncBlocksCommand.php"
    echo "✅ SyncBlocksCommand.php copiado"
else
    echo "⚠️  SyncBlocksCommand.php não encontrado"
fi

if [ -f "$PACKAGE_DIR/stubs/blocks.js" ]; then
    cp "$PACKAGE_DIR/stubs/blocks.js" "resources/js/blocks.js"
    echo "✅ blocks.js copiado"
else
    echo "⚠️  blocks.js não encontrado"
fi

if [ -f "$PACKAGE_DIR/stubs/blocks.css" ]; then
    cp "$PACKAGE_DIR/stubs/blocks.css" "resources/css/blocks.css"
    echo "✅ blocks.css copiado"
else
    echo "⚠️  blocks.css não encontrado"
fi

if [ -f "$PACKAGE_DIR/stubs/vite.config.js" ]; then
    cp "$PACKAGE_DIR/stubs/vite.config.js" "vite.config.js"
    echo "✅ vite.config.js copiado"
else
    echo "⚠️  vite.config.js não encontrado"
fi

if [ -f "$PACKAGE_DIR/stubs/blocks.php" ]; then
    cp "$PACKAGE_DIR/stubs/blocks.php" "resources/blocks.php"
    echo "✅ blocks.php copiado"
else
    echo "⚠️  blocks.php não encontrado"
fi

echo ""
echo "✅ Auto Blocks instalado com sucesso!"
echo ""
echo "📋 Próximos passos:"
echo ""
echo "🔧 Para ambientes com LANDO:"
echo "  1. yarn install"
echo "  2. yarn build"
echo "  3. lando wp acorn make:block meu-primeiro-bloco --with-js --with-css"
echo "  4. yarn build"
echo "  5. Verificar no editor WordPress"
echo ""
echo "🔧 Para ambientes SEM LANDO:"
echo "  1. yarn install"
echo "  2. yarn build" 
echo "  3. wp acorn make:block meu-primeiro-bloco --with-js --with-css"
echo "  4. yarn build"
echo "  5. Verificar no editor WordPress"
echo ""
echo "⚠️  IMPORTANTE: NUNCA use 'php artisan' - use sempre 'lando wp acorn' ou 'wp acorn'"
echo ""
