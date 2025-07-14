<?php

namespace App\Providers;

use Roots\Acorn\ServiceProvider;

class CPTServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        add_action('init', [$this, 'registerProgramCPT']);
        add_action('init', [$this, 'registerImpactStoryCPT']);
        add_action('init', [$this, 'registerAlertCPT']);
        // Optional: Register taxonomies here as well
        // add_action('init', [$this, 'registerProgramCategoryTaxonomy']);
    }

    /**
     * Register the 'Alert' Custom Post Type.
     */
    public function registerAlertCPT()
    {
        $labels = [
            'name'                  => _x('Alerts', 'Post type general name', 'charity-m3'),
            'singular_name'         => _x('Alert', 'Post type singular name', 'charity-m3'),
            'menu_name'             => _x('Crisis Alerts', 'Admin Menu text', 'charity-m3'),
            'add_new_item'          => __('Add New Alert', 'charity-m3'),
            'edit_item'             => __('Edit Alert', 'charity-m3'),
            'new_item'              => __('New Alert', 'charity-m3'),
        ];
        $args = [
            'labels'             => $labels,
            'public'             => false, // Not for public browsing
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_nav_menus'  => false,
            'exclude_from_search'=> true,
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 5, // High priority
            'menu_icon'          => 'dashicons-warning',
            'supports'           => ['title', 'editor', 'custom-fields'],
            'show_in_rest'       => true, // Needed for Customizer control
        ];
        register_post_type('alert', $args);

        // Register meta fields
        register_post_meta('alert', '_alert_link_url', ['type' => 'string', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => 'esc_url_raw']);
        register_post_meta('alert', '_alert_button_text', ['type' => 'string', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => 'sanitize_text_field']);
        register_post_meta('alert', '_alert_urgency', ['type' => 'string', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => 'sanitize_text_field']);
    }

    /**
     * Register the 'Program' Custom Post Type.
     */
    public function registerProgramCPT()
    {
        $labels = [
            'name'                  => _x('Programs', 'Post type general name', 'charity-m3'),
            'singular_name'         => _x('Program', 'Post type singular name', 'charity-m3'),
            'menu_name'             => _x('Programs', 'Admin Menu text', 'charity-m3'),
            'add_new_item'          => __('Add New Program', 'charity-m3'),
            'edit_item'             => __('Edit Program', 'charity-m3'),
            'new_item'              => __('New Program', 'charity-m3'),
            'view_item'             => __('View Program', 'charity-m3'),
            'search_items'          => __('Search Programs', 'charity-m3'),
            'not_found'             => __('No programs found.', 'charity-m3'),
            'not_found_in_trash'    => __('No programs found in Trash.', 'charity-m3'),
        ];
        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'programs', 'with_front' => false],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 21, // Below Newsletter
            'menu_icon'          => 'dashicons-clipboard',
            'supports'           => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions'],
            'show_in_rest'       => true,
            'show_in_graphql'    => true,
            'graphql_single_name' => 'Program',
            'graphql_plural_name' => 'Programs',
        ];
        register_post_type('program', $args);
    }

    /**
     * Register the 'Impact Story' Custom Post Type.
     */
    public function registerImpactStoryCPT()
    {
        $labels = [
            'name'                  => _x('Impact Stories', 'Post type general name', 'charity-m3'),
            'singular_name'         => _x('Impact Story', 'Post type singular name', 'charity-m3'),
            'menu_name'             => _x('Impact Stories', 'Admin Menu text', 'charity-m3'),
            'add_new_item'          => __('Add New Impact Story', 'charity-m3'),
            'edit_item'             => __('Edit Impact Story', 'charity-m3'),
            'new_item'              => __('New Impact Story', 'charity-m3'),
            'view_item'             => __('View Impact Story', 'charity-m3'),
            'search_items'          => __('Search Impact Stories', 'charity-m3'),
            'not_found'             => __('No stories found.', 'charity-m3'),
            'not_found_in_trash'    => __('No stories found in Trash.', 'charity-m3'),
        ];
        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'stories', 'with_front' => false],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 22,
            'menu_icon'          => 'dashicons-format-aside',
            'supports'           => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions'],
            'show_in_rest'       => true,
            'show_in_graphql'    => true,
            'graphql_single_name' => 'ImpactStory',
            'graphql_plural_name' => 'ImpactStories',
        ];
        register_post_type('impact_story', $args);
    }
}
