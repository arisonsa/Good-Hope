<?php

namespace App\Services;

use wpdb;

class SubscriberService
{
    private wpdb $db;
    private string $table_name;

    public function __construct(wpdb $db)
    {
        $this->db = $db;
        $this->table_name = $this->db->prefix . 'charity_m3_subscribers';
    }

    /**
     * Add a new subscriber.
     *
     * @param string $email
     * @param array $data Additional data (name, status, source).
     * @return int|false The ID of the newly added subscriber or false on failure.
     */
    public function addSubscriber(string $email, array $data = [])
    {
        $email = sanitize_email($email);
        if (!is_email($email)) {
            return false; // Invalid email
        }

        // Check if email already exists
        if ($this->getSubscriberByEmail($email)) {
            // Optionally update existing subscriber or return an error/specific code
            // For now, let's prevent duplicates silently or update status if pending
            $existing = $this->getSubscriberByEmail($email);
            if ($existing && $existing->status === 'pending') {
                return $this->updateSubscriber($existing->id, ['status' => $data['status'] ?? 'pending']);
            }
            return $existing->id ?? false; // Or handle as an error: "Email already subscribed"
        }

        $defaults = [
            'name' => null,
            'status' => 'pending', // e.g., 'pending' for double opt-in, 'subscribed' for direct
            'source' => null,
            'subscribed_at' => null, // Set this when status becomes 'subscribed'
            'created_at' => current_time('mysql', true),
        ];
        $insert_data = wp_parse_args($data, $defaults);
        $insert_data['email'] = $email;

        if ($insert_data['status'] === 'subscribed' && empty($insert_data['subscribed_at'])) {
            $insert_data['subscribed_at'] = current_time('mysql', true);
        }

        $result = $this->db->insert($this->table_name, $insert_data);

        if ($result) {
            return $this->db->insert_id;
        }
        return false;
    }

    /**
     * Get a subscriber by ID.
     *
     * @param int $id
     * @return object|null
     */
    public function getSubscriberById(int $id)
    {
        return $this->db->get_row($this->db->prepare("SELECT * FROM $this->table_name WHERE id = %d", $id));
    }

    /**
     * Get a subscriber by email.
     *
     * @param string $email
     * @return object|null
     */
    public function getSubscriberByEmail(string $email)
    {
        $email = sanitize_email($email);
        return $this->db->get_row($this->db->prepare("SELECT * FROM $this->table_name WHERE email = %s", $email));
    }

    /**
     * Update a subscriber.
     *
     * @param int $id
     * @param array $data Data to update.
     * @return bool True on success, false on failure.
     */
    public function updateSubscriber(int $id, array $data)
    {
        // Sanitize and prepare data
        $update_data = [];
        if (isset($data['email'])) {
            $email = sanitize_email($data['email']);
            if (is_email($email)) {
                // Check if new email already exists for another subscriber
                $existing = $this->getSubscriberByEmail($email);
                if ($existing && $existing->id != $id) {
                    return false; // Email conflict
                }
                $update_data['email'] = $email;
            }
        }
        if (isset($data['name'])) {
            $update_data['name'] = sanitize_text_field($data['name']);
        }
        if (isset($data['status'])) {
            $update_data['status'] = sanitize_text_field($data['status']);
            if ($update_data['status'] === 'subscribed' && !isset($data['subscribed_at'])) {
                 // If becoming subscribed and subscribed_at not explicitly set, set it now.
                 // But only if it wasn't subscribed before.
                $current_subscriber = $this->getSubscriberById($id);
                if ($current_subscriber && $current_subscriber->status !== 'subscribed') {
                    $update_data['subscribed_at'] = current_time('mysql', true);
                }
            } elseif ($update_data['status'] === 'unsubscribed' && !isset($data['unsubscribed_at'])) {
                $update_data['unsubscribed_at'] = current_time('mysql', true);
            }
        }
        if (isset($data['source'])) {
            $update_data['source'] = sanitize_text_field($data['source']);
        }
        // subscribed_at and unsubscribed_at can be set explicitly if needed
        if (isset($data['subscribed_at'])) {
            $update_data['subscribed_at'] = $data['subscribed_at'] ? current_time('mysql', true) : null;
        }
         if (isset($data['unsubscribed_at'])) {
            $update_data['unsubscribed_at'] = $data['unsubscribed_at'] ? current_time('mysql', true) : null;
        }


        if (empty($update_data)) {
            return false; // No valid data to update
        }

        // last_changed_at is updated automatically by MySQL
        $result = $this->db->update($this->table_name, $update_data, ['id' => $id]);
        return $result !== false;
    }

    /**
     * Delete a subscriber.
     *
     * @param int $id
     * @return bool True on success, false on failure.
     */
    public function deleteSubscriber(int $id): bool
    {
        // For GDPR, consider if this should be anonymization or actual deletion.
        // Actual deletion:
        $result = $this->db->delete($this->table_name, ['id' => $id]);
        return $result !== false;
        // Soft delete (by changing status to 'deleted' or 'anonymized'):
        // return $this->updateSubscriber($id, ['status' => 'deleted', 'email' => 'deleted-' . $id . '@example.com']);
    }

    /**
     * Get subscribers with pagination, filtering, and sorting.
     *
     * @param array $args {
     *     Optional. Arguments to retrieve subscribers.
     *
     *     @type int    $per_page Number of items per page. Default 20.
     *     @type int    $page     Current page number. Default 1.
     *     @type string $search   Search term for email or name.
     *     @type string $status   Filter by status.
     *     @type string $orderby  Column to order by. Default 'created_at'.
     *     @type string $order    Order direction (ASC or DESC). Default 'DESC'.
     * }
     * @return array An array containing 'items' (list of subscribers) and 'total_items'.
     */
    public function getSubscribers(array $args = []): array
    {
        $defaults = [
            'per_page' => 20,
            'page' => 1,
            'search' => '',
            'status' => '',
            'orderby' => 'created_at',
            'order' => 'DESC',
        ];
        $args = wp_parse_args($args, $defaults);

        $where_clauses = ['1=1'];
        $params = [];

        if (!empty($args['search'])) {
            $search_term = '%' . $this->db->esc_like(sanitize_text_field($args['search'])) . '%';
            $where_clauses[] = "(email LIKE %s OR name LIKE %s)";
            $params[] = $search_term;
            $params[] = $search_term;
        }

        if (!empty($args['status'])) {
            $where_clauses[] = "status = %s";
            $params[] = sanitize_text_field($args['status']);
        }

        $where_sql = implode(' AND ', $where_clauses);

        // Get total items for pagination
        $total_items_sql = "SELECT COUNT(id) FROM $this->table_name WHERE $where_sql";
        $total_items = $this->db->get_var(empty($params) ? $total_items_sql : $this->db->prepare($total_items_sql, $params));

        // Get paginated items
        $offset = ($args['page'] - 1) * $args['per_page'];
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']); // Basic sanitization

        // Ensure orderby column is valid
        $allowed_orderby_cols = ['id', 'email', 'name', 'status', 'created_at', 'subscribed_at', 'last_changed_at'];
        if (!in_array(strtolower($args['orderby']), $allowed_orderby_cols)) {
            $orderby = 'created_at DESC'; // Default if invalid
        }


        $items_sql = "SELECT * FROM $this->table_name WHERE $where_sql ORDER BY $orderby LIMIT %d OFFSET %d";
        $query_params = array_merge($params, [$args['per_page'], $offset]);

        $items = $this->db->get_results(empty($params) ? $items_sql : $this->db->prepare($items_sql, $query_params));

        return [
            'items' => $items,
            'total_items' => (int) $total_items,
        ];
    }

    /**
     * Get count of subscribers by status.
     * @return array Associative array of status => count.
     */
    public function getSubscriberCountsByStatus(): array
    {
        $sql = "SELECT status, COUNT(id) as count FROM {$this->table_name} GROUP BY status";
        $results = $this->db->get_results($sql);
        $counts = [];
        foreach ($results as $row) {
            $counts[$row->status] = (int) $row->count;
        }
        return $counts;
    }
}
