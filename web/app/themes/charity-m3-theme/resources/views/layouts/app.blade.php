<!doctype html>
<html @php(language_attributes()) @php(body_class())>
<head>
  <meta charset="{{ get_bloginfo('charset') }}">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @php(wp_head())
  @stack('page-styles')
</head>
<body @php(body_class())>
  <a class="sr-only focus:not-sr-only" href="#main">
    {{ __('Skip to content') }}
  </a>

  @php(wp_body_open())

  <div id="app" class="flex flex-col min-h-screen">
    @include('partials.header') {{-- Consider making header/footer more configurable via props or slots for landing pages --}}

    <main id="main" class="main @yield('main-class', 'py-8') flex-grow"> {{-- Allow overriding main class --}}
      @yield('content')
    </main>

    @include('partials.footer')
  </div>

  @php(wp_footer())
  @stack('page-scripts')
</body>
</html>
