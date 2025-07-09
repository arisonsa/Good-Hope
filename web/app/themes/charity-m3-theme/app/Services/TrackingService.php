<?php

namespace App\Services;

use wpdb;

class TrackingService
{
    private wpdb $db;
    private string $table_name;

    public function __construct(wpdb $db)
    {
        $this->db = $db;
        $this->table_name = $this->db->prefix . 'charity_m3_newsletter_tracking';
    }

    /**
     * Record a tracking event.
     *
     * @param int $campaign_id
     * @param int $subscriber_id
     * @param string $action_type ('open', 'click')
     * @param string|null $target_url URL if action is 'click'.
     * @return bool True on success, false on failure.
     */
    public function recordEvent(int $campaign_id, int $subscriber_id, string $action_type, ?string $target_url = null): bool
    {
        if (empty($campaign_id) || empty($subscriber_id) || !in_array($action_type, ['open', 'click'])) {
            return false;
        }

        // Basic flood prevention: check if this exact event was recorded recently (e.g., last 5 mins)
        // This is a simple example; more robust flood/bot detection might be needed.
        $five_minutes_ago = gmdate('Y-m-d H:i:s', time() - (5 * 60));
        $existing_event = $this->db->get_var($this->db->prepare(
            "SELECT id FROM {$this->table_name}
             WHERE campaign_id = %d AND subscriber_id = %d AND action_type = %s AND target_url <=> %s AND tracked_at > %s
             LIMIT 1",
            $campaign_id,
            $subscriber_id,
            $action_type,
            $target_url, // <=> is a NULL-safe equality operator
            $five_minutes_ago
        ));

        if ($existing_event) {
            // Event already recorded recently, don't record again to prevent duplicates from quick reloads/clicks.
            return true; // Or false if you want to indicate "not newly recorded"
        }

        $data = [
            'campaign_id' => $campaign_id,
            'subscriber_id' => $subscriber_id,
            'action_type' => sanitize_text_field($action_type),
            'target_url' => $target_url ? esc_url_raw($target_url) : null,
            'ip_address' => $this->getIpAddress(), // Consider anonymization
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : null,
            'tracked_at' => current_time('mysql', true),
        ];

        $result = $this->db->insert($this->table_name, $data);
        return $result !== false;
    }

    /**
     * Get IP address of the current user.
     *
     * @return string|null
     */
    private function getIpAddress(): ?string
    {
        // Handle various headers, especially if behind a proxy
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'] as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : null;
    }

    /**
     * Get tracking data for a campaign.
     *
     * @param int $campaign_id
     * @param array $args {
     *     @type string $action_type Filter by 'open' or 'click'.
     *     @type int $per_page
     *     @type int $page
     * }
     * @return array { 'items': array, 'total_items': int }
     */
    public function getCampaignTrackingData(int $campaign_id, array $args = []): array
    {
        $defaults = [
            'action_type' => '',
            'per_page' => 100, // Usually for raw data export or detailed views
            'page' => 1,
        ];
        $args = wp_parse_args($args, $defaults);

        $where_clauses = ["campaign_id = %d"];
        $params = [$campaign_id];

        if (!empty($args['action_type'])) {
            $where_clauses[] = "action_type = %s";
            $params[] = sanitize_text_field($args['action_type']);
        }

        $where_sql = implode(' AND ', $where_clauses);

        $total_items_sql = "SELECT COUNT(id) FROM $this->table_name WHERE $where_sql";
        $total_items = $this->db->get_var($this->db->prepare($total_items_sql, $params));

        $offset = ($args['page'] - 1) * $args['per_page'];
        $items_sql = "SELECT * FROM $this->table_name WHERE $where_sql ORDER BY tracked_at DESC LIMIT %d OFFSET %d";
        $query_params = array_merge($params, [$args['per_page'], $offset]);

        $items = $this->db->get_results($this->db->prepare($items_sql, $query_params));

        return [
            'items' => $items,
            'total_items' => (int) $total_items,
        ];
    }

    /**
     * Get aggregated tracking stats for a campaign.
     *
     * @param int $campaign_id
     * @return array { 'total_opens': int, 'unique_opens': int, 'total_clicks': int, 'unique_clicks': int }
     */
    public function getCampaignAggregatedStats(int $campaign_id): array
    {
        $stats = [
            'total_opens' => 0,
            'unique_opens' => 0,
            'total_clicks' => 0,
            'unique_clicks' => 0,
        ];

        // Total Opens
        $stats['total_opens'] = (int) $this->db->get_var($this->db->prepare(
            "SELECT COUNT(id) FROM {$this->table_name} WHERE campaign_id = %d AND action_type = 'open'",
            $campaign_id
        ));
        // Unique Opens (by subscriber_id)
        $stats['unique_opens'] = (int) $this->db->get_var($this->db->prepare(
            "SELECT COUNT(DISTINCT subscriber_id) FROM {$this->table_name} WHERE campaign_id = %d AND action_type = 'open'",
            $campaign_id
        ));

        // Total Clicks
        $stats['total_clicks'] = (int) $this->db->get_var($this->db->prepare(
            "SELECT COUNT(id) FROM {$this->table_name} WHERE campaign_id = %d AND action_type = 'click'",
            $campaign_id
        ));
        // Unique Clicks (by subscriber_id, could also be by subscriber_id + target_url for more granularity)
        $stats['unique_clicks'] = (int) $this->db->get_var($this->db->prepare(
            "SELECT COUNT(DISTINCT subscriber_id) FROM {$this->table_name} WHERE campaign_id = %d AND action_type = 'click'",
            $campaign_id
        ));

        return $stats;
    }
}
