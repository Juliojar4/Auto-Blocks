import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin'
import { wordpressPlugin, wordpressThemeJson } from '@roots/vite-plugin';
import fs from 'fs';
import path from 'path';

// Descobrir automaticamente assets dos blocos
function discoverBlockAssets() {
  const blockAssets = [];
  const blocksDir = 'resources/blocks';
  
  // Verificar se o diretório existe
  if (!fs.existsSync(blocksDir)) {
    return blockAssets;
  }
  
  // Ler todos os diretórios de blocos
  const blockDirs = fs.readdirSync(blocksDir, { withFileTypes: true })
    .filter(dirent => dirent.isDirectory())
    .map(dirent => dirent.name);
  
  // Para cada diretório de bloco, procurar por assets
  blockDirs.forEach(blockDir => {
    const blockPath = path.join(blocksDir, blockDir);
    
    // Verificar se existe block.js
    const jsFile = path.join(blockPath, 'block.js');
    if (fs.existsSync(jsFile)) {
      blockAssets.push(jsFile);
    }
    
    // Verificar se existe block.css
    const cssFile = path.join(blockPath, 'block.css');
    if (fs.existsSync(cssFile)) {
      blockAssets.push(cssFile);
    }
  });
  
  return blockAssets;
}

export default defineConfig({
  base: '/app/themes/sage/public/build/',
  plugins: [
    tailwindcss(),
    laravel({
      input: [
        // Assets principais
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/css/editor.css',
        'resources/js/editor.js',
        'resources/js/blocks.js',
        'resources/css/blocks.css',
        // Assets dos blocos descobertos automaticamente
        ...discoverBlockAssets(),
      ],
      refresh: true,
    }),

    wordpressPlugin(),

    // Generate the theme.json file in the public/build/assets directory
    // based on the Tailwind config and the theme.json file from base theme folder
    wordpressThemeJson({
      disableTailwindColors: false,
      disableTailwindFonts: false,
      disableTailwindFontSizes: false,
    }),
  ],
  resolve: {
    alias: {
      '@scripts': '/resources/js',
      '@styles': '/resources/css',
      '@fonts': '/resources/fonts',
      '@images': '/resources/images',
    },
  },
})
