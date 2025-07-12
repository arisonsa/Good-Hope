@extends('layouts.app')

@section('content')
  <div class="container mx-auto py-8">
    {{-- Use the existing page-header partial to display the search results title --}}
    @include('partials.page-header')

    @if (!have_posts())
      <div class="alert alert-warning mb-8">
        <p>{{ __('Sorry, no results were found for your search query.', 'charity-m3') }}</p>
      </div>
      <div class="max-w-md">
        <h3 class="md-typescale-title-medium mb-4">{{ __('Try a new search?', 'charity-m3') }}</h3>
        {!! get_search_form(false) !!}
      </div>
    @else
      <x-m3.grid cols="responsive-default" gap="6">
        @while(have_posts()) @php(the_post())
          @include('partials.card-post', ['subtitle_context' => get_post_type() . ' | ' . get_the_date()])
        @endwhile
      </x-m3.grid>

      <div class="mt-8">
        {!! get_the_posts_navigation() !!}
      </div>
    @endif
  </div>
@endsection
