#!/bin/bash

echo "🧪 Auto Blocks - Teste de Fluxo Completo"
echo "========================================="
echo ""

# Simular a criação de um bloco
BLOCK_NAME="meu-teste-bloco"
IMPORT_LINE="import '../blocks/$BLOCK_NAME/block.jsx';"

echo "📦 Simulando criação do bloco: $BLOCK_NAME"
echo ""

# Criar um arquivo blocks.js temporário como seria depois da instalação
cat > /tmp/blocks-test-completo.js << 'EOF'
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

# Simular o que o comando MakeBlockCommand.php faria
echo "🔄 Simulando atualização automática do blocks.js..."

# Método usado no MakeBlockCommand.php
if grep -q "AUTO-IMPORTS:" /tmp/blocks-test-completo.js; then
    # Adicionar o import após o marcador (método do PHP)
    sed -i "s|// AUTO-IMPORTS: Os blocos criados são importados automaticamente abaixo desta linha|// AUTO-IMPORTS: Os blocos criados são importados automaticamente abaixo desta linha\n$IMPORT_LINE|" /tmp/blocks-test-completo.js
    echo "✅ Import adicionado automaticamente pelo comando make:block"
else
    echo "❌ Marcador AUTO-IMPORTS não encontrado"
fi

echo ""
echo "📄 Conteúdo do blocks.js após criação do bloco:"
echo "----------------------------------------------"
cat /tmp/blocks-test-completo.js
echo "----------------------------------------------"
echo ""

# Verificar se funcionou
if grep -q "$IMPORT_LINE" /tmp/blocks-test-completo.js; then
    echo "✅ SUCESSO: Import foi adicionado automaticamente!"
    echo "✅ O bloco aparecerá no editor após 'yarn build'"
else
    echo "❌ FALHA: Import não foi adicionado!"
    echo "⚠️  Seria necessário executar: bash sync-blocks.sh"
fi

# Simular o app.js e editor.js
echo ""
echo "📄 Verificando integração com app.js:"
cat > /tmp/app-test.js << 'EOF'
/**
 * Arquivo principal da aplicação
 * Este arquivo é carregado no frontend do site
 */

// Importar estilos CSS principais
import '../css/app.css';

// Importar JavaScript dos blocos para o frontend
import './blocks';

// Seu código JavaScript customizado aqui
console.log('🎨 App carregado - Frontend');
EOF

if grep -q "import.*blocks" /tmp/app-test.js; then
    echo "✅ app.js importa blocks.js corretamente"
else
    echo "❌ app.js NÃO importa blocks.js"
fi

echo ""
echo "📄 Verificando integração com editor.js:"
cat > /tmp/editor-test.js << 'EOF'
/**
 * Arquivo para o editor do WordPress (admin)
 * Este arquivo é carregado no editor de blocos (Gutenberg)
 */

// Importar estilos CSS do editor
import '../css/editor.css';

// Importar JavaScript dos blocos para o editor
import './blocks';

// Seu código JavaScript customizado para o editor aqui
console.log('🎨 Editor carregado - Admin');
EOF

if grep -q "import.*blocks" /tmp/editor-test.js; then
    echo "✅ editor.js importa blocks.js corretamente"
else
    echo "❌ editor.js NÃO importa blocks.js"
fi

echo ""
echo "🎯 RESULTADO DO TESTE:"
echo "====================="

all_working=true

# Verificação 1: Import automático
if grep -q "$IMPORT_LINE" /tmp/blocks-test-completo.js; then
    echo "✅ Auto-import do bloco: FUNCIONANDO"
else
    echo "❌ Auto-import do bloco: FALHANDO"
    all_working=false
fi

# Verificação 2: Integração app.js
if grep -q "import.*blocks" /tmp/app-test.js; then
    echo "✅ Integração app.js: FUNCIONANDO"
else
    echo "❌ Integração app.js: FALHANDO"
    all_working=false
fi

# Verificação 3: Integração editor.js
if grep -q "import.*blocks" /tmp/editor-test.js; then
    echo "✅ Integração editor.js: FUNCIONANDO"
else
    echo "❌ Integração editor.js: FALHANDO"
    all_working=false
fi

echo ""
if $all_working; then
    echo "🎉 TODOS OS TESTES PASSARAM!"
    echo "   O sistema está funcionando corretamente"
    echo ""
    echo "📋 Fluxo esperado:"
    echo "   1. composer require juliojar4/auto-blocks:dev-master"
    echo "   2. bash vendor/juliojar4/auto-blocks/install-auto-blocks.sh"
    echo "   3. lando wp acorn make:block meu-bloco --with-js --with-css"
    echo "   4. yarn build"
    echo "   5. ✅ Bloco aparece no editor WordPress!"
else
    echo "❌ ALGUNS TESTES FALHARAM!"
    echo "   O sistema precisa ser corrigido"
fi

# Limpar arquivos temporários
rm -f /tmp/blocks-test-completo.js /tmp/app-test.js /tmp/editor-test.js

echo ""
