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
        $editor_asset_path = \Roots\asset('scripts/blocks/carousel-editor.js');

        if ($editor_asset_path->exists()) {
            wp_register_script(
                $editor_script_handle,
                $editor_asset_path->uri(),
                ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'],
                $editor_asset_path->version(),
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
