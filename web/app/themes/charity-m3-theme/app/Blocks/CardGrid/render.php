<?php
/**
 * Render callback for the Card Grid block.
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

// Extract attributes with defaults from block.json being applied by Gutenberg
$grid_tag = $attributes['gridTag'] ?? 'div';
$cols = $attributes['cols'] ?? 'responsive-default';
$gap = $attributes['gap'] ?? '6';

// Get additional classes from block settings (align, custom classNames)
// These will be passed to the <x-m3.grid> component via its $attributes bag.
$wrapper_attributes_string = get_block_wrapper_attributes();
$attrs_array = [];
if (preg_match('/class="([^"]*)"/', $wrapper_attributes_string, $matches)) {
    $attrs_array['class'] = $matches[1];
}
// Add other attributes from $wrapper_attributes if necessary (e.g. style, if any)

// Data to pass to the Blade component
$component_data = [
    'tag' => $grid_tag,
    'cols' => $cols,
    'gap' => $gap,
    'attributes' => new \Illuminate\View\ComponentAttributeBag($attrs_array),
    // The $content variable already contains the rendered HTML of the InnerBlocks.
    // We need to pass this to the default slot of our x-m3.grid Blade component.
    // The Blade component's slot variable is typically named $slot.
    // We can't directly set $slot here, but we can pass $content and have the
    // Blade component echo it. Or, more cleanly, the x-m3.grid Blade component
    // should just have `{{ $slot }}` and we rely on how Blade handles content passed
    // between the opening and closing tags of the component when used by `do_blocks`.
    // When using render_callback, $content *is* the slot content.
];

if (!function_exists('Roots\\view')) {
    return '<p>Error: Blade templating not available.</p>';
}

try {
    // The $content (InnerBlocks output) needs to be passed into the slot of the x-m3.grid.
    // When rendering a Blade component directly and wanting to pass slot content programmatically,
    // it's often easier if the component's view file expects specific named variables for slots,
    // or if we use a more direct rendering method that supports slots.
    //
    // For a dynamic block's render_callback, the $content variable holds the rendered HTML
    // of the inner blocks. The <x-m3.grid> Blade component's view should simply have `{{ $slot }}`.
    // WordPress's block rendering mechanism will place $content into that slot.
    //
    // So, we render the component and expect $content to be injected.
    // The `render_block` function (which calls this callback) does something like:
    // $block_content = ($render_callback)( $attributes, $block->inner_html );
    // where $block->inner_html is $content.
    //
    // Our <x-m3.grid> Blade component's view is:
    // <{{ $tag }} {{ $attributes->merge(['class' => "grid {$colsFromLit} {$gapFromLit}"]) }}>  <-- Note: Lit component handles actual classes
    //     {{ $slot }}
    // </{{ $tag }}>
    // The Blade component itself renders <charity-grid> which has its own props for cols/gap.
    // The PHP class for x-m3.grid passes these through.

    // We need to ensure the $content (which is HTML string of inner blocks) is passed as the slot.
    // The most straightforward way is to construct the component call as if it were in a Blade file.
    // Since Roots\view() compiles Blade, we can't directly inject $content into a slot variable easily.
    //
    // A common pattern for dynamic blocks rendering components that have slots:
    // The component view itself must be prepared to echo its slot.
    // The $content is the result of `render_inner_blocks()`.
    //
    // Let's assume our x-m3.grid Blade component is simple:
    // <charity-grid tag="{{ $tag }}" cols="{{ $cols }}" gap="{{ $gap }}" {{ $attributes }}>
    //   {{ $slot }}
    // </charity-grid>
    // When WordPress renders this dynamic block, $content (the inner blocks' HTML)
    // is effectively the $slot for the top-level element rendered by this callback.
    // If our callback returns the <x-m3.grid> component's HTML, $content needs to be inside it.

    // The `view()->make()->with()->render()` approach allows passing data.
    // For slots with dynamic blocks, it's often simpler to build the string or use a dedicated slot variable.
    // However, `render_block_core_template_part` or similar patterns show that you can pass content.
    // Let's make the Blade component expect $slotContent explicitly.

    // No, the standard way is simpler: $content *is* the slot.
    // The render_callback should return the outer component's HTML, and WordPress
    // places $content correctly.

    $view = \Roots\view('components.m3.grid', $component_data);

    // The $content variable contains the already rendered HTML of the inner blocks.
    // We need to inject this $content into the slot of the rendered 'components.m3.grid' view.
    // This is tricky with how \Roots\view() works directly.
    // A common way is to make the component view accept $slotContent as a prop.

    // Let's adjust `resources/views/components/m3/grid.blade.php`
    // to accept an explicit `$slotContent` prop if passed, otherwise use `$slot`.
    // For dynamic blocks, it's more reliable to pass it explicitly.
    $component_data['slotContent'] = $content; // Pass inner blocks' HTML

    return \Roots\view('components.m3.grid', $component_data)->render();

} catch (\Exception $e) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        return '<p>Error rendering Card Grid block: ' . esc_html($e->getMessage()) . '</p>';
    }
    return '<p>Error rendering Card Grid block.</p>';
}
