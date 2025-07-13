<?php

namespace App\Providers;

use Roots\Acorn\ServiceProvider;

class BlockPatternsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        add_action('init', [$this, 'registerBlockPatterns']);
        add_action('init', [$this, 'registerBlockPatternCategory']);
    }

    /**
     * Register custom block pattern categories.
     */
    public function registerBlockPatternCategory()
    {
        register_block_pattern_category(
            'charity-m3-pages',
            ['label' => __('Charity M3 - Page Layouts', 'charity-m3')]
        );
    }

    /**
     * Register custom block patterns.
     */
    public function registerBlockPatterns()
    {
        // Program Landing Page Pattern
        register_block_pattern(
            'charity-m3/program-landing-page',
            [
                'title'       => __('Program Landing Page', 'charity-m3'),
                'description' => __('A full page layout for showcasing programs or services, including a hero, a grid of cards, and a call-to-action.', 'charity-m3'),
                'categories'  => ['charity-m3-pages'],
                'keywords'    => ['page', 'layout', 'program', 'services', 'landing'],
                'viewportWidth' => 1200,
                'content'     => $this->getProgramLandingPagePatternContent(),
            ]
        );
    }

    /**
     * Get the HTML content for the Program Landing Page pattern.
     *
     * @return string
     */
    private function getProgramLandingPagePatternContent(): string
    {
        // Using ob_start to make the multiline HTML more readable than a single long string.
        ob_start();
        ?>
        <!-- wp:charity-m3/featured-callout {"title":"Our Programs","subtitle":"Discover the areas where we create lasting impact.","backgroundColor":"var(--md-sys-color-surface-container)","textColor":"var(--md-sys-color-on-surface)","contentWidth":"wide","minHeight":"40vh","buttons":[]} -->
        <div class="wp-block-charity-m3-featured-callout alignwide"></div>
        <!-- /wp:charity-m3/featured-callout -->

        <!-- wp:paragraph {"align":"center","style":{"spacing":{"padding":{"top":"3rem","bottom":"3rem"}}},"fontSize":"large"} -->
        <p class="has-text-align-center has-large-font-size" style="padding-top:3rem;padding-bottom:3rem">This is an introductory paragraph where you can explain the overall goal and approach of your organization’s programs. Describe the collective impact you aim to achieve.</p>
        <!-- /wp:paragraph -->

        <!-- wp:charity-m3/card-grid {"cols":"3","gap":"6","align":"wide"} -->
        <!-- wp:charity-m3/card-item {"title":"Health Services","subtitle":"Well-being for All","text":"Providing access to essential health services, from emergency medical care to long-term health system strengthening.","imageUrl":"https://picsum.photos/seed/health/600/400","href":"#","variant":"elevated","interactive":true,"button1Text":"Learn More","button1Href":"#"} -->
        <div class="wp-block-charity-m3-card-item"></div>
        <!-- /wp:charity-m3/card-item -->

        <!-- wp:charity-m3/card-item {"title":"Emergency Response","subtitle":"Ready to Act","text":"Our teams are ready to deploy at a moment's notice, providing life-saving aid in the wake of disasters.","imageUrl":"https://picsum.photos/seed/emergency/600/400","href":"#","variant":"elevated","interactive":true,"button1Text":"Learn More","button1Href":"#"} -->
        <div class="wp-block-charity-m3-card-item"></div>
        <!-- /wp:charity-m3/card-item -->

        <!-- wp:charity-m3/card-item {"title":"Community Development","subtitle":"Building Resilience","text":"We partner with communities to build local capacity and create sustainable solutions for long-term well-being.","imageUrl":"https://picsum.photos/seed/community/600/400","href":"#","variant":"elevated","interactive":true,"button1Text":"Learn More","button1Href":"#"} -->
        <div class="wp-block-charity-m3-card-item"></div>
        <!-- /wp:charity-m3/card-item -->
        <!-- /wp:charity-m3/card-grid -->

        <!-- wp:spacer {"height":"4rem"} -->
        <div style="height:4rem" aria-hidden="true" class="wp-block-spacer"></div>
        <!-- /wp:spacer -->

        <!-- wp:charity-m3/featured-callout {"title":"Ready to Make a Difference?","subtitle":"Your support can change lives. Join us today in our mission to bring hope and aid to communities in need.","backgroundColor":"var(--md-sys-color-tertiary-container)","textColor":"var(--md-sys-color-on-tertiary-container)","align":"full","buttons":[{"text":"Donate Now","href":"#donate","type":"filled","icon":"volunteer_activism"},{"text":"Get Involved","href":"#volunteer","type":"outlined"}]} -->
        <div class="wp-block-charity-m3-featured-callout alignfull"></div>
        <!-- /wp:charity-m3/featured-callout -->
        <?php
        return ob_get_clean();
    }
}
