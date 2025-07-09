<?php

namespace App\Blocks;

use Roots\Acorn\Application;

class CardItemBlock
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
        $editor_script_handle = 'charity-m3-card-item-editor-script';
        $editor_asset_path = \Roots\asset('scripts/blocks/card-item-editor.js'); // Ensure this path matches webpack output

        if ($editor_asset_path->exists()) {
            wp_register_script(
                $editor_script_handle,
                $editor_asset_path->uri(),
                ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-polyfill', 'wp-url'], // wp-url for URLInput
                $editor_asset_path->version(),
                true
            );
        } elseif (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Card Item editor script not found at: " . $editor_asset_path->path());
        }

        register_block_type_from_metadata(
            CHARITY_M3_THEME_PATH . 'app/Blocks/CardItem', // Path to block.json directory
            [
                'render_callback' => [$this, 'renderCardItem'],
                // 'editor_script' is already defined in block.json as our handle.
            ]
        );
    }

    /**
     * Render callback for the Card Item block.
     */
    public function renderCardItem($attributes, $content, $block)
    {
        // $content for an inner block like this is typically empty unless it also allows InnerBlocks.
        // Our card text comes from an attribute.
        ob_start();
        include CHARITY_M3_THEME_PATH . 'app/Blocks/CardItem/render.php';
        return ob_get_clean();
    }
}
