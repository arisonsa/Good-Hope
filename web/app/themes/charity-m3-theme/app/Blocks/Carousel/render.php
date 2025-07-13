<?php
/**
 * Render callback for the Carousel block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Content of the inner blocks.
 * @param WP_Block $block      Block instance.
 * @return string HTML output for the block.
 */

// Ensure this file is called only from WordPress context.
if (!defined('ABSPATH')) {
    exit;
}

// Extract attributes with defaults from block.json
$slides_per_view = $attributes['slidesPerView'] ?? 1;
$space_between = $attributes['spaceBetween'] ?? 16;
$loop = $attributes['loop'] ?? false;
$autoplay = $attributes['autoplay'] ?? false;
$delay = $attributes['delay'] ?? 3000;
$show_navigation = $attributes['showNavigation'] ?? true;
$show_pagination = $attributes['showPagination'] ?? true;
$effect = $attributes['effect'] ?? 'slide';

// Construct the Swiper options object
$swiper_options = [
    'slidesPerView' => $slides_per_view,
    'spaceBetween' => $space_between,
    'loop' => $loop,
    'effect' => $effect,
];
if ($autoplay) {
    $swiper_options['autoplay'] = [
        'delay' => $delay,
        'disableOnInteraction' => false, // Keep playing after user interacts
    ];
}

// Get block wrapper attributes (for alignwide, etc.)
$wrapper_attributes = get_block_wrapper_attributes();

?>

<div <?php echo $wrapper_attributes; ?>>
    <charity-carousel
        options='<?php echo esc_attr(json_encode($swiper_options)); ?>'
        <?php if ($show_navigation): ?>navigation<?php endif; ?>
        <?php if ($show_pagination): ?>pagination<?php endif; ?>
    >
        <?php echo $content; // The rendered HTML of the InnerBlocks ?>
    </charity-carousel>
</div>
