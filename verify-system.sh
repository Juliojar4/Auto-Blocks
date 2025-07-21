#!/bin/bash

echo "🔍 Auto Blocks - Verificação Completa do Sistema"
echo "================================================="
echo ""

# Verificar se estamos em um tema correto
if [ ! -f "style.css" ] || [ ! -f "functions.php" ]; then
    echo "❌ Execute este script no diretório raiz do tema WordPress"
    exit 1
fi

echo "✅ Tema WordPress detectado!"
echo ""

# Verificações de arquivos críticos
echo "📋 Verificando arquivos críticos:"

files=(
    "app/Blocks/BlockManager.php"
    "app/Console/Commands/MakeBlockCommand.php"
    "app/Console/Commands/SyncBlocksCommand.php"
    "resources/js/blocks.js"
    "resources/js/app.js"
    "resources/js/editor.js"
    "resources/css/blocks.css"
    "resources/blocks.php"
    "vite.config.js"
    "sync-blocks.sh"
)

missing_files=0
for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file"
    else
        echo "❌ $file (FALTANDO)"
        missing_files=$((missing_files + 1))
    fi
done

echo ""
echo "📊 Resultado da verificação:"
echo "   Total de arquivos: ${#files[@]}"
echo "   Arquivos presentes: $((${#files[@]} - missing_files))"
echo "   Arquivos faltando: $missing_files"

if [ $missing_files -gt 0 ]; then
    echo ""
    echo "⚠️  ATENÇÃO: Alguns arquivos estão faltando!"
    echo "Execute o script de instalação:"
    echo "   bash vendor/juliojar4/auto-blocks/install-auto-blocks.sh"
    echo ""
fi

# Verificar conteúdo do blocks.js
echo ""
echo "🔍 Verificando conteúdo do blocks.js:"
if [ -f "resources/js/blocks.js" ]; then
    if grep -q "AUTO-IMPORTS:" resources/js/blocks.js; then
        echo "✅ Marcador AUTO-IMPORTS presente"
    else
        echo "❌ Marcador AUTO-IMPORTS ausente"
    fi
    
    if grep -q "import.*blocks.css" resources/js/blocks.js; then
        echo "✅ Import do CSS presente"
    else
        echo "❌ Import do CSS ausente"
    fi
else
    echo "❌ Arquivo blocks.js não encontrado"
fi

# Verificar conteúdo do app.js
echo ""
echo "🔍 Verificando conteúdo do app.js:"
if [ -f "resources/js/app.js" ]; then
    if grep -q "import.*blocks" resources/js/app.js; then
        echo "✅ Import do blocks.js presente no app.js"
    else
        echo "❌ Import do blocks.js ausente no app.js"
    fi
else
    echo "❌ Arquivo app.js não encontrado"
fi

# Verificar conteúdo do editor.js
echo ""
echo "🔍 Verificando conteúdo do editor.js:"
if [ -f "resources/js/editor.js" ]; then
    if grep -q "import.*blocks" resources/js/editor.js; then
        echo "✅ Import do blocks.js presente no editor.js"
    else
        echo "❌ Import do blocks.js ausente no editor.js"
    fi
else
    echo "❌ Arquivo editor.js não encontrado"
fi

# Verificar se existem blocos criados
echo ""
echo "🔍 Verificando blocos existentes:"
if [ -d "resources/blocks" ]; then
    block_count=0
    for block_dir in resources/blocks/*; do
        if [ -d "$block_dir" ]; then
            block_name=$(basename "$block_dir")
            if [ -f "$block_dir/block.jsx" ]; then
                echo "✅ Bloco encontrado: $block_name"
                
                # Verificar se está no blocks.js
                if grep -q "import.*blocks/$block_name/block.jsx" resources/js/blocks.js; then
                    echo "   ✅ Import presente no blocks.js"
                else
                    echo "   ❌ Import AUSENTE no blocks.js"
                    echo "   💡 Execute: bash sync-blocks.sh"
                fi
                block_count=$((block_count + 1))
            fi
        fi
    done
    
    if [ $block_count -eq 0 ]; then
        echo "ℹ️  Nenhum bloco encontrado ainda"
        echo "   💡 Crie um bloco com: lando wp acorn make:block meu-bloco --with-js --with-css"
    fi
else
    echo "ℹ️  Diretório resources/blocks não existe ainda"
fi

# Verificar se o ambiente está configurado
echo ""
echo "🔍 Verificando ambiente:"
if [ -f ".lando.yml" ]; then
    echo "✅ Lando detectado - use: lando wp acorn make:block"
else
    echo "ℹ️  Lando não detectado - use: wp acorn make:block"
fi

if [ -f "package.json" ]; then
    echo "✅ package.json presente"
    if command -v npm >/dev/null 2>&1; then
        echo "✅ npm disponível"
    fi
    if command -v yarn >/dev/null 2>&1; then
        echo "✅ yarn disponível"
    fi
else
    echo "❌ package.json não encontrado"
fi

echo ""
echo "🎯 RESUMO:"
if [ $missing_files -eq 0 ]; then
    echo "✅ Sistema Auto Blocks está corretamente instalado!"
    echo ""
    echo "📋 Comandos disponíveis:"
    echo "   - Criar bloco: lando wp acorn make:block nome-do-bloco --with-js --with-css"
    echo "   - Sincronizar: bash sync-blocks.sh"
    echo "   - Compilar: yarn build"
else
    echo "⚠️  Sistema precisa ser instalado ou reparado"
    echo ""
    echo "📋 Para instalar/reparar:"
    echo "   bash vendor/juliojar4/auto-blocks/install-auto-blocks.sh"
fi

echo ""
