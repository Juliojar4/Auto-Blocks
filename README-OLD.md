# Auto Blocks - Sistema de Blocos Gutenberg Automatizado

Sistema completo de blocos Gutenberg customizados para WordPress com Sage/Acorn.

## 🚀 Instalação

### Pré-requisitos
- WordPress 6.0+
- Tema Sage/Acorn
- PHP 8.0+
- Node.js e npm

### Instalação via Composer

```bash
# No diretório do seu tema Sage/Acorn
composer config repositories.auto-blocks vcs https://github.com/Juliojar4/Auto-Blocks.git
composer require juliojar4/auto-blocks:dev-master
```

O instalador executará automaticamente e:
- ✅ Copiará todos os arquivos necessários
- ✅ Criará diretórios necessários
- ✅ Atualizará `functions.php` e `ThemeServiceProvider.php`
- ✅ Configurará sistema completo de blocos

### Após Instalação

```bash
# Instalar dependências Node.js
npm install

# Build inicial
npm run build

# Criar primeiro bloco
php artisan make:block meu-primeiro-bloco --with-js --with-css

# Build final
npm run build
```

## 🎯 Uso

### Criar Novos Blocos

```bash
# Bloco básico
php artisan make:block nome-do-bloco

# Bloco com JavaScript e CSS
php artisan make:block card-produto --with-js --with-css

# Bloco com configurações específicas
php artisan make:block hero-banner --category=design --icon=cover-image --description="Banner principal"
```

### Sincronizar Blocos Existentes

```bash
php artisan blocks:sync
```

### Build Assets

```bash
npm run dev    # Desenvolvimento com hot reload
npm run build  # Produção otimizada
```

## 📁 Estrutura Criada

```
tema/
├── app/
│   ├── blocks.php                 ← Registro dos blocos
│   ├── Blocks/
│   │   └── BlockManager.php       ← Gerenciador central
│   └── Console/Commands/
│       ├── MakeBlockCommand.php   ← Comando para criar blocos
│       └── SyncBlocksCommand.php  ← Comando para sincronizar
├── resources/
│   ├── blocks/                    ← Blocos customizados
│   │   └── exemplo-bloco/
│   │       ├── block.json
│   │       ├── block.jsx
│   │       ├── block.php
│   │       ├── block.js  (opcional)
│   │       └── block.css (opcional)
│   ├── views/blocks/              ← Templates Blade
│   │   └── exemplo-bloco.blade.php
│   └── js/
│       └── blocks.js              ← JavaScript global
└── vite.config.js                 ← Configuração atualizada
```

## ⚡ Recursos

- 🎨 **Criação automática** de blocos com comando Artisan
- 🔧 **Templates Blade** para frontend
- 📱 **Assets específicos** por bloco (JS/CSS)
- ⚡ **Build automático** via Vite
- 🔄 **Hot reload** em desenvolvimento
- 📋 **Sincronização** de blocos existentes
- 🎯 **Zero configuração** após instalação

## 🛠️ Comandos Disponíveis

| Comando | Descrição |
|---------|-----------|
| `php artisan make:block nome` | Criar novo bloco |
| `php artisan make:block nome --with-js` | Criar bloco com JavaScript |
| `php artisan make:block nome --with-css` | Criar bloco com CSS |
| `php artisan blocks:sync` | Sincronizar blocos existentes |

## 📄 Licença

MIT License

## 🤝 Contribuição

Contribuições são bem-vindas! Abra uma issue ou pull request.

---

**Auto Blocks** - Sistema automatizado para blocos Gutenberg customizados 🎨
