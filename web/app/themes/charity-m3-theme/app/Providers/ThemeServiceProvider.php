<?php

namespace App\Providers;

use Roots\Acorn\ServiceProvider;
use App\View\Composers\AppComposer;
use App\Theme\CustomizerManager;
use App\Vite; // Add this

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register your service providers, commands, etc. here
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Instantiate our Vite helper
        new Vite();

        // Boot services, add view composers, etc.
        \Roots\Blade::composer('*', AppComposer::class);

        // Load text domain
        add_action('after_setup_theme', function () {
            load_theme_textdomain('charity-m3', $this->app->resourcePath('lang'));
        });

        // Hook into theme activation to run database migrations
        add_action('after_switch_theme', [\App\Database\DatabaseManager::class, 'runMigrations']);

        // Add theme support, image sizes, nav menus, etc.
        $this->addThemeSupports();
        $this->registerNavMenus();
        $this->addImageSizes();
        $this->registerSidebars();

        // Enqueue assets using the new Vite helper
        add_action('wp_enqueue_scripts', [$this, 'enqueueThemeAssets']);

        // Add Google Fonts for Material Symbols and Roboto
        add_action('wp_head', [$this, 'addGoogleFonts']);

        // Initialize Customizer settings
        if (is_customize_preview() || is_admin()) {
            new CustomizerManager();
        }

        // Initialize Admin Pages
        if (is_admin()) {
            $this->bootAdminManager();
        }
    }

    /**
     * Instantiate the AdminManager with all necessary services.
     */
    protected function bootAdminManager()
    {
        // Use the app container to resolve singleton services
        $subscriberService = $this->app->make(\App\Services\SubscriberService::class);
        $campaignService   = $this->app->make(\App\Services\CampaignService::class);
        $trackingService   = $this->app->make(\App\Services\TrackingService::class);
        $donationService   = $this->app->make(\App\Services\DonationService::class);

        new \App\Admin\AdminManager(
            $subscriberService,
            $campaignService,
            $trackingService,
            $donationService
        );
    }

    /**
     * Add Google Fonts for Material Symbols and Roboto.
     */
    public function addGoogleFonts()
    {
        echo "<link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">\n";
        echo "<link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>\n";
        echo "<link href=\"https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap\" rel=\"stylesheet\">\n";
        // Material Symbols - Outlined is often a good default
        echo "<link href=\"https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200\" rel=\"stylesheet\" />\n";
    }

    /**
     * Add theme supports.
     */
    protected function addThemeSupports()
    {
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('html5', [
            'caption', 'comment-form', 'comment-list', 'gallery', 'search-form', 'script', 'style'
        ]);
        add_theme_support('editor-styles'); // For Gutenberg
        add_theme_support('wp-block-styles'); // For Gutenberg block styles
        add_theme_support('align-wide'); // For wide and full-width Gutenberg blocks
        add_theme_support('responsive-embeds');
        add_theme_support('custom-logo', [ // Add custom logo support
            // Optional: define height and width.
            // 'height'      => 100,
            // 'width'       => 400,
            'flex-height' => true,
            'flex-width'  => true,
            // 'header-text' => ['site-title', 'site-description'], // Classes to hide if logo is set
        ]);
        // Add other supports as needed
    }

    /**
     * Register navigation menus.
     */
    protected function registerNavMenus()
    {
        register_nav_menus([
            'primary_navigation' => __('Primary Navigation', 'charity-m3'),
            'footer_navigation' => __('Footer Navigation', 'charity-m3'),
        ]);
    }

    /**
     * Register custom image sizes.
     */
    protected function addImageSizes()
    {
        add_image_size('card-thumbnail', 600, 400, true); // For our 3-column card grid
        add_image_size('hero-large', 1920, 1080, true); // For full-width hero sections
    }

    /**
     * Enqueue theme assets.
     * This is a basic example. A more robust solution might use Acorn's asset management.
     */
    public function enqueueThemeAssets()
    {
        // Output the Vite HMR client script in development
        if ($hmr_script = Vite::hmrScript()) {
            echo $hmr_script;
        }

        // Enqueue main stylesheet and script from Vite manifest
        wp_enqueue_style('charity-m3-main-style', Vite::uri('resources/styles/main.scss'), [], null);
        wp_enqueue_script('charity-m3-main-script', Vite::uri('resources/scripts/main.ts'), ['wp-api-fetch'], null, true);

        // Localize script with data needed by frontend components
        wp_localize_script('charity-m3-main-script', 'charityM3', [
            'stripePublicKey' => getenv('STRIPE_PUBLISHABLE_KEY') ?: '',
        ]);

        // Comment reply script
        if (is_singular() && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }
    }

    // Placeholder for CPTs and Taxonomies
    // public function registerPostTypes() { /* ... */ }
    // public function registerTaxonomies() { /* ... */ }
}
