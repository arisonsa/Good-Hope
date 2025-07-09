<!doctype html>
<html @php(language_attributes()) @php(body_class())>
<head>
  <meta charset="{{ get_bloginfo('charset') }}">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @php(wp_head())
</head>
<body>
  @php(wp_body_open())

  <div id="app" class="flex flex-col min-h-screen">
    @include('partials.header')

    <main id="main" class="main py-8 flex-grow">
      @yield('content')
    </main>

    @include('partials.footer')
  </div>

  @php(wp_footer())
</body>
</html>
