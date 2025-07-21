#!/bin/bash

echo "🧪 Auto Blocks - Teste do setup.php"
echo "===================================="
echo ""

# Verificar o conteúdo do stub setup.php
echo "📄 Verificando stub setup.php:"
echo "-------------------------------"

if [ -f "stubs/setup.php" ]; then
    echo "✅ Arquivo setup.php encontrado"
    
    # Verificar se tem o import correto
    if grep -q "use App\\\\Blocks\\\\BlockManager" stubs/setup.php; then
        echo "✅ Import do BlockManager presente"
    else
        echo "❌ Import do BlockManager AUSENTE"
    fi
    
    # Verificar se tem a instância
    if grep -q "new BlockManager()" stubs/setup.php; then
        echo "✅ Instância do BlockManager presente"
    else
        echo "❌ Instância do BlockManager AUSENTE"
    fi
    
    # Verificar se tem a chamada register
    if grep -q "register()" stubs/setup.php; then
        echo "✅ Chamada register() presente"
    else
        echo "❌ Chamada register() AUSENTE"
    fi
    
    # Verificar se tem o hook init
    if grep -q "add_action('init'" stubs/setup.php; then
        echo "✅ Hook 'init' presente"
    else
        echo "❌ Hook 'init' AUSENTE"
    fi
    
    echo ""
    echo "📄 Trecho relevante do setup.php:"
    echo "--------------------------------"
    grep -A5 -B2 "BlockManager" stubs/setup.php
    
else
    echo "❌ Arquivo setup.php NÃO encontrado!"
fi

echo ""
echo "🎯 Resultado:"
if [ -f "stubs/setup.php" ] && 
   grep -q "use App\\\\Blocks\\\\BlockManager" stubs/setup.php && 
   grep -q "new BlockManager()" stubs/setup.php && 
   grep -q "register()" stubs/setup.php && 
   grep -q "add_action('init'" stubs/setup.php; then
    echo "✅ setup.php está CORRETO e pronto para uso!"
else
    echo "❌ setup.php precisa ser corrigido!"
fi

echo ""
