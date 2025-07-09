<?php

namespace App\Blocks;

use Roots\Acorn\Application;

class CardGridBlock
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
        add_action('init', [$this, 'registerBlock']);
    }

    public function registerBlock()
    {
        // Register editor script handle (matches block.json and webpack output)
        $editor_script_handle = 'charity-m3-card-grid-editor-script';
        $editor_asset_path = \Roots\asset('scripts/blocks/card-grid-editor.js'); // Ensure this path matches webpack output

        if ($editor_asset_path->exists()) {
            wp_register_script(
                $editor_script_handle,
                $editor_asset_path->uri(),
                ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-polyfill'],
                $editor_asset_path->version(),
                true
            );
        } elseif (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Card Grid editor script not found at: " . $editor_asset_path->path());
        }

        register_block_type_from_metadata(
            CHARITY_M3_THEME_PATH . 'app/Blocks/CardGrid', // Path to block.json directory
            [
                'render_callback' => [$this, 'renderCardGrid'],
                // 'editor_script' is already defined in block.json as our handle.
            ]
        );
    }

    /**
     * Render callback for the Card Grid block.
     */
    public function renderCardGrid($attributes, $content, $block)
    {
        // $content here is the rendered HTML of InnerBlocks (CardItem blocks)
        ob_start();
        // Pass $attributes and $content (as $slotContent) to the render.php script
        include CHARITY_M3_THEME_PATH . 'app/Blocks/CardGrid/render.php';
        return ob_get_clean();
    }
}
