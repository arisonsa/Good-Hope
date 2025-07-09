@props([
    'title' => null,
    'text' => null,
    'buttons' => [],
    'backgroundImage' => null,
    'backgroundColor' => 'bg-primary-container',
    'textColor' => null, // Auto-calculated by component class
    'textAlignment' => 'text-center',
    'contentWidth' => 'container',
    'padding' => 'py-12 md:py-20'
])

@php
    $sectionStyle = '';
    if ($backgroundImage) {
        $sectionStyle .= "background-image: url('" . esc_url($backgroundImage) . "');";
    }
    // Handle direct CSS var for background color
    if (str_starts_with($backgroundColor, 'var(--md-sys-color')) {
        $sectionStyle .= "background-color: {$backgroundColor};";
        $backgroundColor = ''; // Prevent it from being applied as a class by $attributes->merge
    }
@endphp

<section
    {{ $attributes->merge(['class' => "cta-banner-section {$padding} {$backgroundColor} {$textAlignment} {$textColor} relative " . ($backgroundImage ? 'bg-cover bg-center' : '')]) }}
    style="{{ $sectionStyle }}"
>
    @if ($backgroundImage)
        <div class="absolute inset-0 bg-black opacity-40 dark:opacity-60 z-0"></div> {{-- Overlay --}}
    @endif

    <div class="{{ $contentWidth }} relative z-10">
        @if ($title)
            <h2 class="md-typescale-headline-large lg:md-typescale-display-small mb-4 font-semibold">
                {!! $title !!}
            </h2>
        @endif

        @if ($text || $slot->isNotEmpty())
            <div class="md-typescale-body-large lg:md-typescale-headline-small max-w-prose {{ $textAlignment === 'text-center' ? 'mx-auto' : ($textAlignment === 'text-right' ? 'ml-auto' : 'mr-auto') }} mb-8 opacity-90">
                @if ($text)
                    <p>{!! $text !!}</p>
                @endif
                {{ $slot }} {{-- Allow for more complex content via slot --}}
            </div>
        @endif

        @if (!empty($buttons))
            <div class="mt-8 flex flex-wrap {{ $textAlignment === 'text-center' ? 'justify-center' : ($textAlignment === 'text-right' ? 'justify-end' : 'justify-start') }} gap-4">
                @foreach ($buttons as $button)
                    <x-m3.button
                        :type="$button['type'] ?? 'filled'"
                        :href="$button['href'] ?? '#'"
                        :icon="$button['icon'] ?? null"
                        class="{{ $button['class'] ?? '' }}"
                    >
                        {{ $button['text'] }}
                    </x-m3.button>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{--
Example Usage:

<x-m3.cta-banner
    title="Ready to Make a Difference?"
    text="Your support can change lives. Join us today in our mission to bring hope and aid to communities in need."
    :buttons="[
        ['text' => 'Donate Now', 'href' => '#donate', 'type' => 'filled'],
        ['text' => 'Volunteer', 'href' => '/volunteer', 'type' => 'outlined']
    ]"
    background-color="bg-tertiary-container"
    text-alignment="text-left"
    content-width="wide"
/>
--}}
