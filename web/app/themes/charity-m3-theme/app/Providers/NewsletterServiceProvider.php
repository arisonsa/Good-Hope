<?php

namespace App\Providers;

use Roots\Acorn\ServiceProvider;
use App\Services\SubscriberService;
use App\Services\CampaignService;
use App\Services\TrackingService;
use App\Admin\AdminManager;

class NewsletterServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Singleton binding for services - one instance throughout the application.
        $this->app->singleton(SubscriberService::class, function () {
            global $wpdb;
            return new SubscriberService($wpdb);
        });

        $this->app->singleton(CampaignService::class, function () {
            // CampaignService might need access to other services or $wpdb
            return new CampaignService();
        });

        $this->app->singleton(TrackingService::class, function () {
            global $wpdb;
            return new TrackingService($wpdb);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register Custom Post Type for Newsletter Campaigns
        $this->registerNewsletterCampaignCPT();

        // Initialize Admin Pages
        if (is_admin()) {
            $subscriberService = $this->app->make(SubscriberService::class);
            $campaignService = $this->app->make(CampaignService::class);
            $trackingService = $this->app->make(TrackingService::class);
            new AdminManager($subscriberService, $campaignService, $trackingService);
        }

        // Register REST API endpoints for tracking
        add_action('rest_api_init', [$this, 'registerTrackingRoutes']);

        // Register WP-GraphQL types and mutations
        add_action('graphql_register_types', [$this, 'registerGraphQLTypesAndMutations']);

        // Add other hooks related to the newsletter system if needed
        // For example, handling AJAX requests for subscriptions if not using REST/GraphQL.
    }

    /**
     * Register the Custom Post Type for Newsletter Campaigns.
     */
    protected function registerNewsletterCampaignCPT()
    {
        $labels = [
            'name'                  => _x('Newsletter Campaigns', 'Post type general name', 'charity-m3'),
            'singular_name'         => _x('Newsletter Campaign', 'Post type singular name', 'charity-m3'),
            'menu_name'             => _x('Newsletter', 'Admin Menu text', 'charity-m3'), // Main menu item
            'name_admin_bar'        => _x('Newsletter Campaign', 'Add New on Toolbar', 'charity-m3'),
            'add_new'               => __('Add New', 'charity-m3'),
            'add_new_item'          => __('Add New Campaign', 'charity-m3'),
            'new_item'              => __('New Campaign', 'charity-m3'),
            'edit_item'             => __('Edit Campaign', 'charity-m3'),
            'view_item'             => __('View Campaign', 'charity-m3'),
            'all_items'             => __('All Campaigns', 'charity-m3'), // Sub-menu item
            'search_items'          => __('Search Campaigns', 'charity-m3'),
            'parent_item_colon'     => __('Parent Campaigns:', 'charity-m3'),
            'not_found'             => __('No campaigns found.', 'charity-m3'),
            'not_found_in_trash'    => __('No campaigns found in Trash.', 'charity-m3'),
            'featured_image'        => _x('Campaign Cover Image', 'Overrides the “Featured Image” phrase for this post type.', 'charity-m3'),
            'set_featured_image'    => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type.', 'charity-m3'),
            'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type.', 'charity-m3'),
            'use_featured_image'    => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type.', 'charity-m3'),
            'archives'              => _x('Campaign archives', 'The post type archive label used in nav menus.', 'charity-m3'),
            'insert_into_item'      => _x('Insert into campaign', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post).', 'charity-m3'),
            'uploaded_to_this_item' => _x('Uploaded to this campaign', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post).', 'charity-m3'),
            'filter_items_list'     => _x('Filter campaigns list', 'Screen reader text for the filter links heading on the post type listing screen.', 'charity-m3'),
            'items_list_navigation' => _x('Campaigns list navigation', 'Screen reader text for the pagination heading on the post type listing screen.', 'charity-m3'),
            'items_list'            => _x('Campaigns list', 'Screen reader text for the items list heading on the post type listing screen.', 'charity-m3'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true, // Set to false if not publicly queryable/viewable
            'publicly_queryable' => true, // If public is true
            'show_ui'            => true, // Show in admin
            'show_in_menu'       => true, // Show under a top-level menu item (can be string for parent slug)
            'menu_position'      => 20, // Below Pages. Adjust as needed.
            'menu_icon'          => 'dashicons-email-alt', // Choose an appropriate Dashicon
            'query_var'          => true,
            'rewrite'            => ['slug' => 'newsletter-campaigns', 'with_front' => false],
            'capability_type'    => 'post', // Or a custom capability type for more granular control
            'has_archive'        => true, // Enable campaign archives if needed
            'hierarchical'       => false,
            'supports'           => ['title', 'editor', 'author', 'thumbnail', 'revisions', 'custom-fields'],
            // 'taxonomies'         => ['category', 'post_tag'], // If you want to categorize campaigns
            'show_in_rest'       => true, // Enable for Gutenberg and REST API
            'rest_base'          => 'newsletter-campaigns',
        ];

        register_post_type('newsletter_campaign', $args);

        // Register meta fields for the CPT
        $meta_fields = [
            '_campaign_subject' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            '_campaign_status' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'], // Consider enum if REST schema supports it
            '_campaign_scheduled_at' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'], // Store as ISO8601 or timestamp string
            '_campaign_sent_at' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            '_campaign_recipients_count' => ['type' => 'integer', 'sanitize_callback' => 'absint'],
            '_campaign_template_id' => ['type' => 'integer', 'sanitize_callback' => 'absint'],
            '_campaign_preheader_text' => ['type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field'],
        ];

        foreach ($meta_fields as $meta_key => $meta_args) {
            register_post_meta('newsletter_campaign', $meta_key, [
                'type' => $meta_args['type'],
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => $meta_args['sanitize_callback'],
                'auth_callback' => function () {
                    return current_user_can('edit_posts'); // Basic capability check
                }
            ]);
        }
    }

    /**
     * Register REST API routes for tracking.
     */
    public function registerTrackingRoutes()
    {
        // Open tracking endpoint: /wp-json/charitym3/v1/track/open/{campaign_id}/{subscriber_id}/pixel.png
        register_rest_route('charitym3/v1', '/track/open/(?P<campaign_id>\d+)/(?P<subscriber_id>\d+)/pixel.png', [
            'methods' => 'GET',
            'callback' => [$this, 'handleOpenTracking'],
            'permission_callback' => '__return_true', // Publicly accessible pixel
            'args' => [
                'campaign_id' => [
                    'validate_callback' => 'is_numeric',
                    'required' => true,
                ],
                'subscriber_id' => [
                    'validate_callback' => 'is_numeric',
                    'required' => true,
                ],
            ],
        ]);

        // Click tracking endpoint: /wp-json/charitym3/v1/track/click/{campaign_id}/{subscriber_id}
        // This will take a 'url' query parameter for the destination.
        register_rest_route('charitym3/v1', '/track/click/(?P<campaign_id>\d+)/(?P<subscriber_id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'handleClickTracking'],
            'permission_callback' => '__return_true',
             'args' => [
                'campaign_id' => [
                    'validate_callback' => 'is_numeric',
                    'required' => true,
                ],
                'subscriber_id' => [
                    'validate_callback' => 'is_numeric',
                    'required' => true,
                ],
                'url' => [ // The destination URL
                    'validate_callback' => function($param, $request, $key) { return is_string($param) && !empty($param); },
                    'sanitize_callback' => 'esc_url_raw',
                    'required' => true,
                ],
                 // Potentially add a hash/signature here for security to ensure the URL isn't tampered with
            ],
        ]);
    }

    /**
     * Handle open tracking pixel request.
     */
    public function handleOpenTracking(\WP_REST_Request $request)
    {
        $campaign_id = (int) $request['campaign_id'];
        $subscriber_id = (int) $request['subscriber_id'];

        /** @var \App\Services\TrackingService $trackingService */
        $trackingService = $this->app->make(\App\Services\TrackingService::class);
        $trackingService->recordEvent($campaign_id, $subscriber_id, 'open');

        // Output a 1x1 transparent GIF
        header('Content-Type: image/gif');
        // Smallest transparent GIF encoded as base64
        echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        exit;
    }

    /**
     * Handle click tracking request and redirect.
     */
    public function handleClickTracking(\WP_REST_Request $request)
    {
        $campaign_id = (int) $request['campaign_id'];
        $subscriber_id = (int) $request['subscriber_id'];
        $target_url = $request['url']; // Already sanitized by 'sanitize_callback' in register_rest_route

        if (empty($target_url) || !wp_http_validate_url($target_url)) {
            // Invalid or missing URL, redirect to homepage or show error
            wp_safe_redirect(home_url());
            exit;
        }

        /** @var \App\Services\TrackingService $trackingService */
        $trackingService = $this->app->make(\App\Services\TrackingService::class);
        $trackingService->recordEvent($campaign_id, $subscriber_id, 'click', $target_url);

        // Perform the redirect
        wp_safe_redirect($target_url, 302); // 302 Found (Temporary Redirect)
        exit;
    }

    /**
     * Register custom GraphQL types and mutations for the newsletter system.
     */
    public function registerGraphQLTypesAndMutations()
    {
        if (!function_exists('register_graphql_object_type') || !function_exists('register_graphql_mutation')) {
            // WPGraphQL is not active or functions not available.
            return;
        }

        // Define Subscriber Output Type (simplified)
        register_graphql_object_type('SubscriberOutputType', [
            'description' => __('Newsletter Subscriber Information', 'charity-m3'),
            'fields' => [
                'id' => ['type' => 'ID', 'description' => __('Subscriber ID', 'charity-m3')],
                'email' => ['type' => 'String', 'description' => __('Subscriber Email', 'charity-m3')],
                'name' => ['type' => 'String', 'description' => __('Subscriber Name', 'charity-m3')],
                'status' => ['type' => 'String', 'description' => __('Subscriber Status', 'charity-m3')],
            ],
        ]);

        // Define Mutation Output Type
        register_graphql_object_type('SubscribeToNewsletterOutput', [
            'description' => __('Output for the subscribeToNewsletter mutation.', 'charity-m3'),
            'fields' => [
                'success' => ['type' => ['non_null' => 'Boolean'], 'description' => __('Whether the subscription was successful.', 'charity-m3')],
                'message' => ['type' => 'String', 'description' => __('A message detailing the result of the subscription attempt.', 'charity-m3')],
                'subscriber' => ['type' => 'SubscriberOutputType', 'description' => __('The subscriber data if successful.', 'charity-m3')],
            ],
        ]);

        // Register the mutation
        register_graphql_mutation('subscribeToNewsletter', [
            'inputFields' => [
                'email' => ['type' => ['non_null' => 'String'], 'description' => __('The email address to subscribe.', 'charity-m3')],
                'name' => ['type' => 'String', 'description' => __('The name of the subscriber (optional).', 'charity-m3')],
                // Add other fields like 'source' if needed.
            ],
            'outputFields' => [
                'result' => ['type' => 'SubscribeToNewsletterOutput', 'description' => __('The result of the subscription.', 'charity-m3')],
            ],
            'mutateAndGetPayload' => function ($input, $context, $info) {
                /** @var \App\Services\SubscriberService $subscriberService */
                $subscriberService = $this->app->make(\App\Services\SubscriberService::class);

                $email = sanitize_email($input['email']);
                $name = isset($input['name']) ? sanitize_text_field($input['name']) : null;

                if (!is_email($email)) {
                    return ['result' => [
                        'success' => false,
                        'message' => __('Invalid email address provided.', 'charity-m3'),
                        'subscriber' => null,
                    ]];
                }

                $subscriber_data = [
                    'name' => $name,
                    'status' => 'pending', // Or 'subscribed'
                    'source' => 'graphql_api_signup',
                ];

                $subscriber_id = $subscriberService->addSubscriber($email, $subscriber_data);

                if ($subscriber_id) {
                    $new_subscriber_obj = $subscriberService->getSubscriberById($subscriber_id);
                    return ['result' => [
                        'success' => true,
                        'message' => __('Successfully subscribed! Please check your email to confirm.', 'charity-m3'),
                        'subscriber' => $new_subscriber_obj ? [
                            'id' => $new_subscriber_obj->id,
                            'email' => $new_subscriber_obj->email,
                            'name' => $new_subscriber_obj->name,
                            'status' => $new_subscriber_obj->status,
                        ] : null,
                    ]];
                } else {
                    $existing_subscriber = $subscriberService->getSubscriberByEmail($email);
                     if ($existing_subscriber && $existing_subscriber->status === 'subscribed') {
                         return ['result' => [
                            'success' => true, // Or false, depending on desired behavior for already subscribed
                            'message' => __('You are already subscribed.', 'charity-m3'),
                            'subscriber' => [
                                'id' => $existing_subscriber->id,
                                'email' => $existing_subscriber->email,
                                'name' => $existing_subscriber->name,
                                'status' => $existing_subscriber->status,
                            ],
                        ]];
                    }
                    return ['result' => [
                        'success' => false,
                        'message' => __('Could not process your subscription. Please try again.', 'charity-m3'),
                        'subscriber' => null,
                    ]];
                }
            },
        ]);

        // TODO: Add GraphQL queries for fetching campaigns (e.g., allCampaigns, campaignById)
        // TODO: Add GraphQL mutations for unsubscribing.
    }
}
