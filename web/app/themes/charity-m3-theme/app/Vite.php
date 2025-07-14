<?php

namespace App;

use Illuminate\Support\HtmlString;

class Vite
{
    protected static $manifest;
    protected static $hotFilePath;
    protected static $devServerUrl;

    public function __construct()
    {
        static::$hotFilePath = get_theme_file_path('/public/hot');
        static::$devServerUrl = $this->getDevServerUrl();
    }

    /**
     * Get the Vite development server URL from the 'hot' file.
     *
     * @return string|null
     */
    protected function getDevServerUrl(): ?string
    {
        if (file_exists(static::$hotFilePath)) {
            return rtrim(file_get_contents(static::$hotFilePath));
        }
        return null;
    }

    /**
     * Get the path to a versioned Vite asset.
     *
     * @param  string  $path
     * @return \Illuminate\Support\HtmlString
     */
    public static function asset(string $path): HtmlString
    {
        if (static::$devServerUrl) {
            // In development, return the full URL to the asset on the Vite dev server
            return new HtmlString(static::$devServerUrl . '/' . $path);
        }

        // In production, get the versioned asset path from the manifest
        if (!static::$manifest) {
            $manifestPath = get_theme_file_path('/public/manifest.json');
            if (file_exists($manifestPath)) {
                static::$manifest = json_decode(file_get_contents($manifestPath), true);
            } else {
                // Manifest not found, return un-versioned path as a fallback
                return new HtmlString(get_theme_file_uri('/public/' . $path));
            }
        }

        if (isset(static::$manifest[$path])) {
            return new HtmlString(get_theme_file_uri('/public/' . static::$manifest[$path]['file']));
        }

        // If asset not found in manifest, log an error and return un-versioned path
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Vite asset not found in manifest: {$path}");
        }
        return new HtmlString(get_theme_file_uri('/public/' . $path));
    }

    /**
     * Get the HTML script tag for the Vite HMR client during development.
     *
     * @return \Illuminate\Support\HtmlString|null
     */
    public static function hmrScript(): ?HtmlString
    {
        if (static::$devServerUrl) {
            return new HtmlString('<script type="module" src="' . static::$devServerUrl . '/@vite/client"></script>');
        }
        return null;
    }

    /**
     * Get the URI for a given asset.
     * A wrapper for asset() that returns a string instead of HtmlString.
     *
     * @param string $path
     * @return string
     */
    public static function uri(string $path): string
    {
        return (string) static::asset($path);
    }
}
