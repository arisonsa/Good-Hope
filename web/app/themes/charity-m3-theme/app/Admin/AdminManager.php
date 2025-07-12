<?php

namespace App\Admin;

use App\Services\SubscriberService;
use App\Services\CampaignService;
use App\Services\TrackingService;
use App\Services\DonationService;
use App\Admin\DonationsListTable;
use App\Admin\SubscribersListTable; // Make sure this is also included

class AdminManager
{
    private SubscriberService $subscriberService;
    private CampaignService $campaignService;
    private TrackingService $trackingService;
    private DonationService $donationService;

    public function __construct(
        SubscriberService $subscriberService,
        CampaignService $campaignService,
        TrackingService $trackingService,
        DonationService $donationService
    ) {
        $this->subscriberService = $subscriberService;
        $this->campaignService = $campaignService;
        $this->trackingService = $trackingService;
        $this->donationService = $donationService;

        add_action('admin_menu', [$this, 'registerAdminPages']);
        add_action('wp_dashboard_setup', [$this, 'addDashboardWidgets']);


        // Customize Newsletter Campaign CPT list table
        add_filter('manage_newsletter_campaign_posts_columns', [$this, 'setNewsletterCampaignColumns']);
        add_action('manage_newsletter_campaign_posts_custom_column', [$this, 'renderNewsletterCampaignColumns'], 10, 2);
        add_filter('manage_edit-newsletter_campaign_sortable_columns', [$this, 'setNewsletterCampaignSortableColumns']);
        // Add action for filtering (if we add a status filter dropdown)
        // add_action('restrict_manage_posts', [$this, 'addNewsletterCampaignStatusFilter']);
    }

    public function addDashboardWidgets()
    {
        wp_add_dashboard_widget(
            'charity_m3_newsletter_stats_widget',
            __('Newsletter Stats', 'charity-m3'),
            [$this, 'renderNewsletterStatsWidget']
        );
    }

    public function renderNewsletterStatsWidget()
    {
        // Fetch data using services
        $total_subscribers = 0;
        $subscriber_counts_by_status = $this->subscriberService->getSubscriberCountsByStatus();
        if (isset($subscriber_counts_by_status['subscribed'])) {
            $total_subscribers = $subscriber_counts_by_status['subscribed'];
        } else {
            // If only 'pending' or other statuses exist, sum them up or show 0 based on definition of "total"
            // For simplicity, let's assume 'subscribed' is the primary count for "active subscribers"
            $total_subscribers = $subscriber_counts_by_status['subscribed'] ?? 0;
        }


        echo '<p><strong>' . __('Total Active Subscribers:', 'charity-m3') . '</strong> ' . esc_html($total_subscribers) . '</p>';

        echo '<h4>' . __('Recent Campaign Performance (Last 5 Sent):', 'charity-m3') . '</h4>';

        $recent_campaigns_data = $this->campaignService->getCampaigns([
            'posts_per_page' => 5,
            'orderby' => 'meta_value', // Order by sent_at meta
            'meta_key' => '_campaign_sent_at',
            'order' => 'DESC',
            'meta_query' => [
                [
                    'key' => '_campaign_status',
                    'value' => 'sent',
                ],
                 [
                    'key' => '_campaign_sent_at', // Ensure it has been sent
                    'compare' => 'EXISTS',
                ]
            ]
        ]);
        $recent_campaigns = $recent_campaigns_data['items'];

        if (empty($recent_campaigns)) {
            echo '<p>' . __('No campaigns sent yet or no data available.', 'charity-m3') . '</p>';
        } else {
            echo '<ul style="list-style-type: disc; padding-left: 20px;">';
            foreach ($recent_campaigns as $campaign_post) {
                $stats = $this->trackingService->getCampaignAggregatedStats($campaign_post->ID);
                $recipients = (int) get_post_meta($campaign_post->ID, '_campaign_recipients_count', true);
                if ($recipients === 0) { // Fallback if recipients count wasn't stored, or to prevent division by zero
                     // Try to estimate recipients based on unique opens/clicks if count is missing, or show N/A
                    $recipients = max($stats['unique_opens'], $stats['unique_clicks']); // Basic estimation
                    if($recipients === 0) $recipients = 1; // Avoid division by zero if no opens/clicks either
                }


                $open_rate = ($recipients > 0 && $stats['unique_opens'] > 0) ? round(($stats['unique_opens'] / $recipients) * 100, 2) : 0;
                // Click rate can be based on unique opens or total recipients. Let's use unique opens.
                $click_rate_of_opens = ($stats['unique_opens'] > 0 && $stats['unique_clicks'] > 0) ? round(($stats['unique_clicks'] / $stats['unique_opens']) * 100, 2) : 0;

                echo '<li>';
                echo '<strong>' . esc_html($campaign_post->post_title) . '</strong>';
                echo ' (' . sprintf(esc_html__('Sent: %s', 'charity-m3'), esc_html(mysql2date(get_option('date_format'), get_post_meta($campaign_post->ID, '_campaign_sent_at', true)))) . ')';
                echo '<br>';
                echo sprintf(esc_html__('Unique Opens: %d (%.2f%%), Unique Clicks (of opens): %d (%.2f%%)', 'charity-m3'),
                    $stats['unique_opens'],
                    $open_rate,
                    $stats['unique_clicks'],
                    $click_rate_of_opens
                );
                // echo '<br>';
                // echo sprintf(esc_html__('Recipients (est.): %d', 'charity-m3'), $recipients);
                echo '</li>';
            }
            echo '</ul>';
        }

        echo '<p><a href="' . esc_url(admin_url('edit.php?post_type=newsletter_campaign')) . '">' . __('View All Campaigns', 'charity-m3') . '</a> | ';
        echo '<a href="' . esc_url(admin_url('admin.php?page=charity-m3-subscribers')) . '">' . __('Manage Subscribers', 'charity-m3') . '</a></p>';
    }


    public function registerAdminPages()
    {
        // The main "Newsletter" menu page will be handled by the CPT registration itself
        // if we set 'show_in_menu' => true for the CPT.
        // The CPT 'menu_name' was "Newsletter".
        // 'all_items' for CPT was "All Campaigns", which becomes a submenu.

        // Add Subscribers submenu page
        add_submenu_page(
            'edit.php?post_type=newsletter_campaign', // Parent slug (main CPT page)
            __('Subscribers', 'charity-m3'),          // Page title
            __('Subscribers', 'charity-m3'),          // Menu title
            'manage_options',                         // Capability (adjust as needed, e.g., a custom capability)
            'charity-m3-subscribers',                 // Menu slug
            [$this, 'renderSubscribersPage']          // Callback function to render the page
        );

        // Add Donations submenu page
        add_submenu_page(
            'edit.php?post_type=newsletter_campaign', // Parent slug (main CPT page)
            __('Donations', 'charity-m3'),            // Page title
            __('Donations', 'charity-m3'),            // Menu title
            'manage_options',                         // Capability
            'charity-m3-donations',                   // Menu slug
            [$this, 'renderDonationsPage']            // Callback
        );

        // Example: Add New Subscriber (not directly in menu, but linked from Subscribers page)
        // We can handle add/edit/delete actions within the renderSubscribersPage or separate handlers.
        // For a true separate page for adding/editing, you might register it without a menu item:
        // add_submenu_page(
        // null, // No parent menu item, so it's hidden unless linked to
        // __('Add New Subscriber', 'charity-m3'),
        // __('Add New Subscriber', 'charity-m3'),
        // 'manage_options',
        // 'charity-m3-subscriber-add',
        // [$this, 'renderSubscriberAddEditPage']
        // );

        // TODO: Add "Analytics" submenu page
        // add_submenu_page(
        // 'edit.php?post_type=newsletter_campaign',
        // __('Analytics', 'charity-m3'),
        // __('Analytics', 'charity-m3'),
        // 'manage_options',
        // 'charity-m3-analytics',
        // [$this, 'renderAnalyticsPage']
        // );

        // TODO: Add "Settings" submenu page
        // add_submenu_page(
        // 'edit.php?post_type=newsletter_campaign',
        // __('Settings', 'charity-m3'),
        // __('Settings', 'charity-m3'),
        // 'manage_options',
        // 'charity-m3-settings',
        // [$this, 'renderSettingsPage']
        // );
    }

    public function renderSubscribersPage()
    {
        // This is where we'll instantiate and display the WP_List_Table for subscribers
        // and handle add/edit/delete actions.
        // For now, a placeholder:
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Newsletter Subscribers', 'charity-m3') . '</h1>';

        // Action handling (add, edit, delete, bulk actions) will go here
        $action = isset($_REQUEST['action']) ? sanitize_key($_REQUEST['action']) : '';
        $action2 = isset($_REQUEST['action2']) ? sanitize_key($_REQUEST['action2']) : ''; // For bulk actions bottom button

        // Handle Add/Edit view
        if ($action === 'add_subscriber_view' || $action === 'edit_subscriber_view') {
            $subscriber_id = isset($_REQUEST['subscriber_id']) ? absint($_REQUEST['subscriber_id']) : null;
            if ($action === 'edit_subscriber_view' && !$subscriber_id) {
                // If edit action but no ID, redirect or show error
                wp_safe_redirect(admin_url('admin.php?page=charity-m3-subscribers&error=missing_id'));
                exit;
            }
            $this->renderSubscriberAddEditForm($subscriber_id); // Changed to non-static
            return; // Stop further processing for the list table
        }

        // Handle Save (Add/Update) action (POST request)
        if (isset($_POST['charity_m3_save_subscriber_nonce']) &&
            wp_verify_nonce(sanitize_key($_POST['charity_m3_save_subscriber_nonce']), 'charity_m3_save_subscriber')) {

            $subscriber_id = isset($_POST['subscriber_id']) ? absint($_POST['subscriber_id']) : null;
            $email = isset($_POST['subscriber_email']) ? sanitize_email(wp_unslash($_POST['subscriber_email'])) : '';
            $name = isset($_POST['subscriber_name']) ? sanitize_text_field(wp_unslash($_POST['subscriber_name'])) : '';
            $status = isset($_POST['subscriber_status']) ? sanitize_text_field(wp_unslash($_POST['subscriber_status'])) : 'pending';
            $source = isset($_POST['subscriber_source']) ? sanitize_text_field(wp_unslash($_POST['subscriber_source'])) : ($subscriber_id ? null : 'admin_added');


            if (empty($email) || !is_email($email)) {
                // Handle error: invalid email
                add_action('admin_notices', function() { echo "<div class='notice notice-error'><p>" . __('Invalid email address provided.', 'charity-m3') . "</p></div>"; });
                $this->renderSubscriberAddEditForm($subscriber_id, ['email' => $email, 'name' => $name, 'status' => $status, 'source' => $source]); // Re-render form with submitted values
                return;
            }

            $data = ['email' => $email, 'name' => $name, 'status' => $status];
            if ($source !== null) $data['source'] = $source; // Only set source if provided, otherwise keep existing for edits

            $result = false;
            if ($subscriber_id) { // Update existing
                $result = $this->subscriberService->updateSubscriber($subscriber_id, $data);
                $message = $result ? __('Subscriber updated successfully.', 'charity-m3') : __('Failed to update subscriber.', 'charity-m3');
            } else { // Add new
                $result = $this->subscriberService->addSubscriber($email, $data);
                $message = $result ? __('Subscriber added successfully.', 'charity-m3') : __('Failed to add subscriber (email might already exist or invalid data).', 'charity-m3');
            }

            if ($result) {
                wp_safe_redirect(admin_url('admin.php?page=charity-m3-subscribers&message=' . urlencode($message)));
                exit;
            } else {
                 add_action('admin_notices', function() use ($message) { echo "<div class='notice notice-error'><p>" . esc_html($message) . "</p></div>"; });
                 $this->renderSubscriberAddEditForm($subscriber_id, $data); // Re-render form with submitted values
                 return;
            }
        }

        // Display messages from redirects
        if (isset($_GET['message'])) {
            add_action('admin_notices', function() {
                echo "<div class='notice notice-success is-dismissible'><p>" . esc_html(urldecode(sanitize_text_field(wp_unslash($_GET['message'])))) . "</p></div>";
            });
        }
         if (isset($_GET['error'])) {
            add_action('admin_notices', function() {
                echo "<div class='notice notice-error is-dismissible'><p>" . esc_html(urldecode(sanitize_text_field(wp_unslash($_GET['error'])))) . "</p></div>";
            });
        }


        // Instantiate and display the WP_List_Table
        // The SubscribersListTable's constructor and prepare_items will handle single delete actions via its process_bulk_action.
        $subscribersListTable = new SubscribersListTable($this->subscriberService);
        $subscribersListTable->prepare_items(); // This also calls process_bulk_action

        echo '<h1 class="wp-heading-inline">' . esc_html__('Newsletter Subscribers', 'charity-m3') . '</h1>';
        echo ' <a href="?page=charity-m3-subscribers&action=add_subscriber_view" class="page-title-action">' . esc_html__('Add New', 'charity-m3') . '</a>';

        // Search box
        echo '<form method="get">';
        echo '<input type="hidden" name="page" value="charity-m3-subscribers" />';
        $subscribersListTable->search_box(__('Search Subscribers', 'charity-m3'), 'subscriber-search-input');
        echo '</form>';

        echo '<form method="post">'; // For bulk actions
        wp_nonce_field('charity_m3_subscriber_bulk_action', '_wpnonce_subscriber_bulk');
        $subscribersListTable->display();
        echo '</form>';

        echo '</div>';
    }

    // Form for adding or editing a subscriber
    public function renderSubscriberAddEditForm($subscriber_id = null, $current_data = [])
    {
        $is_editing = (bool) $subscriber_id;
        $subscriber = null;
        $page_title = $is_editing ? __('Edit Subscriber', 'charity-m3') : __('Add New Subscriber', 'charity-m3');

        if ($is_editing && empty($current_data)) {
            $subscriber = $this->subscriberService->getSubscriberById($subscriber_id);
            if (!$subscriber) {
                echo '<div class="wrap"><div class="notice notice-error"><p>' . __('Subscriber not found.', 'charity-m3') . '</p></div></div>';
                return;
            }
            $current_data = (array) $subscriber;
        } elseif (empty($current_data)) {
             $current_data = ['email' => '', 'name' => '', 'status' => 'pending', 'source' => 'admin_added'];
        }


        $email = $current_data['email'] ?? '';
        $name = $current_data['name'] ?? '';
        $status = $current_data['status'] ?? 'pending';
        $source = $current_data['source'] ?? ($is_editing ? '' : 'admin_added'); // Default source for new, don't override for edit unless specified

        $statuses = ['pending', 'subscribed', 'unsubscribed', 'bounced', 'cleaned']; // Define available statuses

        ?>
        <div class="wrap">
            <h1><?php echo esc_html($page_title); ?></h1>
            <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=charity-m3-subscribers')); ?>">
                <?php wp_nonce_field('charity_m3_save_subscriber', 'charity_m3_save_subscriber_nonce'); ?>
                <?php if ($is_editing): ?>
                    <input type="hidden" name="subscriber_id" value="<?php echo esc_attr($subscriber_id); ?>" />
                <?php endif; ?>
                <input type="hidden" name="action" value="<?php echo $is_editing ? 'update_subscriber' : 'add_subscriber'; ?>" />


                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="subscriber-email"><?php _e('Email Address', 'charity-m3'); ?></label></th>
                            <td><input name="subscriber_email" type="email" id="subscriber-email" value="<?php echo esc_attr($email); ?>" class="regular-text" required aria-required="true"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="subscriber-name"><?php _e('Name (Optional)', 'charity-m3'); ?></label></th>
                            <td><input name="subscriber_name" type="text" id="subscriber-name" value="<?php echo esc_attr($name); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="subscriber-status"><?php _e('Status', 'charity-m3'); ?></label></th>
                            <td>
                                <select name="subscriber_status" id="subscriber-status">
                                    <?php foreach ($statuses as $s): ?>
                                        <option value="<?php echo esc_attr($s); ?>" <?php selected($status, $s); ?>><?php echo esc_html(ucfirst($s)); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                         <?php if (!$is_editing): // Only show source field when adding, or make it editable if needed ?>
                        <tr>
                            <th scope="row"><label for="subscriber-source"><?php _e('Source', 'charity-m3'); ?></label></th>
                            <td><input name="subscriber_source" type="text" id="subscriber-source" value="<?php echo esc_attr($source); ?>" class="regular-text" readonly></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php submit_button($is_editing ? __('Update Subscriber', 'charity-m3') : __('Add Subscriber', 'charity-m3')); ?>
            </form>
        </div>
        <?php
    }


    public function renderAnalyticsPage()
    {
        echo '<div class="wrap"><h1>' . esc_html__('Newsletter Analytics', 'charity-m3') . '</h1><p>Coming soon...</p></div>';
    }

    public function renderDonationsPage()
    {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Donations', 'charity-m3') . '</h1>';

        $donationsListTable = new DonationsListTable($this->donationService);
        $donationsListTable->prepare_items();

        // Search box for donations
        echo '<form method="get">';
        echo '<input type="hidden" name="page" value="charity-m3-donations" />';
        $donationsListTable->search_box(__('Search Donations', 'charity-m3'), 'donation-search-input');
        echo '</form>';

        // Display the table
        $donationsListTable->display();

        echo '</div>';
    }

    public function renderSettingsPage()
    {
        echo '<div class="wrap"><h1>' . esc_html__('Newsletter Settings', 'charity-m3') . '</h1><p>Coming soon...</p></div>';
    }

    /**
     * Set custom columns for the Newsletter Campaign CPT list table.
     */
    public function setNewsletterCampaignColumns($columns)
    {
        $new_columns = [];
        foreach ($columns as $key => $title) {
            $new_columns[$key] = $title;
            if ($key === 'title') { // Add our custom columns after 'title'
                $new_columns['campaign_subject'] = __('Subject', 'charity-m3');
                $new_columns['campaign_status'] = __('Status', 'charity-m3');
                $new_columns['campaign_scheduled_at'] = __('Scheduled At', 'charity-m3');
                $new_columns['campaign_sent_at'] = __('Sent At', 'charity-m3');
            }
        }
        // If 'date' column exists, move it to the end or after our custom columns
        if (isset($new_columns['date'])) {
            $date_col = $new_columns['date'];
            unset($new_columns['date']);
            $new_columns['date'] = $date_col;
        }
        return $new_columns;
    }

    /**
     * Render content for custom columns in the Newsletter Campaign CPT list table.
     */
    public function renderNewsletterCampaignColumns($column, $post_id)
    {
        switch ($column) {
            case 'campaign_subject':
                echo esc_html(get_post_meta($post_id, '_campaign_subject', true));
                break;
            case 'campaign_status':
                echo esc_html(ucfirst(get_post_meta($post_id, '_campaign_status', true) ?: 'Draft'));
                break;
            case 'campaign_scheduled_at':
                $scheduled_at = get_post_meta($post_id, '_campaign_scheduled_at', true);
                echo $scheduled_at ? esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $scheduled_at)) : 'N/A';
                break;
            case 'campaign_sent_at':
                $sent_at = get_post_meta($post_id, '_campaign_sent_at', true);
                echo $sent_at ? esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $sent_at)) : 'N/A';
                break;
        }
    }

    /**
     * Make custom columns sortable for Newsletter Campaign CPT.
     */
    public function setNewsletterCampaignSortableColumns($columns)
    {
        $columns['campaign_subject'] = '_campaign_subject';
        $columns['campaign_status'] = '_campaign_status';
        $columns['campaign_scheduled_at'] = '_campaign_scheduled_at';
        $columns['campaign_sent_at'] = '_campaign_sent_at';
        return $columns;
    }

    // TODO: Implement addNewsletterCampaignStatusFilter if needed for a dropdown filter.
    // public function addNewsletterCampaignStatusFilter($post_type) {
    //     if ($post_type === 'newsletter_campaign') {
    //         // Add dropdown here
    //     }
    // }
}
