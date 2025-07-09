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
            // The constructor of these classes should hook into 'init' to register the block.
            new FeaturedCalloutBlock($this->app);

            // Ensure NewsletterSignupBlock is also instantiated if its registration is in its constructor
            // If NewsletterSignupBlock's registration is already handled elsewhere (e.g. directly in NewsletterServiceProvider's boot),
            // then no need to instantiate it again here.
            // For consistency, let's assume all block registrations are handled by instantiating their respective classes.
            new NewsletterSignupBlock($this->app);
        }
    }
}
