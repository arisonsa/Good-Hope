<?php

namespace App\Blocks;

use Roots\Acorn\Application;

class FeaturedCalloutBlock
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
        add_action('init', [$this, 'registerBlock']);
        // Potentially add custom block category here if not done elsewhere
        add_filter('block_categories_all', [$this, 'addBlockCategory'], 10, 2);
    }

    public function registerBlock()
    {
        // Define the handle matching block.json's editorScript
        $editor_script_handle = 'charity-m3-featured-callout-editor-script';
        $editor_asset_path = \Roots\asset('scripts/blocks/featured-callout-editor.js');

        if ($editor_asset_path->exists()) {
            wp_register_script(
                $editor_script_handle,
                $editor_asset_path->uri(),
                ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-polyfill', 'uuid'], // Added uuid
                $editor_asset_path->version(), // Use filemtime for versioning if manifest doesn't provide it
                true // In footer
            );
        } else {
            // Log error or add admin notice if script is missing in development
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Featured Callout editor script not found at: " . $editor_asset_path->path());
            }
        }

        // Ensure the NewsletterSignupBlock's editor script is also registered similarly if not already.
        // Let's assume its block.json also uses a handle like 'charity-m3-newsletter-signup-editor-script'
        // and its registration logic in NewsletterSignupBlock.php would handle wp_register_script.
        // For now, focusing on FeaturedCalloutBlock.

        register_block_type_from_metadata(
            CHARITY_M3_THEME_PATH . 'app/Blocks/FeaturedCallout',
            [
                'render_callback' => [$this, 'renderFeaturedCallout'],
                // 'editor_script' is already defined in block.json as our handle.
                // WordPress will use the registered script with this handle.
            ]
        );
    }

    /**
     * Render callback for the block.
     */
    public function renderFeaturedCallout($attributes, $content, $block)
    {
        // Use a separate render.php file for cleaner organization
        // Pass $this->app if the render script needs access to Acorn container/services
        // For now, render.php is self-contained or uses global functions.
        $attributes['blockId'] = $attributes['blockId'] ?? 'charity-m3-fc-' . $block->parsed_block['clientId'] ?? uniqid();


        ob_start();
        include CHARITY_M3_THEME_PATH . 'app/Blocks/FeaturedCallout/render.php';
        return ob_get_clean();
    }

    /**
     * Add a custom block category for Charity M3 components.
     */
    public function addBlockCategory($categories, $post)
    {
        // Check if category already exists
        $category_slugs = wp_list_pluck($categories, 'slug');
        if (!in_array('charity-m3-components', $category_slugs, true)) {
            $categories = array_merge(
                $categories,
                [
                    [
                        'slug'  => 'charity-m3-components',
                        'title' => __('Charity M3 Components', 'charity-m3'),
                        'icon'  => 'star-filled', // Or a custom SVG icon
                    ],
                ]
            );
        }
        return $categories;
    }
}
