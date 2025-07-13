<?php

namespace App\Services;

// May need Wpdb or other WP functions if not just using CPT functions
// use wpdb;

class CampaignService
{
    public const CPT_SLUG = 'newsletter_campaign';

    public function __construct()
    {
        // Constructor if needed, e.g., for injecting dependencies
    }

    /**
     * Get campaign data by ID.
     *
     * @param int $campaign_id
     * @return \WP_Post|null Post object or null if not found.
     */
    public function getCampaignById(int $campaign_id): ?\WP_Post
    {
        $post = get_post($campaign_id);
        if ($post && $post->post_type === self::CPT_SLUG) {
            return $post;
        }
        return null;
    }

    /**
     * Get all campaign metadata.
     *
     * @param int $campaign_id
     * @return array
     */
    public function getCampaignMeta(int $campaign_id): array
    {
        $meta = get_post_meta($campaign_id);
        $processed_meta = [];
        foreach ($meta as $key => $value) {
            // Only include our campaign-specific meta, and unserialize if single value
            if (strpos($key, '_campaign_') === 0) {
                $processed_meta[substr($key, 1)] = maybe_unserialize($value[0]); // remove leading underscore
            }
        }
        return $processed_meta;
    }

    /**
     * Update specific campaign metadata.
     *
     * @param int $campaign_id
     * @param string $meta_key (e.g., 'campaign_subject', 'campaign_status')
     * @param mixed $meta_value
     * @return bool|int
     */
    public function updateCampaignMeta(int $campaign_id, string $meta_key, $meta_value)
    {
        // Ensure meta_key starts with underscore as per our convention in CPT registration
        $wp_meta_key = (strpos($meta_key, '_') === 0) ? $meta_key : '_' . $meta_key;

        // Sanitize based on meta key
        switch ($wp_meta_key) {
            case '_campaign_subject':
            case '_campaign_preheader_text':
                $meta_value = sanitize_text_field($meta_value);
                break;
            case '_campaign_status':
                $allowed_statuses = ['draft', 'scheduled', 'sending', 'sent', 'archived'];
                $meta_value = in_array($meta_value, $allowed_statuses) ? $meta_value : 'draft';
                break;
            case '_campaign_scheduled_at':
            case '_campaign_sent_at':
                // Validate datetime format if necessary, or ensure it's null
                $meta_value = $meta_value ? gmdate('Y-m-d H:i:s', strtotime($meta_value)) : null;
                break;
            case '_campaign_recipients_count':
            case '_campaign_template_id':
                $meta_value = intval($meta_value);
                break;
        }

        return update_post_meta($campaign_id, $wp_meta_key, $meta_value);
    }

    /**
     * Create a new campaign.
     *
     * @param array $campaign_data {
     *     'post_title' => string,
     *     'post_content' => string,
     *     'post_status' => string ('draft', 'publish', etc. for the CPT itself)
     *     'meta' => [ 'campaign_subject' => 'Subject', ... ] // Our custom meta
     * }
     * @return int|\WP_Error Campaign ID on success, \WP_Error on failure.
     */
    public function createCampaign(array $campaign_data)
    {
        $cpt_data = [
            'post_type'    => self::CPT_SLUG,
            'post_title'   => sanitize_text_field($campaign_data['post_title'] ?? 'New Campaign'),
            'post_content' => wp_kses_post($campaign_data['post_content'] ?? ''),
            'post_status'  => sanitize_text_field($campaign_data['post_status'] ?? 'draft'), // CPT status
            'post_author'  => get_current_user_id(),
        ];

        $campaign_id = wp_insert_post($cpt_data, true);

        if (is_wp_error($campaign_id)) {
            return $campaign_id;
        }

        // Set default meta if not provided, especially our internal campaign status
        $meta_input = $campaign_data['meta'] ?? [];
        if (!isset($meta_input['campaign_status'])) {
             $this->updateCampaignMeta($campaign_id, 'campaign_status', 'draft');
        }
        if (!isset($meta_input['campaign_subject']) && isset($cpt_data['post_title'])) {
            $this->updateCampaignMeta($campaign_id, 'campaign_subject', $cpt_data['post_title']);
       }


        foreach ($meta_input as $key => $value) {
            $this->updateCampaignMeta($campaign_id, $key, $value);
        }

        return $campaign_id;
    }

    /**
     * Update an existing campaign.
     *
     * @param int $campaign_id
     * @param array $campaign_data (Similar structure to createCampaign)
     * @return int|\WP_Error Campaign ID on success, \WP_Error on failure.
     */
    public function updateCampaign(int $campaign_id, array $campaign_data)
    {
        $cpt_data = ['ID' => $campaign_id]; // Must include ID for wp_update_post

        if (isset($campaign_data['post_title'])) {
            $cpt_data['post_title'] = sanitize_text_field($campaign_data['post_title']);
        }
        if (isset($campaign_data['post_content'])) {
            $cpt_data['post_content'] = wp_kses_post($campaign_data['post_content']);
        }
        if (isset($campaign_data['post_status'])) {
            $cpt_data['post_status'] = sanitize_text_field($campaign_data['post_status']);
        }

        $result = wp_update_post($cpt_data, true);

        if (is_wp_error($result)) {
            return $result;
        }

        if (isset($campaign_data['meta']) && is_array($campaign_data['meta'])) {
            foreach ($campaign_data['meta'] as $key => $value) {
                $this->updateCampaignMeta($campaign_id, $key, $value);
            }
        }

        return $campaign_id;
    }

    /**
     * Delete a campaign.
     *
     * @param int $campaign_id
     * @param bool $force_delete Whether to bypass trash and force delete.
     * @return mixed \WP_Post|false|null Post data on success, false or null on failure.
     */
    public function deleteCampaign(int $campaign_id, bool $force_delete = false)
    {
        return wp_delete_post($campaign_id, $force_delete);
    }

    /**
     * Get campaigns with pagination, filtering, and sorting.
     *
     * @param array $args Arguments for WP_Query.
     * @return array { 'items': \WP_Post[], 'total_items': int }
     */
    public function getCampaigns(array $args = []): array
    {
        $defaults = [
            'post_type' => self::CPT_SLUG,
            'posts_per_page' => 20,
            'paged' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
            // Add more WP_Query args as needed (e.g., meta_query for campaign_status)
        ];
        $query_args = wp_parse_args($args, $defaults);

        $query = new \WP_Query($query_args);

        return [
            'items' => $query->get_posts(),
            'total_items' => (int) $query->found_posts,
        ];
    }

    // Placeholder for sending logic
    public function sendCampaign(int $campaign_id, array $subscriber_ids = [])
    {
        // This will be complex:
        // 1. Get campaign content.
        // 2. Get subscriber emails.
        // 3. Loop and send emails (batching is crucial).
        // 4. Integrate with an email sending service or wp_mail().
        // 5. Update campaign status to 'sending' then 'sent'.
        // 6. Log results.
        // For now, just a placeholder.
        $this->updateCampaignMeta($campaign_id, 'campaign_status', 'sent');
        $this->updateCampaignMeta($campaign_id, 'campaign_sent_at', current_time('mysql', true));
        // In a real scenario, update recipients_count as well.
        return true;
    }

    /**
     * Send a test version of a campaign to a specific email address.
     *
     * @param int $campaign_id
     * @param string $test_email
     * @return bool|\WP_Error True on success, false or WP_Error on failure.
     */
    public function sendTestCampaign(int $campaign_id, string $test_email)
    {
        $campaign = $this->getCampaignById($campaign_id);
        if (!$campaign) {
            return new \WP_Error('campaign_not_found', __('Campaign not found.', 'charity-m3'));
        }

        $subject = get_post_meta($campaign_id, '_campaign_subject', true) ?: $campaign->post_title;
        $content = $campaign->post_content; // Raw content from editor

        // Apply content filters to process shortcodes, embeds, etc.
        $content = apply_filters('the_content', $content);

        // Prepare email headers
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        // Could add From name/email from settings here
        // $headers[] = 'From: My Awesome Charity <noreply@example.com>';

        // TODO: In a real implementation, parse content to inject tracking pixel and wrap links
        // For a test send, this might be optional or use a dummy subscriber ID (e.g., 0).
        // $trackingService = app(\App\Services\TrackingService::class);
        // $content = $this->injectTracking($content, $campaign_id, 0); // Example with subscriber_id 0 for tests

        // Add a notice that this is a test email
        $test_notice = '<p style="text-align:center; padding:10px; background-color:#f0f0f0; border:1px solid #ddd;"><em>' . __('This is a test email.', 'charity-m3') . '</em></p>';
        $content = $test_notice . $content;

        // Use wp_mail to send
        $sent = wp_mail($test_email, '[TEST] ' . $subject, $content, $headers);

        if (!$sent) {
            // wp_mail can fail silently; checking for errors might require other plugins or server log inspection.
            return new \WP_Error('wp_mail_failed', __('The email could not be sent. Your server may not be configured to send emails.', 'charity-m3'));
        }

        return true;
    }
}
