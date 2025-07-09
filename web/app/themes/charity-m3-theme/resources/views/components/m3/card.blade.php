@props([
    'href' => null,
    'imageUrl' => null,
    'imageAlt' => '',
    'title' => null,
    'subtitle' => null,
    'variant' => 'elevated', // 'elevated', 'filled', 'outlined'
    'interactive' => false, // If true and href, whole card is a link
    'actions' => null, // Slot for card actions (buttons, links)
])

@php
    $baseClasses = 'm3-card block rounded-xl overflow-hidden transition-shadow duration-300 ease-in-out';
    $variantClasses = '';

    switch ($variant) {
        case 'elevated':
            // Default M3 elevation for elevated card is level 1, level 3 on hover/focus
            // We use Tailwind shadows to simulate this. M3 uses specific shadow tokens.
            // For more precise M3 elevation, one would define CSS custom properties for shadows.
            $variantClasses = 'bg-surface-container-low dark:bg-surface-container-low-dark shadow-md hover:shadow-xl focus:shadow-xl';
            // M3 spec: --md-sys-elevation-level1, --md-sys-elevation-level3 on hover
            // These would be applied via CSS custom properties if not using Tailwind's general shadows.
            // Example: style="--card-elevation: var(--md-sys-elevation-level1); box-shadow: var(--card-elevation);"
            // For now, Tailwind shadows are a good approximation.
            break;
        case 'filled':
            $variantClasses = 'bg-surface-container-highest dark:bg-surface-container-highest-dark'; // M3 filled card color
            break;
        case 'outlined':
            $variantClasses = 'bg-surface dark:bg-surface-dark border border-outline dark:border-outline-dark'; // M3 outlined card
            break;
        default:
            $variantClasses = 'bg-surface-container-low dark:bg-surface-container-low-dark shadow-md';
            break;
    }

    $tag = ($interactive && $href) ? 'a' : 'div';
    $linkAttributes = ($interactive && $href) ? ['href' => esc_url($href)] : [];
@endphp

<{{ $tag }} {{ $attributes->merge(['class' => "{$baseClasses} {$variantClasses}"]) }} @if($interactive && $href) href="{{ esc_url($href) }}" @endif>
    @if ($imageUrl)
        <div class="card-media">
            <img src="{{ esc_url($imageUrl) }}" alt="{{ esc_attr($imageAlt ?: $title) }}" class="w-full h-48 object-cover">
        </div>
    @endif

    <div class="card-header p-4 @if(!$imageUrl && $slot->isEmpty() && !$actions) pb-4 @else pb-0 @endif">
        @if ($title)
            <h3 class="md-typescale-title-medium mb-1">
                {{ $title }}
            </h3>
        @endif
        @if ($subtitle)
            <p class="md-typescale-body-medium text-on-surface-variant dark:text-on-surface-variant-dark">
                {{ $subtitle }}
            </p>
        @endif
    </div>

    @if ($slot->isNotEmpty())
        <div class="card-content p-4 text-on-surface dark:text-on-surface-dark md-typescale-body-medium">
            {{ $slot }} {{-- Main content of the card --}}
        </div>
    @endif

    @if ($actions && $actions->isNotEmpty())
        <div class="card-actions p-4 pt-2 flex flex-wrap gap-2 justify-start">
            {{ $actions }}
        </div>
    @endif
</{{ $tag }}>

{{--
Example Usage:

<x-m3.card
    title="Event Title"
    subtitle="July 20, 2024"
    image-url="/path/to/event.jpg"
    href="/events/event-slug"
    variant="elevated"
    :interactive="true"
>
    <p>A brief description of the event goes here, providing more details.</p>
    <x-slot name="actions">
        <x-m3.button type="text" href="/events/event-slug/register">Register</x-m3.button>
        <x-m3.button type="text" icon="share">Share</x-m3.button>
    </x-slot>
</x-m3.card>

<x-m3.card variant="outlined" title="Quick Fact">
    <p>This is an important piece of information presented in an outlined card.</p>
</x-m3.card>
--}}
