<?php

namespace App\Theme;

class CustomizerManager
{
    public function __construct()
    {
        add_action('customize_register', [$this, 'registerCustomizerSettings']);
        add_action('wp_head', [$this, 'outputCustomizerCss'], 100); // Output dynamic CSS
    }

    public function registerCustomizerSettings(\WP_Customize_Manager $wp_customize)
    {
        // --- Homepage Settings Section ---
        $wp_customize->add_section('homepage_settings_section', [
            'title'    => __('Homepage Settings', 'charity-m3'),
            'priority' => 20,
        ]);

        // Get available alerts for the dropdown
        $alerts = get_posts([
            'post_type' => 'alert',
            'post_status' => 'publish',
            'numberposts' => -1,
        ]);
        $alert_choices = ['none' => __('— None —', 'charity-m3')];
        if ($alerts) {
            foreach ($alerts as $alert) {
                $alert_choices[$alert->ID] = $alert->post_title;
            }
        }

        // Active Alert Setting
        $wp_customize->add_setting('homepage_active_alert', [
            'default'   => 'none',
            'transport' => 'refresh',
            'sanitize_callback' => 'absint', // or a custom callback to validate post ID
        ]);
        $wp_customize->add_control('homepage_active_alert_control', [
            'label'    => __('Active Crisis Alert', 'charity-m3'),
            'description' => __('Select an alert to feature prominently at the top of the homepage. To create a new alert, go to the "Crisis Alerts" menu in the dashboard.', 'charity-m3'),
            'section'  => 'homepage_settings_section',
            'settings' => 'homepage_active_alert',
            'type'     => 'select',
            'choices'  => $alert_choices,
        ]);


        // --- Site Identity Panel (for Logo) ---
        // WordPress already has a 'title_tagline' section for site title, tagline, and logo.
        // We just ensure our theme supports custom logo.
        // This is typically done in ThemeServiceProvider or functions.php: add_theme_support('custom-logo');
        // We'll add it to ThemeServiceProvider for completeness.

        // --- M3 Theme Colors Section ---
        $wp_customize->add_section('m3_theme_colors_section', [
            'title'    => __('M3 Theme Colors', 'charity-m3'),
            'priority' => 30, // Adjust priority to position it
        ]);

        // Primary Color Setting
        $wp_customize->add_setting('m3_primary_color', [
            'default'   => '#6750A4', // M3 Default Primary
            'transport' => 'refresh', // or 'postMessage' for live preview with JS
            'sanitize_callback' => 'sanitize_hex_color',
        ]);
        $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'm3_primary_color_control', [
            'label'    => __('Primary Color', 'charity-m3'),
            'section'  => 'm3_theme_colors_section',
            'settings' => 'm3_primary_color',
        ]));

        // Secondary Color Setting
        $wp_customize->add_setting('m3_secondary_color', [
            'default'   => '#625B71', // M3 Default Secondary
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color',
        ]);
        $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'm3_secondary_color_control', [
            'label'    => __('Secondary Color', 'charity-m3'),
            'section'  => 'm3_theme_colors_section',
            'settings' => 'm3_secondary_color',
        ]));

        // Tertiary Color Setting (Optional, add if needed)
        // $wp_customize->add_setting('m3_tertiary_color', [
        //     'default'   => '#7D5260', // M3 Default Tertiary
        //     'transport' => 'refresh',
        //     'sanitize_callback' => 'sanitize_hex_color',
        // ]);
        // $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'm3_tertiary_color_control', [
        //     'label'    => __('Tertiary Color', 'charity-m3'),
        //     'section'  => 'm3_theme_colors_section',
        //     'settings' => 'm3_tertiary_color',
        // ]));
    }

    /**
     * Output CSS in wp_head from Customizer settings.
     * This will override the :root variables defined in main.scss.
     */
    public function outputCustomizerCss()
    {
        $primary_color = get_theme_mod('m3_primary_color', '#6750A4');
        $secondary_color = get_theme_mod('m3_secondary_color', '#625B71');
        // $tertiary_color = get_theme_mod('m3_tertiary_color', '#7D5260');

        // Helper function to generate derived M3 colors (simplified placeholder)
        // In a real M3 setup, you'd use a library or more complex logic to derive all related
        // -on, -container, -on-container colors from the base primary/secondary/tertiary.
        // For this example, we'll only override the base colors and assume the SCSS
        // defines the full palette which might partially rely on these.
        // A more advanced setup would generate ALL related M3 tokens.

        $css = '<style type="text/css" id="charity-m3-customizer-css">:root {';

        if ($primary_color) {
            $css .= '--md-sys-color-primary: ' . esc_attr($primary_color) . ';';
            // Simple example for on-primary (assumes light or dark based on primary)
            // This is NOT a proper M3 color generation. A real system uses HCT.
            $css .= '--md-sys-color-on-primary: ' . (self::isColorDark($primary_color) ? '#FFFFFF' : '#000000') . ';';
            // Placeholder for primary-container (needs proper generation)
            // $css .= '--md-sys-color-primary-container: ' . self::adjustColor($primary_color, 60) . ';';
            // $css .= '--md-sys-color-on-primary-container: ' . (self::isColorDark(self::adjustColor($primary_color, 60)) ? '#FFFFFF' : '#000000') . ';';
        }
        if ($secondary_color) {
            $css .= '--md-sys-color-secondary: ' . esc_attr($secondary_color) . ';';
            $css .= '--md-sys-color-on-secondary: ' . (self::isColorDark($secondary_color) ? '#FFFFFF' : '#000000') . ';';
        }
        // if ($tertiary_color) {
        //     $css .= '--md-sys-color-tertiary: ' . esc_attr($tertiary_color) . ';';
        //     $css .= '--md-sys-color-on-tertiary: ' . (self::isColorDark($tertiary_color) ? '#FFFFFF' : '#000000') . ';';
        // }

        $css .= '} </style>';

        // Only output if there's something to output
        if (strpos($css, ';') !== false) {
            echo "\n" . $css . "\n";
        }
    }

    /**
     * Helper to check if a hex color is dark.
     * Basic implementation based on luminance.
     * @param string $hexcolor
     * @return bool
     */
    private static function isColorDark(string $hexcolor): bool
    {
        $hexcolor = ltrim($hexcolor, '#');
        if (strlen($hexcolor) == 3) {
            $hexcolor = $hexcolor[0] . $hexcolor[0] . $hexcolor[1] . $hexcolor[1] . $hexcolor[2] . $hexcolor[2];
        }
        $r = hexdec(substr($hexcolor, 0, 2));
        $g = hexdec(substr($hexcolor, 2, 2));
        $b = hexdec(substr($hexcolor, 4, 2));
        // Standard luminance calculation
        $luminance = (0.2126 * $r + 0.7152 * $g + 0.0722 * $b);
        return $luminance < 128; // Threshold for darkness
    }

    // Placeholder for a color adjustment function (very simplified)
    // private static function adjustColor(string $hex, int $steps): string {
    //     // This is not how M3 tones are generated. M3 uses HCT color space.
    //     // This is a naive example and should be replaced with a proper library for M3 theming.
    //     // ...
    //     return $hex;
    // }
}
