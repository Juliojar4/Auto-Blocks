use App\Blocks\BlockManager;

/**
 * Register Auto Blocks system
 */
add_action('init', function () {
    $blockManager = new BlockManager();
    $blockManager->register();
});
