@extends('layouts.app')

@section('content')
  <div class="container mx-auto py-8">
    {{-- Use the existing page-header partial to display the archive title --}}
    @include('partials.page-header')

    @if (!have_posts())
      <div class="alert alert-warning">
        {{ __('Sorry, no results were found.', 'charity-m3') }}
      </div>
      {!! get_search_form(false) !!}
    @else
      <x-m3.grid cols="responsive-default" gap="6">
        @while(have_posts()) @php(the_post())
          @include('partials.card-post', ['subtitle_context' => get_the_date()])
        @endwhile
      </x-m3.grid>

      <div class="mt-8">
        {!! get_the_posts_navigation() !!}
      </div>
    @endif
  </div>
@endsection
