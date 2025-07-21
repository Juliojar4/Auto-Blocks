#!/bin/bash

echo "🔄 Auto Blocks - Sincronizador de Blocos"
echo "========================================"
echo ""

# Verificar se estamos no diretório correto
if [ ! -f "resources/js/blocks.js" ]; then
    echo "❌ Arquivo blocks.js não encontrado!"
    echo "Execute este script no diretório raiz do tema."
    exit 1
fi

echo "✅ Procurando blocos criados..."

# Encontrar todos os arquivos block.jsx
BLOCKS_FOUND=0
BLOCKS_ADDED=0

# Ler o conteúdo atual do blocks.js
BLOCKS_JS_CONTENT=$(cat resources/js/blocks.js)

# Procurar por arquivos block.jsx
if [ -d "resources/blocks" ]; then
    for BLOCK_DIR in resources/blocks/*; do
        if [ -d "$BLOCK_DIR" ] && [ -f "$BLOCK_DIR/block.jsx" ]; then
            BLOCKS_FOUND=$((BLOCKS_FOUND + 1))
            BLOCK_NAME=$(basename "$BLOCK_DIR")
            IMPORT_LINE="import '../blocks/$BLOCK_NAME/block.jsx';"
            
            echo "📦 Encontrado bloco: $BLOCK_NAME"
            
            # Verificar se o import já existe
            if ! grep -q "$IMPORT_LINE" resources/js/blocks.js; then
                echo "➕ Adicionando import para $BLOCK_NAME"
                
                # Adicionar após o marcador AUTO-IMPORTS ou antes do console.log
                if grep -q "AUTO-IMPORTS:" resources/js/blocks.js; then
                    sed -i "/AUTO-IMPORTS:/ a\\$IMPORT_LINE" resources/js/blocks.js
                else
                    # Criar arquivo temporário com o novo import
                    awk -v import="$IMPORT_LINE" '
                    /console\.log.*Auto Blocks.*Sistema carregado/ {
                        print import "\n"
                        print
                        next
                    }
                    { print }
                    ' resources/js/blocks.js > resources/js/blocks.js.tmp
                    mv resources/js/blocks.js.tmp resources/js/blocks.js
                fi
                
                BLOCKS_ADDED=$((BLOCKS_ADDED + 1))
            else
                echo "✅ Import já existe para $BLOCK_NAME"
            fi
        fi
    done
else
    echo "⚠️  Diretório resources/blocks não encontrado"
fi

echo ""
echo "📊 Resultado:"
echo "   Blocos encontrados: $BLOCKS_FOUND"
echo "   Imports adicionados: $BLOCKS_ADDED"

if [ $BLOCKS_ADDED -gt 0 ]; then
    echo ""
    echo "🔧 Próximos passos:"
    echo "   1. yarn build"
    echo "   2. Verificar blocos no editor WordPress"
else
    echo ""
    echo "✅ Todos os blocos já estão sincronizados!"
fi

echo ""
