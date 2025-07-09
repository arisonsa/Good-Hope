@props([
    'href' => null,
    'imageUrl' => null,
    'imageAlt' => '',
    'title' => null,
    'subtitle' => null,
    'variant' => 'elevated', // 'elevated', 'filled', 'outlined'
    'interactive' => false,
    'actions' => null,      // Slot object for actions when used directly in Blade
    'actionsHtml' => '',    // Pre-rendered HTML string for actions from PHP render_callback
])

<charity-card
    @if($href) href="{{ esc_url($href) }}" @endif
    @if($imageUrl) image-url="{{ esc_url($imageUrl) }}" @endif
    @if($imageAlt) image-alt="{{ esc_attr($imageAlt) }}" @endif
    @if($title) title="{{ esc_attr($title) }}" @endif
    @if($subtitle) subtitle="{{ esc_attr($subtitle) }}" @endif
    variant="{{ $variant }}"
    @if($interactive) interactive @endif
    {{ $attributes }} {{-- Pass through any additional HTML attributes --}}
>
    {{-- Default slot for main card content --}}
    {{ $slot }}

    {{-- Named slot for actions --}}
    @if (!empty($actionsHtml))
        <div slot="actions">
            {!! $actionsHtml !!}
        </div>
    @elseif ($actions && $actions->isNotEmpty())
        <div slot="actions">
            {{ $actions }}
        </div>
    @endif
</charity-card>

{{--
Example Usage (remains the same for the Blade component user):

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
        <x-m3.button type="text" icon="share">Share</x-m3.button> {{-- Assuming x-m3.button uses md-icon or similar --}}
    </x-slot>
</x-m3.card>

<x-m3.card variant="outlined" title="Quick Fact">
    <p>This is an important piece of information presented in an outlined card.</p>
</x-m3.card>
--}}
