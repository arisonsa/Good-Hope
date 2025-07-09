@props([
    'title' => null,
    'text' => null,
    'buttons' => [],
    'backgroundImage' => null,
    'backgroundColor' => 'bg-primary-container',
    'textColor' => null,
    'textAlignment' => 'center',
    'contentWidth' => 'container',
    'padding' => '3rem 0', // Default matching Lit component
    'showOverlay' => true  // Default matching Lit component
])

<charity-cta-banner
    @if($title) title="{{ esc_attr($title) }}" @endif
    @if($text) text="{{ esc_attr($text) }}" @endif
    @if(!empty($buttons)) :buttons="{{ esc_attr(json_encode($buttons)) }}" @endif
    @if($backgroundImage) background-image="{{ esc_url($backgroundImage) }}" @endif
    @if($backgroundColor) background-color="{{ esc_attr($backgroundColor) }}" @endif
    @if($textColor) text-color="{{ esc_attr($textColor) }}" @endif
    text-alignment="{{ $textAlignment }}"
    content-width="{{ $contentWidth }}"
    padding="{{ $padding }}"
    @if($showOverlay) show-overlay @endif
    {{ $attributes }}
>
    {{-- Default slot for additional rich text content --}}
    {{ $slot }}
</charity-cta-banner>

{{--
Example Usage (remains the same for the Blade component user):

<x-m3.cta-banner
    title="Ready to Make a Difference?"
    text="Your support can change lives. Join us today in our mission to bring hope and aid to communities in need."
    :buttons="[
        ['text' => 'Donate Now', 'href' => '#donate', 'type' => 'filled', 'icon' => 'volunteer_activism'],
        ['text' => 'Volunteer', 'href' => '/volunteer', 'type' => 'outlined']
    ]"
    background-color="var(--md-sys-color-tertiary-container)"
    text-color="var(--md-sys-color-on-tertiary-container)"
    text-alignment="text-left"
    content-width="wide"
    padding="4rem 0"
/>
--}}
