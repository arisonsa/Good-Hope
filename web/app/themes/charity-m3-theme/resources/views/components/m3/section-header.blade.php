@props([
    'title' => null,
    'subtitle' => null,
])

@if ($title || $subtitle)
<div {{ $attributes->merge(['class' => 'section-header']) }}>
    @if ($title)
        {{-- These will pick up global h2 styles from main.scss --}}
        <h2 class="md-typescale-display-small">
            {{ $title }}
        </h2>
    @endif
    @if ($subtitle)
        {{-- These will pick up global p styles or specific subtitle styles if added --}}
        <p class="md-typescale-headline-small mt-2 text-on-surface-variant">
            {{ $subtitle }}
        </p>
    @endif
</div>
@endif
