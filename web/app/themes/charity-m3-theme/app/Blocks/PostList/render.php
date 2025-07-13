<?php
/**
 * Render callback for the Post List block.
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
$post_type = $attributes['postType'] ?? 'post';
$taxonomy = $attributes['taxonomy'] ?? '';
$terms = $attributes['terms'] ?? [];
$count = $attributes['count'] ?? 3;
$order_by = $attributes['orderBy'] ?? 'date';
$order = $attributes['order'] ?? 'DESC';
$columns = $attributes['columns'] ?? 'responsive-default';
$gap = $attributes['gap'] ?? '6';

// Build WP_Query arguments
$query_args = [
    'post_type' => sanitize_key($post_type),
    'posts_per_page' => (int) $count,
    'orderby' => sanitize_key($order_by),
    'order' => in_array(strtoupper($order), ['ASC', 'DESC']) ? strtoupper($order) : 'DESC',
    'post_status' => 'publish',
    'ignore_sticky_posts' => true,
];

// Add taxonomy query if taxonomy and terms are set
if (!empty($taxonomy) && !empty($terms)) {
    $query_args['tax_query'] = [
        [
            'taxonomy' => sanitize_key($taxonomy),
            'field'    => 'term_id',
            'terms'    => array_map('intval', $terms),
        ],
    ];
}

$post_list_query = new WP_Query($query_args);

// Get block wrapper attributes (for align, etc.)
$wrapper_attributes = get_block_wrapper_attributes();

?>

<div <?php echo $wrapper_attributes; ?>>
    <?php if ($post_list_query->have_posts()): ?>
        <x-m3.grid :cols="$columns" :gap="$gap">
            <?php while ($post_list_query->have_posts()): ?>
                <?php $post_list_query->the_post(); ?>
                <?php
                    // Pass specific subtitle context based on what's being queried
                    $subtitle_context = get_post_type_object(get_post_type())->labels->singular_name . ' | ' . get_the_date();
                ?>
                @include('partials.card-post', ['subtitle_context' => $subtitle_context])
            <?php endwhile; ?>
        </x-m3.grid>
        <?php wp_reset_postdata(); ?>
    <?php else: ?>
        <p>{{ __('No posts found matching your criteria.', 'charity-m3') }}</p>
    <?php endif; ?>
</div>
