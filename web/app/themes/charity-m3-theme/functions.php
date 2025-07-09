<?php
/**
 * Theme functions and definitions.
 *
 * For more information on hooks, actions, and filters,
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package CharityM3
 */

// Ensure Composer autoloader is loaded.
$composer_autoloader = __DIR__ . '/vendor/autoload.php';
if (file_exists($composer_autoloader)) {
    require_once $composer_autoloader;
} else {
    // Fallback if theme is not installed via Composer (e.g. direct upload)
    // This assumes Acorn is installed as a project dependency in Bedrock root.
    $bedrock_autoloader = dirname(__DIR__, 4) . '/vendor/autoload.php'; // Adjust depth based on final structure
    if (file_exists($bedrock_autoloader)) {
        require_once $bedrock_autoloader;
    } else {
        if (is_admin()) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                echo 'Composer autoloader not found. Please install theme dependencies.';
                echo '</p></div>';
            });
        }
        // return; // Optionally halt if autoloader is critical
    }
}

// Boot Acorn.
if (! class_exists(\Roots\Acorn\Application::class)) {
    if (is_admin()) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            _e('Acorn not found. Please ensure Acorn is installed. Theme functionality will be limited.', 'charity-m3');
            echo '</p></div>';
        });
    }
    // return; // Optionally halt if Acorn is critical
} else {
    // Ensure the ThemeServiceProvider class exists.
    // Assumes PSR-4 autoloading from the 'app' directory relative to the theme root.
    if (class_exists(\App\Providers\ThemeServiceProvider::class)) {
        try {
            \Roots\Acorn\Application::configure(untrailingslashit(get_template_directory()))
                ->withProviders([
                    \App\Providers\ThemeServiceProvider::class,
                    \App\Providers\NewsletterServiceProvider::class, // Add this line
                ])
                ->boot();
        } catch (\Exception $e) {
            if (is_admin()) {
                add_action('admin_notices', function() use ($e) {
                    echo '<div class="notice notice-error"><p>';
                    printf(__('Error booting Acorn with ThemeServiceProvider: %s', 'charity-m3'), esc_html($e->getMessage()));
                    echo '</p></div>';
                });
            }
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Acorn Boot Error with ThemeServiceProvider: ' . $e->getMessage());
            }
        }
    } else {
        // Fallback or error if ThemeServiceProvider is missing
        if (is_admin()) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                _e('ThemeServiceProvider not found. Theme may not function correctly. Attempting basic Acorn boot.', 'charity-m3');
                echo '</p></div>';
            });
        }
        // Attempt to boot Acorn without the provider as a last resort.
        try {
            \Roots\Acorn\Bootloader::getInstance()->boot();
        } catch (\Exception $e) {
             if (is_admin()) {
                add_action('admin_notices', function() use ($e) {
                    echo '<div class="notice notice-error"><p>';
                    printf(__('Error booting Acorn (fallback): %s', 'charity-m3'), esc_html($e->getMessage()));
                    echo '</p></div>';
                });
            }
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Acorn Boot Error (fallback): ' . $e->getMessage());
            }
        }
    }
}

/**
 * Define path constants for the theme.
 */
if (!defined('CHARITY_M3_THEME_PATH')) {
    define('CHARITY_M3_THEME_PATH', trailingslashit(get_template_directory()));
}
if (!defined('CHARITY_M3_THEME_URI')) {
    define('CHARITY_M3_THEME_URI', trailingslashit(get_template_directory_uri()));
}

// Any other critical, non-Acorn-dependent functions for the theme can go here.
// However, most functionality should be housed in Acorn service providers or other classes.
