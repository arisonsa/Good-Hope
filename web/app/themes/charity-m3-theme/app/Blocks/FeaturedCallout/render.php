<?php
/**
 * Render callback for the Featured Callout / Hero block.
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

// Extract attributes with defaults (defaults from block.json should be automatically applied by Gutenberg)
$title = $attributes['title'] ?? 'Featured Title';
$subtitle = $attributes['subtitle'] ?? 'Compelling subtitle to engage visitors.';
$background_image_url = $attributes['backgroundImageUrl'] ?? '';
// $background_image_id = $attributes['backgroundImageId'] ?? 0; // Not directly used by x-m3.hero, but good to have
$show_overlay = $attributes['showOverlay'] ?? true;
$background_color = $attributes['backgroundColor'] ?? 'var(--md-sys-color-surface-variant)';
$text_color = $attributes['textColor'] ?? 'var(--md-sys-color-on-surface-variant)';
$content_width = $attributes['contentWidth'] ?? 'container';
$text_alignment = $attributes['textAlignment'] ?? 'center';
$min_height = $attributes['minHeight'] ?? '60vh';
$buttons_data = $attributes['buttons'] ?? [];

// The x-m3.hero Blade component expects props that match its constructor or @props definition.
// We need to map our block attributes to these props.
// The Blade component for hero already expects these names.

// Prepare the buttons array for the Blade component
$hero_buttons = [];
if (!empty($buttons_data) && is_array($buttons_data)) {
    foreach ($buttons_data as $button) {
        $hero_buttons[] = [
            'text' => $button['text'] ?? __('Button', 'charity-m3'),
            'href' => $button['href'] ?? '#',
            'type' => $button['type'] ?? 'filled',
            'icon' => $button['icon'] ?? null,
            'target' => $button['target'] ?? null,
            'rel' => $button['rel'] ?? null,
        ];
    }
}

// Get additional classes from block settings (align, custom classNames)
$wrapper_attributes = get_block_wrapper_attributes(); // This will include alignwide, alignfull etc.

// We're rendering a Blade component. Ensure Acorn/Blade is available.
if (!function_exists('Roots\\view')) {
    // Fallback or error if Blade isn't available (shouldn't happen in Acorn context)
    return '<p>Error: Blade templating not available.</p>';
}

// Data to pass to the Blade component
$component_data = [
    'title' => $title,
    'subtitle' => $subtitle,
    'backgroundImage' => $background_image_url, // Blade component expects 'backgroundImage'
    'showOverlay' => $show_overlay,             // Blade component expects 'showOverlay'
    'backgroundColor' => $background_color,
    'textColor' => $text_color,
    'contentWidth' => $content_width,
    'textAlignment' => $text_alignment,
    'minHeight' => $min_height,
    'buttons' => $hero_buttons,
    'attributes' => new \Illuminate\View\ComponentAttributeBag([]), // Pass empty for now, or construct if needed
];


// Capture output of the Blade component
// The $wrapper_attributes from get_block_wrapper_attributes() should wrap the component output.
// However, the <x-m3.hero> itself is a <section>.
// So, we might not need an additional wrapper from $wrapper_attributes IF the hero itself handles alignment.
// But, if block supports like custom className need to be applied, $wrapper_attributes is the way.
// Let's assume the hero component is the root block element.
// We can merge classes into the component's attributes.

// A cleaner way to get just the classes:
$block_classes = '';
if (preg_match('/class="([^"]+)"/', $wrapper_attributes, $matches)) {
    $block_classes = $matches[1];
}
// Remove alignment classes if the component handles width internally, or keep if needed.
// For 'alignwide'/'alignfull', these often need to be on an outer wrapper.
// The <charity-hero> web component is a <section> and doesn't inherently know about alignwide/full.
// So, we should wrap it.

// The block editor's `useBlockProps` applies alignment classes to the root element in the editor.
// On the frontend, `get_block_wrapper_attributes()` provides these classes.

try {
    // The <x-m3.hero> Blade component already renders a <section> tag.
    // We need to pass the alignment classes to it.
    // The 'attributes' prop in the Blade component can take a ComponentAttributeBag.
    // Let's pass the raw $wrapper_attributes string and let the Blade component merge it carefully,
    // or extract just the class.

    // For simplicity, we'll let the Blade component handle merging attributes.
    // The `attributes` prop of the Blade component `<x-m3.hero>` will receive this.
    $component_data['attributes'] = new \Illuminate\View\ComponentAttributeBag(
        $block->parsed_block['attrs'] ?? [] // Pass block attributes if needed by component for merging
    );
    // Add the wrapper classes to the component attributes.
    // This is a bit manual; ideally the Blade component itself would accept a 'class' prop and merge it.
    // Or, we render the wrapper here.

    // Pass wrapper attributes (which include classes like alignwide, custom classNames)
    // to the Blade component. The Blade component's root element should use {{ $attributes->merge(...) }}
    // The <x-m3.hero> already does this with {{ $attributes }} on <charity-hero>.
    // We need to construct the ComponentAttributeBag correctly.

    // Extract classes from $wrapper_attributes to pass explicitly if preferred,
    // or pass the full string and let the component parse (less ideal).
    // Wordpress's get_block_wrapper_attributes() returns a string of attributes.
    // We need to convert this into an array for ComponentAttributeBag or merge.

    // Simplest: Assume the Blade component `<x-m3.hero>`'s {{ $attributes }} will handle it
    // if we pass what get_block_wrapper_attributes() gives, but that function returns a string.
    // We need to parse it or just extract classes.

    // Let's ensure the Blade component can receive and merge a 'class' attribute.
    // The <x-m3.hero> Blade file already uses `$attributes` on its `<charity-hero>` tag,
    // so any attributes passed in the $component_data['attributes'] will be applied.
    // We need to give it the classes from $wrapper_attributes.

    $attrs_array = [];
    // Simple parsing for class attribute from $wrapper_attributes
    if (preg_match('/class="([^"]*)"/', $wrapper_attributes, $matches)) {
        $attrs_array['class'] = $matches[1];
    }
    // Add other attributes from $wrapper_attributes if necessary (e.g. style, if any)
    // For now, primarily concerned with class for alignment.

    $component_data['attributes'] = new \Illuminate\View\ComponentAttributeBag($attrs_array);

    return \Roots\view('components.m3.hero', $component_data)->render();

} catch (\Exception $e) {
    // Log error and return error message
    // error_log('Error rendering Featured Callout block: ' . $e->getMessage());
    if (defined('WP_DEBUG') && WP_DEBUG) {
        return '<p>Error rendering block: ' . esc_html($e->getMessage()) . '</p>';
    }
    return '<p>Error rendering block.</p>';
}
