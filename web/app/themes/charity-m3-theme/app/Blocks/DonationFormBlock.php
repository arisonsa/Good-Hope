<?php

namespace App\Blocks;

use Roots\Acorn\Application;

class DonationFormBlock
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
        $editor_script_handle = 'charity-m3-donation-form-editor-script';
        $editor_asset_uri = \App\Vite::uri('app/Blocks/DonationForm/edit.js');

        if ($editor_asset_uri) {
            wp_register_script(
                $editor_script_handle,
                $editor_asset_uri,
                ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-polyfill'],
                false,
                true
            );
        } elseif (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Donation Form editor script not found for Vite.");
        }

        register_block_type_from_metadata(
            CHARITY_M3_THEME_PATH . 'app/Blocks/DonationForm', // Path to block.json directory
            [
                'render_callback' => [$this, 'renderDonationForm'],
                // 'editor_script' is already defined in block.json as our handle.
            ]
        );
    }

    /**
     * Render callback for the Donation Form block.
     */
    public function renderDonationForm($attributes, $content, $block)
    {
        ob_start();
        include CHARITY_M3_THEME_PATH . 'app/Blocks/DonationForm/render.php';
        return ob_get_clean();
    }
}
