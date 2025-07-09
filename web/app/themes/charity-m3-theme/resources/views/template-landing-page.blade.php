@extends('layouts.app')

{{-- Landing pages often have no default padding on main content area --}}
@section('main-class', 'flex-grow')

@section('content')
  @while(have_posts()) @php(the_post())
    {{-- Landing pages typically don't have a global page header; content is built with sections/blocks --}}
    {{-- @include('partials.page-header') --}}

    {{-- Content is expected to be composed of full-width sections or blocks --}}
    @include('partials.content-page')
  @endwhile
@endsection

{{--
  Future enhancements for landing page template:
  - Option to hide theme header/footer (via Customizer or page meta)
  - If header/footer are hidden, ensure essential elements like body_class() and wp_head/footer are still present.
  - This could involve a different layout, e.g., @extends('layouts.minimal')
--}}
