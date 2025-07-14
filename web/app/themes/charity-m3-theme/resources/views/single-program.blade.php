@extends('layouts.app')

{{-- Use the full-width layout by removing default padding on main --}}
@section('main-class', 'flex-grow')

@section('content')
  @while(have_posts()) @php(the_post())
    {{-- Use the Hero component for the page header with the featured image --}}
    <x-m3.hero
      :title="get_the_title()"
      :background-image="has_post_thumbnail() ? get_the_post_thumbnail_url(null, 'hero-large') : \App\Vite::uri('resources/images/placeholders/placeholder-16x9.jpg')"
      :show-overlay="true"
      text-color="var(--md-sys-color-on-primary)"
      min-height="40vh"
      text-alignment="text-left"
      content-width="wide"
    >
      {{-- You can add a subtitle from a custom field if it exists --}}
      {{-- <p class="md-typescale-headline-small">{{ get_post_meta(get_the_ID(), '_program_subtitle', true) }}</p> --}}
    </x-m3.hero>

    {{-- Main content area --}}
    <div class="container mx-auto py-12 md:py-16">
      <article @php(post_class('content-area'))> {{-- Removed prose classes --}}
        @php(the_content())

        {{-- Example of displaying custom fields if they were added --}}
        {{--
        <div class="program-meta mt-8">
            <h3 class="md-typescale-title-large">Program Details</h3>
            <ul>
                <li><strong>Status:</strong> {{ get_post_meta(get_the_ID(), '_program_status', true) }}</li>
                <li><strong>Location:</strong> {{ get_post_meta(get_the_ID(), '_program_location', true) }}</li>
            </ul>
        </div>
        --}}
      </article>
    </div>
  @endwhile
@endsection
