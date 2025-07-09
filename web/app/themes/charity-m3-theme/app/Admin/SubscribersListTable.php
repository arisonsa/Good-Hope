<?php

namespace App\Admin;

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

use App\Services\SubscriberService;

class SubscribersListTable extends \WP_List_Table
{
    private SubscriberService $subscriberService;

    public function __construct(SubscriberService $subscriberService)
    {
        $this->subscriberService = $subscriberService;
        parent::__construct([
            'singular' => __('Subscriber', 'charity-m3'),
            'plural'   => __('Subscribers', 'charity-m3'),
            'ajax'     => false // Set to true if_you_want_to_enable_ajax_sorting_pagination
        ]);
    }

    /**
     * Prepare the items for the table to process.
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = [$columns, $hidden, $sortable];

        // Process bulk actions
        $this->process_bulk_action();

        $per_page = $this->get_items_per_page('subscribers_per_page', 20);
        $current_page = $this->get_pagenum();

        $search_term = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '';
        $status_filter = isset($_REQUEST['status']) ? sanitize_text_field(wp_unslash($_REQUEST['status'])) : '';

        $orderby = isset($_REQUEST['orderby']) ? sanitize_key($_REQUEST['orderby']) : 'created_at';
        $order = isset($_REQUEST['order']) ? strtoupper(sanitize_key($_REQUEST['order'])) : 'DESC';
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }


        $data = $this->subscriberService->getSubscribers([
            'per_page' => $per_page,
            'page'     => $current_page,
            'search'   => $search_term,
            'status'   => $status_filter,
            'orderby'  => $orderby,
            'order'    => $order,
        ]);

        $this->items = $data['items'];
        $total_items = $data['total_items'];

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ]);
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table.
     */
    public function get_columns()
    {
        return [
            'cb'          => '<input type="checkbox" />', // For bulk actions
            'email'       => __('Email', 'charity-m3'),
            'name'        => __('Name', 'charity-m3'),
            'status'      => __('Status', 'charity-m3'),
            'source'      => __('Source', 'charity-m3'),
            'subscribed_at' => __('Subscribed At', 'charity-m3'),
            'created_at'  => __('Added At', 'charity-m3'),
        ];
    }

    /**
     * Define which columns are hidden
     */
    public function get_hidden_columns()
    {
        return [];
    }

    /**
     * Define the sortable columns
     */
    public function get_sortable_columns()
    {
        return [
            'email'       => ['email', false],
            'name'        => ['name', false],
            'status'      => ['status', false],
            'subscribed_at' => ['subscribed_at', false],
            'created_at'  => ['created_at', true], // True means it's already sorted by this column
        ];
    }

    /**
     * Define what data to show on each column of the table
     */
    protected function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'email':
            case 'name':
            case 'status':
            case 'source':
                return esc_html($item->$column_name);
            case 'subscribed_at':
            case 'created_at':
                return $item->$column_name ? mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $item->$column_name) : 'N/A';
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Handle the checkbox column
     */
    protected function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="subscriber_id[]" value="%s" />', $item->id
        );
    }

    /**
     * Handle the 'email' column with actions (Edit, Delete)
     */
    protected function column_email($item)
    {
        $page_slug = 'charity-m3-subscribers'; // From add_submenu_page

        // Nonce for delete action
        $delete_nonce = wp_create_nonce('charity_m3_delete_subscriber_' . $item->id);

        $actions = [
            'edit' => sprintf(
                '<a href="?page=%s&action=%s&subscriber_id=%s">%s</a>',
                esc_attr($page_slug),
                'edit_subscriber_view', // We'll use this action to show the edit form
                absint($item->id),
                __('Edit', 'charity-m3')
            ),
            'delete' => sprintf(
                '<a href="?page=%s&action=%s&subscriber_id=%s&_wpnonce=%s" onclick="return confirm(\'%s\')">%s</a>',
                esc_attr($page_slug),
                'delete_subscriber',
                absint($item->id),
                $delete_nonce,
                esc_js(__('Are you sure you want to delete this subscriber?', 'charity-m3')),
                __('Delete', 'charity-m3')
            ),
        ];
        return sprintf('%1$s %2$s', esc_html($item->email), $this->row_actions($actions));
    }

    /**
     * Define bulk actions
     */
    public function get_bulk_actions()
    {
        return [
            'bulk_delete'        => __('Delete', 'charity-m3'),
            'bulk_set_subscribed' => __('Set as Subscribed', 'charity-m3'),
            'bulk_set_unsubscribed' => __('Set as Unsubscribed', 'charity-m3'),
            'bulk_set_pending'    => __('Set as Pending', 'charity-m3'),
        ];
    }

    /**
     * Process bulk actions.
     */
    public function process_bulk_action()
    {
        $action = $this->current_action();

        if (!isset($_POST['_wpnonce_subscriber_bulk']) || !wp_verify_nonce(sanitize_key($_POST['_wpnonce_subscriber_bulk']), 'charity_m3_subscriber_bulk_action')) {
            // Potential nonce issue, but current_action() might be from GET for single delete
            if (strpos($action, 'bulk_') !== 0 && $action !== 'delete_subscriber') { // only fail for actual bulk POSTs
                 // return; // Or display an error
            }
        }

        $subscriber_ids = isset($_REQUEST['subscriber_id'])
            ? (is_array($_REQUEST['subscriber_id']) ? array_map('absint', $_REQUEST['subscriber_id']) : [absint($_REQUEST['subscriber_id'])])
            : [];

        if (empty($subscriber_ids)) return;

        $success_count = 0;
        $error_count = 0;

        switch ($action) {
            case 'bulk_delete':
            case 'delete_subscriber': // Single delete from row action
                 // Verify nonce for single delete
                if ($action === 'delete_subscriber' && (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_key($_GET['_wpnonce']), 'charity_m3_delete_subscriber_' . $subscriber_ids[0]))) {
                    wp_die(__('Security check failed for deleting subscriber.', 'charity-m3'));
                }
                foreach ($subscriber_ids as $id) {
                    if ($this->subscriberService->deleteSubscriber($id)) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                }
                // Add admin notice: $success_count subscribers deleted. $error_count failed.
                if ($success_count > 0) add_action('admin_notices', function() use ($success_count) {
                    echo "<div class='notice notice-success is-dismissible'><p>" . sprintf(_n('%s subscriber deleted.', '%s subscribers deleted.', $success_count, 'charity-m3'), $success_count) . "</p></div>";
                });
                if ($error_count > 0) add_action('admin_notices', function() use ($error_count) {
                     echo "<div class='notice notice-error is-dismissible'><p>" . sprintf(_n('Failed to delete %s subscriber.', 'Failed to delete %s subscribers.', $error_count, 'charity-m3'), $error_count) . "</p></div>";
                });
                break;

            case 'bulk_set_subscribed':
            case 'bulk_set_unsubscribed':
            case 'bulk_set_pending':
                $new_status = str_replace('bulk_set_', '', $action);
                foreach ($subscriber_ids as $id) {
                    if ($this->subscriberService->updateSubscriber($id, ['status' => $new_status])) {
                         $success_count++;
                    } else {
                        $error_count++;
                    }
                }
                 if ($success_count > 0) add_action('admin_notices', function() use ($success_count, $new_status) {
                    echo "<div class='notice notice-success is-dismissible'><p>" . sprintf(_n('Status for %s subscriber updated to %s.', 'Status for %s subscribers updated to %s.', $success_count, 'charity-m3'), $success_count, esc_html($new_status)) . "</p></div>";
                });
                if ($error_count > 0) add_action('admin_notices', function() use ($error_count, $new_status) {
                     echo "<div class='notice notice-error is-dismissible'><p>" . sprintf(_n('Failed to update status for %s subscriber to %s.', 'Failed to update status for %s subscribers to %s.', $error_count, 'charity-m3'), $error_count, esc_html($new_status)) . "</p></div>";
                });
                break;
            default:
                // Do nothing for other actions or if no action is set.
                return;
        }

        // Redirect after processing to prevent form resubmission issues and clear URL params
        // if ($success_count > 0 || $error_count > 0) {
        //     wp_redirect(remove_query_arg(['action', 'action2', 'subscriber_id', '_wpnonce'], wp_get_referer()));
        //     exit;
        // }
    }

    /**
     * Display extra table navigation (e.g., filters)
     */
    protected function extra_tablenav($which)
    {
        if ($which == "top") {
            // Status filter dropdown
            $current_status = isset($_REQUEST['status']) ? sanitize_text_field(wp_unslash($_REQUEST['status'])) : '';
            $statuses = ['pending', 'subscribed', 'unsubscribed', 'bounced', 'cleaned']; // Get these dynamically if possible

            echo '<div class="alignleft actions">';
            echo '<label for="filter-by-status" class="screen-reader-text">' . __('Filter by status', 'charity-m3') . '</label>';
            echo '<select name="status" id="filter-by-status">';
            echo '<option value="">' . __('All Statuses', 'charity-m3') . '</option>';
            foreach ($statuses as $status) {
                printf(
                    '<option value="%s"%s>%s</option>',
                    esc_attr($status),
                    selected($current_status, $status, false),
                    esc_html(ucfirst($status))
                );
            }
            echo '</select>';
            submit_button(__('Filter'), 'secondary', 'filter_action', false, ['id' => 'post-query-submit']);
            echo '</div>';
        }
    }

    /**
     * Message to be displayed when there are no items
     */
    public function no_items()
    {
        _e('No subscribers found.', 'charity-m3');
    }
}
