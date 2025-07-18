import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin'
import { wordpressPlugin, wordpressThemeJson } from '@roots/vite-plugin';
import { glob } from 'glob';
import path from 'path';

// Descobrir automaticamente assets dos blocos
function discoverBlockAssets() {
  const blockAssets = [];
  
  // Encontrar todos os arquivos block.js e block.css
  const jsFiles = glob.sync('resources/blocks/*/block.js');
  const cssFiles = glob.sync('resources/blocks/*/block.css');
  
  return [...jsFiles, ...cssFiles];
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
