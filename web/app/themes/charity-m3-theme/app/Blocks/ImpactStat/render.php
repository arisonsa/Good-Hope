<?php
/**
 * Render callback for the Impact Stat block.
 */

if (!defined('ABSPATH')) {
    exit;
}

$value = $attributes['value'] ?? 0;
$prefix = $attributes['prefix'] ?? '';
$suffix = $attributes['suffix'] ?? '';
$description = $attributes['description'] ?? '';
$icon = $attributes['icon'] ?? '';

$wrapper_attributes = get_block_wrapper_attributes();

?>

<div <?php echo $wrapper_attributes; ?>>
    <charity-counter
        target-value="<?php echo esc_attr($value); ?>"
        prefix="<?php echo esc_attr($prefix); ?>"
        suffix="<?php echo esc_attr($suffix); ?>"
    >
        <?php if ($icon): ?>
            <md-icon slot="icon"><?php echo esc_html($icon); ?></md-icon>
        <?php endif; ?>
        <?php echo wp_kses_post($description); ?>
    </charity-counter>
</div>
