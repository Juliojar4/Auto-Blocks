# 🎨 Auto Blocks - Sistema de Blocos Gutenberg para Sage/Acorn

Sistema completo para criação de blocos Gutenberg customizados em temas WordPress usando Sage/Acorn + Laravel.

## 📦 Instalação

### 1. Instalar via Composer
```bash
composer require juliojar4/auto-blocks:dev-master
```

### 2. Se a instalação automática não funcionar

Execute um dos scripts de instalação manual:

**Opção A - Script Bash (Recomendado):**
```bash
bash vendor/juliojar4/auto-blocks/install-auto-blocks.sh
```

**Opção B - Script PHP:**
```bash
php vendor/juliojar4/auto-blocks/install-auto-blocks.php
```

### 3. Detectar seu ambiente automaticamente
```bash
bash vendor/juliojar4/auto-blocks/detect-environment.sh
```

### 4. Comando correto por ambiente

**🐳 Com Lando (detecta .lando.yml):**
```bash
lando wp acorn make:block nome-do-bloco --with-js --with-css
```

**💻 WP-CLI direto:**
```bash
wp acorn make:block nome-do-bloco --with-js --with-css
```

**❌ NUNCA USE:**
```bash
php artisan make:block  # ← ISSO NÃO FUNCIONA EM WORDPRESS!
```

## 🚀 Uso Rápido

### Criar um novo bloco:
```bash
# Com Lando
lando wp acorn make:block meu-bloco --with-js --with-css

# Sem Lando  
wp acorn make:block meu-bloco --with-js --with-css
```

### Compilar assets:
```bash
npm run build
# ou
yarn build
```

### Verificar blocos criados:
```bash
# Sincronizar blocos existentes
lando wp acorn blocks:sync

# Verificar se tudo está funcionando
bash verify-system.sh
```

## 📁 Estrutura Criada

Após a instalação, os seguintes arquivos e diretórios serão criados:

```
📁 app/
  📁 Blocks/
    📄 BlockManager.php           # Gerenciador de blocos
  📁 Console/
    📁 Commands/
      📄 MakeBlockCommand.php     # Comando para criar blocos
      📄 SyncBlocksCommand.php    # Comando para sincronizar
  📄 setup.php                    # Integração com BlockManager (adicionado)

📁 resources/
  📁 blocks/                      # Diretório para blocos customizados
  📁 views/
    📁 blocks/                    # Templates Blade dos blocos
  📁 js/
    📄 blocks.js                  # JavaScript principal dos blocos
  📁 css/
    📄 blocks.css                 # CSS dos blocos
  📄 blocks.php                   # Configuração PHP dos blocos

📄 vite.config.js                 # Configuração do Vite (atualizada)
📄 sync-blocks.sh                # Script para sincronizar imports
📄 verify-system.sh              # Script para verificar instalação
```

## 🎯 Exemplo de Uso

### 1. Criar um bloco
```bash
lando wp acorn make:block banner-promocional --with-js --with-css
```

### 2. Compilar
```bash
yarn build
```

### 3. Usar no WordPress
- O BlockManager será automaticamente registrado no WordPress
- Acesse o editor de blocos
- Procure por "Banner Promocional"
- Adicione e configure!

## 🔧 Comandos Disponíveis

```bash
# Criar bloco simples
lando wp acorn make:block nome-do-bloco

# Criar bloco com JavaScript e CSS
lando wp acorn make:block nome-do-bloco --with-js --with-css

# Sincronizar blocos existentes (comando do Acorn)
lando wp acorn blocks:sync

# Sincronizar imports no blocks.js (script bash)
bash sync-blocks.sh

# Verificar instalação completa
bash verify-system.sh
```

## ⚠️ Problemas Comuns e Soluções

### 1. Erro "Could not open input file: artisan"
**Solução:** Use `lando wp acorn` em vez de `php artisan`

### 2. Erro no vite.config.js com glob
**Solução:** Já corrigido na versão atual. Se acontecer, reinstale.

### 3. Erro "Could not resolve entry module"
**Solução:** Execute `yarn build` após criar blocos

### 4. Scripts de instalação não executaram
**Solução:** Execute manualmente um dos scripts de instalação

### 5. Comando make:block não encontrado
**Solução:** Verifique se está no diretório do tema e se o Acorn está configurado

### 6. Erro "Could not open input file: artisan"
**Solução:** O comando correto depende do seu ambiente:
- **Com Lando:** `lando wp acorn make:block nome`
- **Sem Lando:** `wp acorn make:block nome` 
- **Nunca use:** `php artisan` (não funciona em WordPress)

### 7. Bloco criado mas não aparece no editor
**Solução:** O import não foi adicionado automaticamente ao blocks.js:
```bash
# Executar o sincronizador automático
bash sync-blocks.sh

# Depois compilar
yarn build
```

### 8. Verificar se tudo está funcionando corretamente
**Solução:** Execute o script de verificação completa:
```bash
bash verify-system.sh
```

## 📋 Requisitos

- ✅ WordPress com tema Sage/Acorn
- ✅ PHP 8.0+
- ✅ Node.js e npm/yarn
- ✅ Composer
- ✅ WP-CLI (recomendado)
- ✅ Lando (opcional, mas recomendado)

## 🆘 Suporte

Se encontrar problemas:

1. ✅ Verifique se está no diretório raiz do tema
2. ✅ Confirme que o Sage/Acorn está configurado
3. ✅ Execute os scripts de instalação manual
4. ✅ Verifique se todos os arquivos foram criados
5. ✅ Execute `yarn build` após mudanças

## 🎉 Resultado

Após a instalação bem-sucedida, você terá:

- ✅ Sistema completo de criação de blocos
- ✅ Templates automatizados
- ✅ Compilação automática de assets
- ✅ Integração perfeita com Gutenberg
- ✅ Suporte a Tailwind CSS
- ✅ Hot reload durante desenvolvimento

---

**Desenvolvido por Julio Jara**  
🔗 [GitHub](https://github.com/Juliojar4/Auto-Blocks)
