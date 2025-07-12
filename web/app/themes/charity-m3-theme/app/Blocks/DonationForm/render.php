<?php
/**
 * Render callback for the Donation Form block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 * @return string HTML output for the block.
 */

// Ensure this file is called only from WordPress context.
if (!defined('ABSPATH')) {
    exit;
}

// Extract attributes with defaults
$title = $attributes['title'] ?? __('Make a Donation', 'charity-m3');
$description = $attributes['description'] ?? __('Your generous gift helps us continue our mission.', 'charity-m3');
$suggested_amounts_str = $attributes['suggestedAmounts'] ?? '25, 50, 100, 250';
$default_frequency = $attributes['defaultFrequency'] ?? 'one-time';
$campaign_id = $attributes['campaignId'] ?? null;

// Convert suggested amounts string to an array of numbers
$suggested_amounts = array_map('intval', explode(',', $suggested_amounts_str));

// Get block wrapper attributes (for alignment, custom classes)
$wrapper_attributes = get_block_wrapper_attributes();

// Prepare attributes for the <charity-donation-form> Web Component
$wc_attrs_string = '';
$wc_attrs = [
    'suggested-amounts' => esc_attr(json_encode($suggested_amounts)),
    'default-frequency' => esc_attr($default_frequency),
];
if ($campaign_id) {
    $wc_attrs['campaign-id'] = esc_attr($campaign_id);
}
foreach ($wc_attrs as $key => $value) {
    $wc_attrs_string .= $key . '=\'' . $value . '\' '; // Use single quotes for JSON
}

?>

<div <?php echo $wrapper_attributes; ?>>
    <?php if (!empty($title)): ?>
        <h3 class="block-title md-typescale-headline-medium">
            <?php echo esc_html($title); ?>
        </h3>
    <?php endif; ?>
    <?php if (!empty($description)): ?>
        <p class="block-description md-typescale-body-large">
            <?php echo esc_html($description); ?>
        </p>
    <?php endif; ?>

    <charity-donation-form <?php echo trim($wc_attrs_string); ?>>
        {{-- Any slotted content could go here if the component supported it --}}
    </charity-donation-form>
</div>
