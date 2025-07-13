@extends('layouts.app')

@section('main-class', 'flex-grow')

@section('content')
  @while(have_posts()) @php(the_post())
    <x-m3.hero
      :title="get_the_title()"
      :background-image="has_post_thumbnail() ? get_the_post_thumbnail_url(null, 'large') : 'https://picsum.photos/seed/' . get_the_ID() . '/1920/1080'"
      :show-overlay="true"
      text-color="var(--md-sys-color-on-primary)"
      min-height="50vh"
      text-alignment="text-center"
      content-width="narrow"
    >
      {{-- Could display a key quote from the story as a subtitle here --}}
      @if(get_post_meta(get_the_ID(), '_story_quote', true))
        <blockquote class="md-typescale-headline-small border-l-4 border-on-primary pl-4 italic">
            {{ get_post_meta(get_the_ID(), '_story_quote', true) }}
        </blockquote>
      @endif
    </x-m3.hero>

    <div class="container mx-auto py-12 md:py-16">
      <article @php(post_class('content-area'))> {{-- Removed prose classes --}}
        @php(the_content())
      </article>
    </div>
  @endwhile
@endsection
