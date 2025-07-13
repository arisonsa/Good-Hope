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
     * Schedule a campaign to be sent at a specific time.
     *
     * @param int $campaign_id
     * @param int $timestamp Unix timestamp for when to send.
     * @return bool|\WP_Error
     */
    public function scheduleCampaign(int $campaign_id, int $timestamp)
    {
        // Clear any previously scheduled event for this campaign to avoid duplicates
        wp_clear_scheduled_hook('charity_m3_initiate_campaign_cron', [$campaign_id]);

        $this->updateCampaignMeta($campaign_id, 'campaign_status', 'scheduled');
        $this->updateCampaignMeta($campaign_id, 'campaign_scheduled_at', gmdate('Y-m-d H:i:s', $timestamp));

        $result = wp_schedule_single_event($timestamp, 'charity_m3_initiate_campaign_cron', [$campaign_id]);

        if ($result === false) {
            return new \WP_Error('schedule_failed', __('Could not schedule the campaign. Please try again.', 'charity-m3'));
        }

        return true;
    }

    /**
     * Initiates the campaign sending process.
     *
     * @param int $campaign_id
     * @return bool|\WP_Error
     */
    public function initiateCampaignSending(int $campaign_id)
    {
        // Prevent re-sending a campaign that is already sending or sent
        $status = get_post_meta($campaign_id, '_campaign_status', true);
        if (in_array($status, ['sending', 'sent'])) {
            return new \WP_Error('already_sent', __('This campaign is already sending or has been sent.', 'charity-m3'));
        }

        /** @var \App\Services\SubscriberService $subscriberService */
        $subscriberService = app(\App\Services\SubscriberService::class);
        $subscribers = $subscriberService->getSubscribers(['status' => 'subscribed', 'per_page' => -1]); // Get all subscribed users

        if (empty($subscribers['items'])) {
            $this->updateCampaignMeta($campaign_id, 'campaign_status', 'sent'); // No one to send to, mark as sent
            $this->updateCampaignMeta($campaign_id, 'campaign_recipients_count', 0);
            return new \WP_Error('no_subscribers', __('There are no subscribed users to send this campaign to.', 'charity-m3'));
        }

        $recipient_ids = wp_list_pluck($subscribers['items'], 'id');
        $this->updateCampaignMeta($campaign_id, 'campaign_recipients_count', count($recipient_ids));
        $this->updateCampaignMeta($campaign_id, 'campaign_status', 'sending');

        // Store recipient IDs in a transient
        set_transient('charity_m3_campaign_' . $campaign_id . '_recipients', $recipient_ids, DAY_IN_SECONDS);

        // Schedule the batch processor if not already scheduled
        if (!wp_next_scheduled('charity_m3_process_campaign_batch_cron', [$campaign_id])) {
            // Schedule to run immediately and then every 5 minutes
            wp_schedule_event(time(), 'five_minutes', 'charity_m3_process_campaign_batch_cron', [$campaign_id]);
        }

        return true;
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

    /**
     * Process a single batch of emails for a campaign.
     * Intended to be called by WP-Cron.
     *
     * @param int $campaign_id
     */
    public function processCampaignBatch(int $campaign_id)
    {
        $transient_key = 'charity_m3_campaign_' . $campaign_id . '_recipients';
        $recipient_ids = get_transient($transient_key);

        if (empty($recipient_ids)) {
            // All done, clean up
            wp_clear_scheduled_hook('charity_m3_process_campaign_batch_cron', [$campaign_id]);
            $this->updateCampaignMeta($campaign_id, 'campaign_status', 'sent');
            $this->updateCampaignMeta($campaign_id, 'campaign_sent_at', current_time('mysql', true));
            return;
        }

        $batch_size = apply_filters('charity_m3_campaign_batch_size', 50);
        $batch_ids = array_slice($recipient_ids, 0, $batch_size);

        $campaign = $this->getCampaignById($campaign_id);
        if (!$campaign) {
            wp_clear_scheduled_hook('charity_m3_process_campaign_batch_cron', [$campaign_id]);
            return; // Campaign was deleted, stop processing.
        }
        $subject = get_post_meta($campaign_id, '_campaign_subject', true) ?: $campaign->post_title;
        $content = apply_filters('the_content', $campaign->post_content);
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        /** @var \App\Services\SubscriberService $subscriberService */
        $subscriberService = app(\App\Services\SubscriberService::class);

        foreach ($batch_ids as $subscriber_id) {
            $subscriber = $subscriberService->getSubscriberById($subscriber_id);
            if (!$subscriber || $subscriber->status !== 'subscribed') {
                continue; // Skip if subscriber not found or no longer subscribed
            }

            $personalized_content = $this->injectTracking($content, $campaign_id, $subscriber_id);

            // TODO: Add personalization (e.g., replace {{name}} with $subscriber->name)

            wp_mail($subscriber->email, $subject, $personalized_content, $headers);
        }

        // Update the transient with the remaining IDs
        $remaining_ids = array_slice($recipient_ids, $batch_size);
        set_transient($transient_key, $remaining_ids, DAY_IN_SECONDS);

        // If that was the last batch, clean up immediately instead of waiting for next cron run
        if (empty($remaining_ids)) {
            wp_clear_scheduled_hook('charity_m3_process_campaign_batch_cron', [$campaign_id]);
            $this->updateCampaignMeta($campaign_id, 'campaign_status', 'sent');
            $this->updateCampaignMeta($campaign_id, 'campaign_sent_at', current_time('mysql', true));
        }
    }

    /**
     * Injects tracking pixel and wraps links for a given HTML content.
     *
     * @param string $html
     * @param int $campaign_id
     * @param int $subscriber_id
     * @return string
     */
    private function injectTracking(string $html, int $campaign_id, int $subscriber_id): string
    {
        // Inject Open Tracking Pixel
        $pixel_url = rest_url("charitym3/v1/track/open/{$campaign_id}/{$subscriber_id}/pixel.png");
        $tracking_pixel = '<img src="' . esc_url($pixel_url) . '" width="1" height="1" alt="" style="display:none;"/>';
        // Append pixel before closing body tag
        $html = str_ireplace('</body>', $tracking_pixel . '</body>', $html, $count);
        if ($count === 0) { // If no body tag, append to end
            $html .= $tracking_pixel;
        }

        // Wrap Links for Click Tracking
        if (class_exists('DOMDocument')) {
            $dom = new \DOMDocument();
            // Suppress errors from malformed HTML
            libxml_use_internal_errors(true);
            $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();

            $links = $dom->getElementsByTagName('a');
            foreach ($links as $link) {
                $original_href = $link->getAttribute('href');
                if ($original_href && !str_starts_with($original_href, '#') && filter_var($original_href, FILTER_VALIDATE_URL)) {
                    $tracking_url_base = rest_url("charitym3/v1/track/click/{$campaign_id}/{$subscriber_id}");
                    $tracking_url = add_query_arg('url', rawurlencode($original_href), $tracking_url_base);
                    $link->setAttribute('href', esc_url($tracking_url));
                }
            }
            $html = $dom->saveHTML();
        }

        return $html;
    }
}
