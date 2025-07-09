<?php

namespace App\Blocks;

use Roots\Acorn\Application; // For accessing Acorn services if needed

class NewsletterSignupBlock
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
        add_action('init', [$this, 'registerBlock']);
    }

    public function registerBlock()
    {
        // Ensure our build assets are available.
        // The script handle must match what's used in webpack.mix.js for the block's JS.
        // We'll create this JS file in the next steps.
        // The `\Roots\asset()` helper will point to the compiled asset in the theme's `public` directory.

        // First, register the script. The actual JS file will be created later.
        // This assumes you'll have a dedicated JS entry point for your blocks,
        // or include block JS in your main.js and provide the correct handle.
        // For this example, let's assume 'charity-m3-theme-blocks' will be a new entry point.
        wp_register_script(
            'charity-m3-theme-newsletter-block-editor',
            \Roots\asset('scripts/blocks/newsletter-signup.editor.js')->uri(), // Will be created
            ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'],
            null, // Version will be handled by Mix
            true
        );

        // Register the block type.
        register_block_type_from_metadata(CHARITY_M3_THEME_PATH . 'app/Blocks/NewsletterSignup', [
            'render_callback' => [$this, 'render'],
            'editor_script' => 'charity-m3-theme-newsletter-block-editor', // For editor-side JS
            // 'style' => 'charity-m3-theme-main', // If frontend styles are in main.css
            // 'editor_style' => 'charity-m3-theme-main', // If editor styles are in main.css
        ]);
    }

    public function render($attributes, $content)
    {
        $title = $attributes['title'] ?? __('Stay Updated', 'charity-m3');
        $description = $attributes['description'] ?? __('Subscribe to our newsletter for the latest news and updates.', 'charity-m3');
        $emailPlaceholder = $attributes['emailPlaceholder'] ?? __('Enter your email', 'charity-m3');
        $buttonText = $attributes['buttonText'] ?? __('Subscribe', 'charity-m3');
        $textAlign = $attributes['textAlign'] ?? 'left';
        $backgroundColor = $attributes['backgroundColor'] ?? '';
        $textColor = $attributes['textColor'] ?? '';
        $formId = 'charity-m3-newsletter-form-' . uniqid(); // Unique ID for the form and messages

        $submission_message = '';
        $message_type = ''; // 'success' or 'error'

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'charity_m3_subscribe' && isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_key($_POST['_wpnonce']), 'charity_m3_subscribe_nonce')) {

            $email = isset($_POST['newsletter_email']) ? sanitize_email(wp_unslash($_POST['newsletter_email'])) : '';
            $name = isset($_POST['newsletter_name']) ? sanitize_text_field(wp_unslash($_POST['newsletter_name'])) : null; // Optional name field

            if (!is_email($email)) {
                $submission_message = __('Invalid email address provided.', 'charity-m3');
                $message_type = 'error';
            } else {
                /** @var \App\Services\SubscriberService $subscriberService */
                $subscriberService = $this->app->make(\App\Services\SubscriberService::class);
                $subscriber_data = [
                    'name' => $name,
                    'status' => 'pending', // Or 'subscribed' if no double opt-in
                    'source' => 'website_signup_block',
                ];

                $result = $subscriberService->addSubscriber($email, $subscriber_data);

                if ($result) {
                    $submission_message = __('Thank you for subscribing!', 'charity-m3');
                    $message_type = 'success';
                    // TODO: Send opt-in confirmation email if status is 'pending'
                } else {
                    // Check if email already exists and is subscribed
                    $existing_subscriber = $subscriberService->getSubscriberByEmail($email);
                    if ($existing_subscriber && $existing_subscriber->status === 'subscribed') {
                        $submission_message = __('You are already subscribed.', 'charity-m3');
                        $message_type = 'info'; // Or success, depending on desired UX
                    } else {
                        $submission_message = __('Could not process your subscription. Please try again.', 'charity-m3');
                        $message_type = 'error';
                    }
                }
            }
        }


        $wrapper_attributes = get_block_wrapper_attributes([
            'style' => 'text-align:' . esc_attr($textAlign) . ';' .
                       ($backgroundColor ? 'background-color:' . esc_attr($backgroundColor) . ';' : '') .
                       ($textColor ? 'color:' . esc_attr($textColor) . ';' : ''),
            'class' => 'charity-m3-newsletter-signup-block align' . ($attributes['align'] ?? '')
        ]);

        ob_start();
        ?>
        <div <?php echo $wrapper_attributes; ?>>
            <?php if (!empty($title)): ?>
                <h3 class="md-typescale-headline-small" style="<?php echo $textColor ? 'color:' . esc_attr($textColor) . ';' : ''; ?>">
                    <?php echo esc_html($title); ?>
                </h3>
            <?php endif; ?>
            <?php if (!empty($description)): ?>
                <p class="md-typescale-body-medium" style="<?php echo $textColor ? 'color:' . esc_attr($textColor) . ';' : ''; ?>">
                    <?php echo esc_html($description); ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($submission_message)): ?>
                <div class="newsletter-submission-message type-<?php echo esc_attr($message_type); ?>" style="margin-bottom: 16px; padding: 10px; border: 1px solid <?php echo $message_type === 'error' ? 'red' : 'green'; ?>;">
                    <?php echo esc_html($submission_message); ?>
                </div>
            <?php endif; ?>

            <form class="newsletter-form" id="<?php echo esc_attr($formId); ?>" method="post" action="<?php echo esc_url(add_query_arg(null, null)); // Post to current page ?>">
                <input type="hidden" name="action" value="charity_m3_subscribe">
                <?php wp_nonce_field('charity_m3_subscribe_nonce'); ?>

                <?php // Optional: Add a name field, controlled by an attribute in block.json if desired ?>
                <?php if (isset($attributes['showNameField']) && $attributes['showNameField']): ?>
                <md-outlined-text-field
                    label="<?php echo esc_attr($attributes['namePlaceholder'] ?? __('Your Name', 'charity-m3')); ?>"
                    type="text"
                    name="newsletter_name"
                    style="margin-bottom: 16px; width: 100%; max-width: 400px;"
                ></md-outlined-text-field>
                <?php endif; ?>

                <md-outlined-text-field
                    label="<?php echo esc_attr($emailPlaceholder); ?>"
                    type="email"
                    name="newsletter_email"
                    required
                    value="" <?php // Clear value on successful submission if page reloads ?>
                    style="margin-bottom: 16px; width: 100%; max-width: 400px;"
                ></md-outlined-text-field>
                <md-filled-button type="submit">
                    <?php echo esc_html($buttonText); ?>
                </md-filled-button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}
