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
        // Register editor script handle (matches block.json)
        $editor_script_handle = 'charity-m3-card-grid-editor-script';
        $editor_asset_uri = \App\Vite::uri('app/Blocks/CardGrid/edit.js');

        if ($editor_asset_uri) {
            wp_register_script(
                $editor_script_handle,
                $editor_asset_uri,
                ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-polyfill'],
                false,
                true
            );
        } elseif (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Card Grid editor script not found for Vite.");
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
