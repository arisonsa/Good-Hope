@extends('layouts.app')

{{-- Optionally remove default padding from main for truly edge-to-edge content --}}
@section('main-class', 'flex-grow') {{-- Removes py-8, or use 'p-0' or specific padding as needed --}}

@section('content')
  @while(have_posts()) @php(the_post())
    {{-- No container here, content spans full width --}}
    @include('partials.page-header') {{-- Page header might still want a container internally or be full-width --}}
    @include('partials.content-page')
  @endwhile
@endsection
