# 🎨 Auto Blocks - Gutenberg Block System for Sage/Acorn

Complete system for creating custom Gutenberg blocks in WordPress themes using Sage/Acorn + Laravel.

## � System Flow

```mermaid
graph TD
    A[🎯 Start] --> B[📦 Install via Composer]
    B --> C[📝 Run Installation Script]
    C --> D[🔧 Files Created]

    D --> E[📁 app/Blocks/BlockManager.php]
    D --> F[📁 app/Console/Commands/]
    D --> G[📁 resources/blocks/]
    D --> H[📄 vite.config.js updated]

    I[🚀 Create Block] --> J[php artisan make:block my-block]
    J --> K[📁 Block Structure Created]
    J --> L[📝 BlockManager Updated]
    J --> M[📝 blocks.js Updated]

    K --> N[📄 block.json]
    K --> O[📄 block.jsx]
    K --> P[📄 block.css]
    K --> Q[📄 block.blade.php]

    R[⚙️ Compile Assets] --> S[yarn build]
    S --> T[📦 Vite Processes]
    T --> U[🎨 CSS Compiled]
    T --> V[⚡ JS Compiled]

    W[📝 Use in WordPress] --> X[Gutenberg Editor]
    X --> Y[🔍 Search Block]
    Y --> Z[✨ Block Added]

    L --> AA[🔄 Auto Registration]
    M --> BB[🔗 Auto Import]
    AA --> CC[📋 WordPress Registry]
    BB --> DD[🎯 Block Available]

    style A fill:#e1f5fe
    style B fill:#f3e5f5
    style C fill:#f3e5f5
    style I fill:#e8f5e8
    style R fill:#fff3e0
    style W fill:#fce4ec
```

## �🚀 Installation

### Prerequisites
- WordPress 6.0+
- Sage/Acorn theme
- PHP 8.0+
- Node.js and npm

### 1. Install via Composer
```bash
composer config repositories.auto-blocks vcs https://github.com/Juliojar4/Auto-Blocks.git
composer require juliojar4/auto-blocks:dev-master
```

### 2.  Add files

Run the manual installation script:

```bash
bash vendor/juliojar4/auto-blocks/install-auto-blocks.sh
```

### 3. Environment-specific commands

**🐳 With Lando (detects .lando.yml):**
```bash
lando wp acorn make:block block-name
```

**💻 Direct WP-CLI:**
```bash
wp acorn make:block block-name
```

## 🚀 Quick Usage

### Create a new block:
Run in your theme
```bash
# With Lando
lando wp acorn make:block my-block

# Without Lando  
wp acorn make:block my-block
```

### Compile assets:
```bash
npm run build
# or
yarn build
```

### Verify created blocks:
```bash
# Sync existing blocks (Acorn command)
lando wp acorn blocks:sync

# Verify system installation
bash verify-system.sh
```

## 📁 Created Structure

After installation, the following files and directories will be created:

```
📁 app/
  📁 Blocks/
    📄 BlockManager.php           # Block manager
  📁 Console/
    📁 Commands/
      📄 MakeBlockCommand.php     # Command to create blocks
      📄 SyncBlocksCommand.php    # Command to synchronize
  📄 setup.php                    # BlockManager integration (added)

📁 resources/
  📁 blocks/                      # Directory for custom blocks
  📁 views/
    📁 blocks/                    # Blade templates for blocks
  📁 js/
    📄 blocks.js                  # Main JavaScript for blocks
    📄 app.js                     # Frontend JavaScript entry point
    📄 editor.js                  # Editor JavaScript entry point
  📁 css/
    📄 blocks.css                 # Block styles
  📄 blocks.php                   # PHP block configuration

📄 vite.config.js                 # Vite configuration (updated)
📄 sync-blocks.sh                # Script to sync imports
📄 verify-system.sh              # Script to verify installation
```

## 🏗️ Architecture Diagram

```mermaid
graph TB
    subgraph "🎯 WordPress Integration"
        WP[WordPress Core]
        GB[Gutenberg Editor]
        AC[Acorn Framework]
    end

    subgraph "📦 Auto Blocks System"
        BM[BlockManager.php<br/>📋 Block Registry]
        CMD[Commands<br/>🔧 Make & Sync]
        VITE[vite.config.js<br/>⚙️ Build Config]
    end

    subgraph "🎨 Block Assets"
        JS[blocks.js<br/>⚡ Main Entry]
        CSS[blocks.css<br/>🎨 Global Styles]
        BLOCKS[resources/blocks/<br/>📁 Block Folders]
    end

    subgraph "🧩 Individual Block"
        BJ[block.json<br/>📋 Block Definition]
        BJS[block.jsx<br/>⚡ Block Logic]
        BCSS[block.css<br/>🎨 Block Styles]
        BTPL[block.blade.php<br/>📄 Block Template]
    end

    WP --> BM
    BM --> GB
    AC --> CMD
    CMD --> BM
    CMD --> JS
    VITE --> JS
    VITE --> CSS
    JS --> BLOCKS
    CSS --> BLOCKS
    BLOCKS --> BJ
    BLOCKS --> BJS
    BLOCKS --> BCSS
    BLOCKS --> BTPL

    style WP fill:#e3f2fd
    style BM fill:#f3e5f5
    style CMD fill:#e8f5e8
    style VITE fill:#fff3e0
    style JS fill:#fce4ec
    style BJ fill:#e1f5fe
    style BJS fill:#f1f8e9
    style BCSS fill:#fff8e1
    style BTPL fill:#fce4ec
```

## 🎯 Usage Example

### 1. Create a block
```bash
lando wp acorn make:block promotional-banner
```

### 2. Compile
```bash
yarn build
```

### 3. Use in WordPress
- BlockManager will be automatically registered in WordPress
- Go to the block editor
- Search for "Promotional Banner"
- Add and configure!

## 📈 Block Creation Flow

```mermaid
sequenceDiagram
    participant Dev as Developer
    participant CMD as MakeBlockCommand
    participant FS as File System
    participant BM as BlockManager
    participant BJS as blocks.js
    participant VITE as Vite
    participant WP as WordPress

    Dev->>CMD: php artisan make:block hero-banner
    CMD->>FS: Create resources/blocks/hero-banner/
    FS-->>CMD: Directory created
    CMD->>FS: Create block.json, block.jsx, block.css, block.blade.php
    FS-->>CMD: Files created

    CMD->>BM: Add 'hero-banner' to blocks array
    BM-->>CMD: Block registered

    CMD->>BJS: Add import '../blocks/hero-banner/block.jsx'
    BJS-->>CMD: Import added

    CMD-->>Dev: ✅ Block created successfully!

    Dev->>VITE: yarn build
    VITE->>FS: Process block assets
    FS-->>VITE: Assets compiled

    VITE-->>Dev: ✅ Build complete!

    Dev->>WP: Open Gutenberg Editor
    WP->>BM: Load registered blocks
    BM-->>WP: hero-banner available
    WP-->>Dev: Block ready to use!
```

## 🔧 Available Commands

```bash
# Create simple block
lando wp acorn make:block block-name

# Sync existing blocks (Acorn command)
lando wp acorn blocks:sync

# Sync imports in blocks.js (bash script)
bash sync-blocks.sh

# Verify complete installation
bash verify-system.sh
```

## ⚠️ Common Issues and Solutions

### 1. Error "Could not open input file: artisan"
**Solution:** Use `lando wp acorn` instead of `php artisan`

### 2. Error in vite.config.js with glob
**Solution:** Already fixed in current version. If it happens, reinstall.

### 3. Error "Could not resolve entry module"
**Solution:** Run `yarn build` after creating blocks

### 4. Installation scripts didn't run
**Solution:** Manually run one of the installation scripts

### 5. Command make:block not found
**Solution:** Check if you're in the theme directory and Acorn is configured

### 6. Block created but doesn't appear in editor
**Solution:** The import wasn't automatically added to blocks.js:
```bash
# Run the automatic synchronizer
bash sync-blocks.sh

# Then compile
yarn build
```

### 7. Verify if everything is working correctly
**Solution:** Run the complete verification script:
```bash
bash verify-system.sh
```

## 📋 Requirements

- ✅ WordPress with Sage/Acorn theme
- ✅ PHP 8.0+
- ✅ Node.js and npm/yarn
- ✅ Composer
- ✅ WP-CLI (recommended)
- ✅ Lando (optional, but recommended)

## 🛠️ Technology Stack

```mermaid
mindmap
  root((🎨 Auto Blocks))
    WordPress
      Gutenberg
        Block API
        Block Editor
    PHP
      Laravel
        Acorn Framework
          Artisan Commands
      Composer
        Package Management
        Auto Installation
    JavaScript
      React
        JSX Syntax
        Component Logic
      ES6 Modules
        Import/Export
        Dynamic Loading
    CSS
      Tailwind CSS
        Utility Classes
        Responsive Design
      PostCSS
        Autoprefixer
        CSS Processing
    Build Tools
      Vite
        Fast HMR
        Asset Bundling
      Node.js
        npm/yarn
        Script Execution
    Development
      WP-CLI
        Command Line
        Theme Management
      Lando
        Local Development
        Container Management
```

## 🆘 Support

If you encounter issues:

1. ✅ Check if you're in the theme root directory
2. ✅ Confirm that Sage/Acorn is configured
3. ✅ Run the manual installation scripts
4. ✅ Verify that all files were created
5. ✅ Run `yarn build` after changes

## 🔍 Troubleshooting Guide

```mermaid
flowchart TD
    A[❌ Problem Occurred] --> B{What type of error?}
    
    B -->|Command not found| C[Check if in theme directory]
    B -->|Build error| D[Run yarn build]
    B -->|Block not showing| E[Check blocks.js imports]
    B -->|Installation failed| F[Run install script manually]
    
    C --> G[✅ Fixed?]
    D --> G
    E --> H[Run bash sync-blocks.sh]
    F --> I[Check file permissions]
    
    H --> G
    I --> G
    
    G -->|Yes| J[🎉 Success!]
    G -->|No| K[📋 Check Requirements]
    K --> L[📞 Contact Support]
```

## 🎉 Result

After successful installation, you will have:

- ✅ Complete block creation system
- ✅ Automated templates
- ✅ Automatic asset compilation
- ✅ Perfect Gutenberg integration
- ✅ Tailwind CSS support
- ✅ Hot reload during development

---

**Developed by Julio Jara**  
🔗 [GitHub](https://github.com/Juliojar4/Auto-Blocks)
