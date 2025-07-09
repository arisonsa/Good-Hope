<?php
/**
 * Render callback for the Card Item block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content (RichText 'text' attribute is already in $attributes).
 * @param WP_Block $block      Block instance.
 * @return string HTML output for the block.
 */

// Ensure this file is called only from WordPress context.
if (!defined('ABSPATH')) {
    exit;
}

// Extract attributes with defaults from block.json
$title = $attributes['title'] ?? '';
$subtitle = $attributes['subtitle'] ?? '';
$card_text_content = $attributes['text'] ?? ''; // Main content for the card's default slot
// $image_id = $attributes['imageId'] ?? 0; // Not directly used by x-m3.card, but good to have
$image_url = $attributes['imageUrl'] ?? '';
$image_alt = $attributes['imageAlt'] ?? $title; // Default alt to title
$href = $attributes['href'] ?? '';
$variant = $attributes['variant'] ?? 'elevated';
$interactive = $attributes['interactive'] ?? false;

// Prepare buttons for the 'actions' slot
$actions_slot_content = '';
$buttons_html = [];

if (!empty($attributes['button1Text']) && !empty($attributes['button1Href'])) {
    $buttons_html[] = \Roots\view('components.m3.button', [
        'type' => $attributes['button1Type'] ?? 'text',
        'href' => $attributes['button1Href'],
        'icon' => $attributes['button1Icon'] ?? null,
        'slot' => esc_html($attributes['button1Text']),
        // Note: The x-m3.button Blade component needs to handle the slot correctly.
        // We are passing content for its default slot here.
    ])->render();
}

if (!empty($attributes['button2Text']) && !empty($attributes['button2Href'])) {
     $buttons_html[] = \Roots\view('components.m3.button', [
        'type' => $attributes['button2Type'] ?? 'text',
        'href' => $attributes['button2Href'],
        'icon' => $attributes['button2Icon'] ?? null,
        'slot' => esc_html($attributes['button2Text']),
    ])->render();
}

if (!empty($buttons_html)) {
    // The <x-m3.card> Blade component expects content for the 'actions' slot
    // to be passed within <x-slot name="actions">...</x-slot>.
    // When calling programmatically, we can't directly construct that.
    // So, the x-m3.card component's view will need to be able to accept $actionsHtml as a prop.
    // For now, let's assume we'll modify x-m3.card to accept $actionsHtml.
    // Alternatively, we build the string for the slot here.
    $actions_slot_content = implode(' ', $buttons_html);
}


// Data to pass to the x-m3.card Blade component
$component_data = [
    'title' => $title,
    'subtitle' => $subtitle,
    'imageUrl' => $image_url,
    'imageAlt' => $image_alt,
    'href' => $href,
    'variant' => $variant,
    'interactive' => $interactive,
    'attributes' => new \Illuminate\View\ComponentAttributeBag(
        $block->parsed_block['attrs']['className'] ?? [] // Pass custom class if any
    ),
    // Pass the main card content (from RichText 'text' attribute) to the default slot
    // And the rendered buttons to the 'actions' slot.
    // This requires x-m3.card to handle these.
    'slot' => $card_text_content, // For the default slot
    'actionsHtml' => $actions_slot_content, // Custom prop for actions HTML
];


if (!function_exists('Roots\\view')) {
    return '<p>Error: Blade templating not available.</p>';
}

try {
    return \Roots\view('components.m3.card', $component_data)->render();
} catch (\Exception $e) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        return '<p>Error rendering Card Item block: ' . esc_html($e->getMessage()) . '</p>';
    }
    return '<p>Error rendering Card Item block.</p>';
}
