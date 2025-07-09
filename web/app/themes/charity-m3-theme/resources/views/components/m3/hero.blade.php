@props([
    'title' => null,
    'subtitle' => null,
    'backgroundImage' => null,
    'backgroundColor' => 'bg-surface-variant', // Default M3 surface variant
    'textColor' => null, // Auto-calculated if null
    'contentWidth' => 'container', // Default to container class: 'container', 'narrow', 'wide', 'full', 'edge-to-edge'
    'textAlignment' => 'text-center',
    'buttons' => [], // Array of button data: ['text' => '', 'href' => '', 'type' => 'filled|outlined', 'icon' => '']
    'minHeight' => '60vh', // Default value matching Lit component
    'showOverlay' => false
])

{{-- This Blade component now primarily acts as a bridge to the Lit Web Component --}}
{{-- It passes data as attributes. Complex data like arrays/objects are JSON encoded. --}}
<charity-hero
    @if($title) title="{{ $title }}" @endif
    @if($subtitle) subtitle="{{ $subtitle }}" @endif
    @if($backgroundImage) background-image="{{ $backgroundImage }}" @endif
    @if($backgroundColor) background-color="{{ $backgroundColor }}" @endif
    @if($textColor) text-color="{{ $textColor }}" @endif
    content-width="{{ $contentWidth }}"
    text-alignment="{{ $textAlignment }}"
    min-height="{{ $minHeight }}"
    @if($showOverlay) show-overlay @endif
    @if(!empty($buttons)) :buttons="{{ esc_attr(json_encode($buttons)) }}" @endif
    {{ $attributes }} {{-- Pass through any additional HTML attributes --}}
>
    {{-- Slot content is passed directly to the Web Component's <slot> --}}
    {{ $slot }}
</charity-hero>

{{--
Example Usage (remains the same, but now renders <charity-hero> web component):

<x-m3.hero
    title="Welcome to Our Charity"
    subtitle="Making a difference, one step at a time."
    background-image="/path/to/hero-image.jpg"
    :show-overlay="true"
    text-color="var(--md-sys-color-on-primary)" {{-- Example if bg is dark --}}
    :buttons="[
        ['text' => 'Donate Now', 'href' => '#donate', 'type' => 'filled', 'icon' => 'favorite'],
        ['text' => 'Learn More', 'href' => '/about-us', 'type' => 'outlined']
    ]"
    content-width="wide"
    text-alignment="text-left"
    min-height="80vh"
/>

<x-m3.hero
    title="Our Mission"
    subtitle="To provide aid and support to those in need."
    background-color="var(--md-sys-color-primary-container)"
    text-color="var(--md-sys-color-on-primary-container)"
    :buttons="[['text' => 'Get Involved', 'href' => '#get-involved', 'type' => 'tonal']]"
>
    <p class="md-typescale-body-medium" style="color: var(--md-sys-color-on-primary-container);">Additional details can go here via the slot.</p>
</x-m3.hero>
--}}
