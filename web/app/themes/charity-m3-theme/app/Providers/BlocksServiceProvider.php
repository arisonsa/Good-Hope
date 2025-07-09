<?php

namespace App\Providers;

use Roots\Acorn\ServiceProvider;
use App\Blocks\FeaturedCalloutBlock;
use App\Blocks\NewsletterSignupBlock; // Assuming this is its class name

class BlocksServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Could bind block classes to the container if they have complex dependencies
        // $this->app->singleton(FeaturedCalloutBlock::class, function ($app) {
        //     return new FeaturedCalloutBlock($app);
        // });
        // $this->app->singleton(NewsletterSignupBlock::class, function ($app) {
        //     return new NewsletterSignupBlock($app);
        // });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (function_exists('register_block_type_from_metadata')) {
            // Instantiate block registration classes
            // Their constructors hook into 'init' to register the blocks.
            new FeaturedCalloutBlock($this->app);
            new NewsletterSignupBlock($this->app);
            new CardGridBlock($this->app);      // Add this
            new CardItemBlock($this->app);      // Add this
        }
    }
}
