<?php
/**
 * Render callback for the Testimonial block.
 */

if (!defined('ABSPATH')) {
    exit;
}

$quote = $attributes['quote'] ?? '';
$author_name = $attributes['authorName'] ?? '';
$author_title = $attributes['authorTitle'] ?? '';
$author_image_url = $attributes['authorImageUrl'] ?? '';

$wrapper_attributes = get_block_wrapper_attributes();

?>

<div <?php echo $wrapper_attributes; ?>>
    <charity-testimonial
        quote="<?php echo esc_attr($quote); // Note: quote is passed as prop, but also slotted for RichText saving to work. Lit component should prioritize slot. ?>"
        author-name="<?php echo esc_attr($author_name); ?>"
        author-title="<?php echo esc_attr($author_title); ?>"
        author-image-url="<?php echo esc_url($author_image_url); ?>"
    >
        <?php echo wp_kses_post($quote); ?>
    </charity-testimonial>
</div>
