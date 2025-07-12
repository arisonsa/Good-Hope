<?php

namespace App\Providers;

use Roots\Acorn\ServiceProvider;
use App\View\Composers\AppComposer;
use App\Theme\CustomizerManager; // Add this

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
        // Boot services, add view composers, etc.
        // Example: Pass site name to all views
        \Roots\Blade::composer('*', AppComposer::class);

        // Load text domain
        add_action('after_setup_theme', function () {
            load_theme_textdomain('charity-m3', $this->app->resourcePath('lang'));
        });

        // Hook into theme activation to run database migrations
        add_action('after_switch_theme', [\App\Database\DatabaseManager::class, 'runMigrations']);

        // Add theme support, image sizes, nav menus, etc.
        // (Some of this is in functions.php for now, can be moved here)
        $this->addThemeSupports();
        $this->registerNavMenus();
        $this->addImageSizes();

        // Enqueue assets (can be done here or via a dedicated Assets service)
        add_action('wp_enqueue_scripts', [$this, 'enqueueThemeAssets']);

        // Register custom post types and taxonomies if any
        // add_action('init', [$this, 'registerPostTypes']);
        // add_action('init', [$this, 'registerTaxonomies']);

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
        // add_image_size('large_thumbnail', 700, 500, true);
    }

    /**
     * Enqueue theme assets.
     * This is a basic example. A more robust solution might use Acorn's asset management.
     */
    public function enqueueThemeAssets()
    {
        // Main stylesheet (style.css)
        wp_enqueue_style(
            'charity-m3-main',
            \Roots\asset('styles/main.css')->uri(), // Assumes you have a main.css in resources/styles built by a bundler
            false,
            null // Version will be handled by asset pipeline if configured
        );

        // Main JavaScript
        wp_enqueue_script(
            'charity-m3-main',
            \Roots\asset('scripts/main.js')->uri(), // Assumes you have a main.js in resources/scripts built by a bundler
            ['jquery'], // Dependencies
            null, // Version
            true // In footer
        );

        // Comment reply script
        if (is_singular() && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }

        // Localize script with data needed by frontend components
        // The handle 'charity-m3-main' must match the one used in wp_enqueue_script
        wp_localize_script('charity-m3-main', 'charityM3', [
            'stripePublicKey' => getenv('STRIPE_PUBLISHABLE_KEY') ?: '',
            // Add other global data if needed
        ]);

        // wp_localize_script for wpApiSettings is usually handled by WordPress core
        // when a script is enqueued with 'wp-api-fetch' as a dependency, but let's ensure it's there.
        // If not, we could add it manually, but it's better to rely on WP's handling.
        // Our REST API endpoint uses permission_callback and nonce checks, so 'wp-api-fetch' should be a dependency
        // for any script making such calls, or the nonce needs to be passed.
        // The Lit component currently assumes `window.wpApiSettings.nonce` exists.
    }

    // Placeholder for CPTs and Taxonomies
    // public function registerPostTypes() { /* ... */ }
    // public function registerTaxonomies() { /* ... */ }
}
