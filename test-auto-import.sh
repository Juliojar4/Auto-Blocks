#!/bin/bash

echo "🧪 Auto Blocks - Teste de Auto Import"
echo "======================================"
echo ""

# Criar um arquivo blocks.js temporário para teste
cat > /tmp/blocks-test.js << 'EOF'
/**
 * Arquivo principal para registrar todos os blocos customizados
 * 
 * Os blocos serão automaticamente importados quando criados via:
 * lando wp acorn make:block nome-do-bloco --with-js --with-css
 * ou
 * wp acorn make:block nome-do-bloco --with-js --with-css
 */

// Importar estilos globais para os blocos no editor
import '../css/blocks.css';

// Os imports dos blocos serão adicionados automaticamente aqui
// Exemplo:
// import '../blocks/meu-bloco/block.jsx';

// AUTO-IMPORTS: Os blocos criados são importados automaticamente abaixo desta linha

console.log('🎨 Auto Blocks - Sistema carregado!');
EOF

echo "📄 Arquivo de teste criado:"
echo "----------------------------"
cat /tmp/blocks-test.js
echo "----------------------------"
echo ""

# Simular adição de import
IMPORT_LINE="import '../blocks/teste-bloco/block.jsx';"

echo "➕ Simulando adição do import: $IMPORT_LINE"
echo ""

# Testar método 1: após marcador AUTO-IMPORTS
if grep -q "AUTO-IMPORTS:" /tmp/blocks-test.js; then
    echo "✅ Marcador AUTO-IMPORTS encontrado!"
    
    # Usar sed para adicionar após o marcador
    sed -i "/AUTO-IMPORTS:/ a\\$IMPORT_LINE" /tmp/blocks-test.js
    echo "➕ Import adicionado após marcador"
else
    echo "❌ Marcador AUTO-IMPORTS não encontrado!"
fi

echo ""
echo "📄 Resultado final:"
echo "----------------------------"
cat /tmp/blocks-test.js
echo "----------------------------"
echo ""

# Testar se o import foi adicionado
if grep -q "$IMPORT_LINE" /tmp/blocks-test.js; then
    echo "✅ SUCESSO: Import foi adicionado corretamente!"
else
    echo "❌ FALHA: Import não foi adicionado!"
fi

# Limpar
rm /tmp/blocks-test.js

echo ""
echo "🧪 Teste concluído!"
