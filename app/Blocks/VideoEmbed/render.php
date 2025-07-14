<?php
/**
 * Render callback for the Styled Video Embed block.
 */

if (!defined('ABSPATH')) {
    exit;
}

$url = $attributes['url'] ?? '';
$caption = $attributes['caption'] ?? '';
$aspect_ratio = $attributes['aspectRatio'] ?? '16/9';

if (empty($url)) {
    return '';
}

$wrapper_attributes = get_block_wrapper_attributes();
$embed_html = wp_oembed_get($url);

// Calculate padding for aspect ratio
$ratio_parts = explode('/', $aspect_ratio);
$padding_bottom = (isset($ratio_parts[1]) && $ratio_parts[1] > 0)
    ? ($ratio_parts[1] / $ratio_parts[0]) * 100
    : 56.25; // Default to 16:9

?>

<figure <?php echo $wrapper_attributes; ?>>
    <div class="charity-m3-video-wrapper" style="position: relative; padding-bottom: <?php echo esc_attr($padding_bottom); ?>%; height: 0; overflow: hidden;">
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
            <?php echo $embed_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
    </div>
    <?php if (!empty($caption)): ?>
        <figcaption class="wp-element-caption">
            <?php echo wp_kses_post($caption); ?>
        </figcaption>
    <?php endif; ?>
</figure>
