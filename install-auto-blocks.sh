#!/bin/bash

echo "🎨 Auto Blocks - Manual Installation"
echo "===================================="
echo ""

# Check if we're in a Sage/Acorn theme
if [ ! -f "style.css" ] || [ ! -f "functions.php" ] || [ ! -d "app" ]; then
    echo "❌ This doesn't appear to be a valid Sage/Acorn theme directory."
    echo "Make sure you're in the theme root directory (where style.css and functions.php are located)."
    exit 1
fi

echo "✅ Sage/Acorn theme detected!"

# Find package directory
PACKAGE_DIR=""
for path in "vendor/juliojar4/auto-blocks" "../vendor/juliojar4/auto-blocks" "../../vendor/juliojar4/auto-blocks" "../../../vendor/juliojar4/auto-blocks"; do
    if [ -d "$path" ]; then
        PACKAGE_DIR=$(realpath "$path")
        break
    fi
done

if [ -z "$PACKAGE_DIR" ]; then
    echo "❌ Auto-blocks package not found in vendor!"
    echo "Run: composer require juliojar4/auto-blocks:dev-master"
    exit 1
fi

echo "✅ Package found at: $PACKAGE_DIR"

# Create necessary directories
echo ""
echo "📁 Creating directories..."
mkdir -p resources/blocks
mkdir -p resources/views/blocks
mkdir -p public/build
mkdir -p app/Blocks
mkdir -p app/Console/Commands

echo "✅ Directories created!"

# Copy files
echo ""
echo "📄 Copying files..."

if [ -f "$PACKAGE_DIR/stubs/BlockManager.php" ]; then
    cp "$PACKAGE_DIR/stubs/BlockManager.php" "app/Blocks/BlockManager.php"
    echo "✅ BlockManager.php copied"
else
    echo "⚠️  BlockManager.php not found"
fi

if [ -f "$PACKAGE_DIR/stubs/MakeBlockCommand.php" ]; then
    cp "$PACKAGE_DIR/stubs/MakeBlockCommand.php" "app/Console/Commands/MakeBlockCommand.php"
    echo "✅ MakeBlockCommand.php copied"
else
    echo "⚠️  MakeBlockCommand.php not found"
fi

if [ -f "$PACKAGE_DIR/stubs/SyncBlocksCommand.php" ]; then
    cp "$PACKAGE_DIR/stubs/SyncBlocksCommand.php" "app/Console/Commands/SyncBlocksCommand.php"
    echo "✅ SyncBlocksCommand.php copied"
else
    echo "⚠️  SyncBlocksCommand.php not found"
fi

if [ -f "$PACKAGE_DIR/stubs/blocks.js" ]; then
    cp "$PACKAGE_DIR/stubs/blocks.js" "resources/js/blocks.js"
    echo "✅ blocks.js copied"
else
    echo "⚠️  blocks.js not found"
fi

if [ -f "$PACKAGE_DIR/stubs/app.js" ]; then
    cp "$PACKAGE_DIR/stubs/app.js" "resources/js/app.js"
    echo "✅ app.js copied"
else
    echo "⚠️  app.js not found"
fi

if [ -f "$PACKAGE_DIR/stubs/editor.js" ]; then
    cp "$PACKAGE_DIR/stubs/editor.js" "resources/js/editor.js"
    echo "✅ editor.js copied"
else
    echo "⚠️  editor.js not found"
fi

if [ -f "$PACKAGE_DIR/stubs/blocks.css" ]; then
    cp "$PACKAGE_DIR/stubs/blocks.css" "resources/css/blocks.css"
    echo "✅ blocks.css copied"
else
    echo "⚠️  blocks.css not found"
fi

if [ -f "$PACKAGE_DIR/stubs/vite.config.js" ]; then
    cp "$PACKAGE_DIR/stubs/vite.config.js" "vite.config.js"
    echo "✅ vite.config.js copied"
else
    echo "⚠️  vite.config.js not found"
fi

if [ -f "$PACKAGE_DIR/stubs/blocks.php" ]; then
    cp "$PACKAGE_DIR/stubs/blocks.php" "resources/blocks.php"
    echo "✅ blocks.php copied"
else
    echo "⚠️  blocks.php not found"
fi

if [ -f "$PACKAGE_DIR/stubs/setup.php" ]; then
    if [ -f "app/setup.php" ]; then
        # Check if integration already exists
        if grep -q "BlockManager" app/setup.php; then
            echo "✅ BlockManager already integrated in setup.php"
        else
            echo "➕ Adding BlockManager integration to setup.php"
            cat "$PACKAGE_DIR/stubs/setup.php" >> "app/setup.php"
            echo "✅ BlockManager integrated into setup.php"
        fi
    else
        # If setup.php doesn't exist, create with integration only
        cp "$PACKAGE_DIR/stubs/setup.php" "app/setup.php"
        echo "✅ setup.php created with BlockManager integration"
    fi
else
    echo "⚠️  setup.php not found in package"
fi

if [ -f "$PACKAGE_DIR/sync-blocks.sh" ]; then
    cp "$PACKAGE_DIR/sync-blocks.sh" "sync-blocks.sh"
    chmod +x "sync-blocks.sh"
    echo "✅ sync-blocks.sh copied"
else
    echo "⚠️  sync-blocks.sh not found"
fi

if [ -f "$PACKAGE_DIR/verify-system.sh" ]; then
    cp "$PACKAGE_DIR/verify-system.sh" "verify-system.sh"
    chmod +x "verify-system.sh"
    echo "✅ verify-system.sh copied"
else
    echo "⚠️  verify-system.sh not found"
fi

echo ""
echo "✅ Auto Blocks installed successfully!"
echo ""
echo "📋 Next steps:"
echo ""
echo "🔧 For LANDO environments:"
echo "  1. yarn install"
echo "  2. yarn build"
echo "  3. lando wp acorn make:block my-first-block --with-js --with-css"
echo "  4. bash sync-blocks.sh  (if import wasn't added automatically)"
echo "  5. yarn build"
echo "  6. ✅ BlockManager automatically integrated in setup.php - blocks will work!"
echo ""
echo "🔧 For environments WITHOUT LANDO:"
echo "  1. yarn install"
echo "  2. yarn build" 
echo "  3. wp acorn make:block my-first-block --with-js --with-css"
echo "  4. bash sync-blocks.sh  (if import wasn't added automatically)"
echo "  5. yarn build"
echo "  6. ✅ BlockManager automatically integrated in setup.php - blocks will work!"
echo ""
echo "⚠️  IMPORTANT: NEVER use 'php artisan' - always use 'lando wp acorn' or 'wp acorn'"
echo ""
