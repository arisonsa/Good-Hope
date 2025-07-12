<?php

namespace App\Providers;

use Roots\Acorn\ServiceProvider;
use App\Blocks\FeaturedCalloutBlock;
use App\Blocks\NewsletterSignupBlock;
use App\Blocks\CardGridBlock;
use App\Blocks\CardItemBlock;
use App\Blocks\DonationFormBlock;

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
use App\Blocks\DonationFormBlock; // Add this

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
        // ...
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
            new CardGridBlock($this->app);
            new CardItemBlock($this->app);
            new DonationFormBlock($this->app);
        }
    }
}
