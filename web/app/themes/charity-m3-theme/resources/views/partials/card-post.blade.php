@php
  // This partial assumes it's being called inside The Loop.
  // It can be passed optional context, e.g., for the subtitle.
  $subtitle_context = $subtitle_context ?? get_the_date(); // Default to date if no context passed

  $card_data = [
      'title' => get_the_title(),
      'subtitle' => $subtitle_context,
      'imageUrl' => has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'medium_large') : 'https://picsum.photos/seed/' . get_the_ID() . '/600/400', // Placeholder
      'imageAlt' => has_post_thumbnail() ? get_the_post_thumbnail_caption() ?: get_the_title() : get_the_title(),
      'href' => get_permalink(),
      'variant' => 'outlined',
      'interactive' => true,
  ];
@endphp

<x-m3.card
  :title="$card_data['title']"
  :subtitle="$card_data['subtitle']"
  :image-url="$card_data['imageUrl']"
  :image-alt="$card_data['imageAlt']"
  :href="$card_data['href']"
  :variant="$card_data['variant']"
  :interactive="$card_data['interactive']"
  class="h-full flex flex-col" {{-- Make card flex-col to push actions to bottom --}}
>
  <div class="flex-grow"> {{-- This div will expand, pushing actions down --}}
    {!! get_the_excerpt() !!}
  </div>

  <x-slot name="actions">
    <x-m3.button type="text" href="{{ get_permalink() }}">{{ __('Read More', 'charity-m3') }}</x-m3.button>
  </x-slot>
</x-m3.card>
