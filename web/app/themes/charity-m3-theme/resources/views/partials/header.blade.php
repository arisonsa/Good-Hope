<header class="banner bg-gray-200 p-4">
  <div class="container mx-auto">
    <a class="brand" href="{{ home_url('/') }}">
      {{ $siteName ?? get_bloginfo('name') }}
    </a>

    @if (has_nav_menu('primary_navigation'))
      <nav class="nav-primary" aria-label="{{ wp_get_nav_menu_name('primary_navigation') }}">
        {!! wp_nav_menu([
            'theme_location' => 'primary_navigation',
            'menu_class' => 'nav flex space-x-4', // Basic styling, M3 will enhance this
            'echo' => false
        ]) !!}
      </nav>
    @endif
  </div>
</header>
