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
        $backgroundColor = $attributes['backgroundColor'] ?? ''; // CSS color value or var()
        $textColor = $attributes['textColor'] ?? '';           // CSS color value or var()
        $formId = 'charity-m3-newsletter-form-' . ($this->app->isProduction() ? md5(serialize($attributes)) : uniqid());


        $submission_message_text = '';
        $submission_message_type = ''; // 'success', 'error', 'info'

        // Handle form submission (this logic remains in PHP)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
            isset($_POST['action']) && $_POST['action'] === 'charity_m3_subscribe' &&
            isset($_POST['_wpnonce_newsletter_signup_block']) &&
            wp_verify_nonce(sanitize_key($_POST['_wpnonce_newsletter_signup_block']), 'charity_m3_subscribe_nonce_block_' . $attributes['blockId'] ?? '')) { // Use a more specific nonce if blockId is available

            $email = isset($_POST['newsletter_email']) ? sanitize_email(wp_unslash($_POST['newsletter_email'])) : '';
            $name = (isset($attributes['showNameField']) && $attributes['showNameField'] && isset($_POST['newsletter_name']))
                    ? sanitize_text_field(wp_unslash($_POST['newsletter_name']))
                    : null;

            if (!is_email($email)) {
                $submission_message_text = __('Invalid email address provided.', 'charity-m3');
                $submission_message_type = 'error';
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
                    $submission_message_text = __('Thank you for subscribing!', 'charity-m3');
                    $submission_message_type = 'success';
                } else {
                    $existing_subscriber = $subscriberService->getSubscriberByEmail($email);
                    if ($existing_subscriber && $existing_subscriber->status === 'subscribed') {
                        $submission_message_text = __('You are already subscribed.', 'charity-m3');
                        $submission_message_type = 'info';
                    } else {
                        $submission_message_text = __('Could not process your subscription. Please try again.', 'charity-m3');
                        $submission_message_type = 'error';
                    }
                }
            }
        }

        $wrapper_attributes = get_block_wrapper_attributes([
            'style' => trim(
                ($textAlign ? 'text-align:' . esc_attr($textAlign) . ';' : '') .
                ($backgroundColor ? 'background-color:' . esc_attr($backgroundColor) . ';' : '') .
                ($textColor ? 'color:' . esc_attr($textColor) . ';' : '')
            ),
            'class' => 'charity-m3-newsletter-signup-block align' . ($attributes['align'] ?? '')
        ]);

        // Prepare attributes for the Web Component
        $wc_attrs = [
            'email-placeholder' => esc_attr($emailPlaceholder),
            'button-text' => esc_attr($buttonText),
            'form-action' => esc_url(add_query_arg(null, null)), // Post to current page
            'nonce-value' => wp_create_nonce('charity_m3_subscribe_nonce_block_' . $attributes['blockId'] ?? ''),
            'nonce-name' => '_wpnonce_newsletter_signup_block', // Ensure this matches the check
            'form-id' => esc_attr($formId),
        ];
        if (isset($attributes['showNameField']) && $attributes['showNameField']) {
            $wc_attrs['show-name-field'] = ''; // Boolean attribute
            $wc_attrs['name-placeholder'] = esc_attr($attributes['namePlaceholder'] ?? __('Your Name', 'charity-m3'));
        }
        if ($submission_message_text) {
            $wc_attrs['submission-message'] = esc_attr($submission_message_text);
            $wc_attrs['message-type'] = esc_attr($submission_message_type);
        }

        $wc_attrs_string = '';
        foreach ($wc_attrs as $key => $value) {
            // For boolean attributes, only include the key if true.
            if (is_bool($value) && $value === true) { // This logic needs to be in Lit component for boolean props from attributes
                 $wc_attrs_string .= $key . ' '; // Lit handles boolean attributes by presence
            } elseif (!is_bool($value)) {
                 $wc_attrs_string .= $key . '="' . $value . '" ';
            }
        }
        // A better way for boolean attributes if Lit component expects presence:
        if (isset($attributes['showNameField']) && $attributes['showNameField']) {
             $wc_attrs_string .= 'show-name-field ';
        }


        ob_start();
        ?>
        <div <?php echo $wrapper_attributes; ?>>
            <?php if (!empty($title)): ?>
                <h3 class="block-title" style="<?php echo $textColor ? 'color:' . esc_attr($textColor) . ';' : ''; ?>">
                    <?php echo esc_html($title); ?>
                </h3>
            <?php endif; ?>
            <?php if (!empty($description)): ?>
                <p class="block-description" style="<?php echo $textColor ? 'color:' . esc_attr($textColor) . ';' : ''; ?>">
                    <?php echo esc_html($description); ?>
                </p>
            <?php endif; ?>

            <newsletter-signup-form <?php echo trim($wc_attrs_string); ?>>
                {{-- Any slotted content for the web component could go here if designed for it --}}
            </newsletter-signup-form>
        </div>
        <?php
        return ob_get_clean();
    }
}
