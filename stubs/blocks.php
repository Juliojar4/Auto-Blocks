<?php

use App\Blocks\BlockManager;

/*
|--------------------------------------------------------------------------
| Register Custom Blocks
|--------------------------------------------------------------------------
|
| Este arquivo é responsável por registrar todos os blocos Gutenberg customizados.
| O BlockManager descobrirá e registrará automaticamente todos os blocos
| listados no array $blocks.
|
*/

// Inicializar e registrar todos os blocos
add_action('init', function () {
    $blockManager = new BlockManager();
    $blockManager->register();
});
