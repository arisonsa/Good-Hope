<?php

namespace App\Blocks;

use Roots\Acorn\Application;

class CarouselBlock
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
        add_action('init', [$this, 'registerBlock']);
    }

    public function registerBlock()
    {
        $editor_script_handle = 'charity-m3-carousel-editor-script';

        // The asset path needs to match an entry point in vite.config.ts
        $editor_asset_uri = \App\Vite::uri('app/Blocks/Carousel/edit.js');

        if ($editor_asset_uri) {
            wp_register_script(
                $editor_script_handle,
                $editor_asset_uri,
                ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'],
                false, // Versioning is handled by Vite manifest
                true
            );
        }

        register_block_type_from_metadata(
            CHARITY_M3_THEME_PATH . 'app/Blocks/Carousel',
            [
                'render_callback' => [$this, 'renderCarousel'],
            ]
        );
    }

    public function renderCarousel($attributes, $content, $block)
    {
        ob_start();
        include CHARITY_M3_THEME_PATH . 'app/Blocks/Carousel/render.php';
        return ob_get_clean();
    }
}
