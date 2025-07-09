@extends('layouts.app')

@section('content')
  <div class="container mx-auto">
    @if (!have_posts())
      <div class="alert alert-warning">
        {{ __('Sorry, no results were found.', 'charity-m3') }}
      </div>
      {!! get_search_form(false) !!}
    @endif

    @while(have_posts()) @php(the_post())
      @includeFirst(['partials.content-' . get_post_type(), 'partials.content'])
    @endwhile

    {!! get_the_posts_navigation() !!}
  </div>
@endsection
