<header class="banner">
  <div class="banner-container">
    <a class="brand" href="{{ home_url('/') }}">
      @php $custom_logo_id = get_theme_mod('custom_logo'); @endphp
      @if ($custom_logo_id)
        {!! wp_get_attachment_image($custom_logo_id, 'full', false, ['class' => 'custom-logo']) !!}
      @else
        {{ $siteName ?? get_bloginfo('name') }}
      @endif
    </a>

    {{-- Desktop Navigation --}}
    @if (has_nav_menu('primary_navigation'))
      <nav class="nav-primary" aria-label="{{ wp_get_nav_menu_name('primary_navigation') }}">
        {!! wp_nav_menu([
            'theme_location' => 'primary_navigation',
            'menu_class' => 'nav-desktop',
            'container' => false,
            'echo' => false
        ]) !!}
      </nav>
    @endif

    {{-- Mobile Navigation Toggle --}}
    <div class="mobile-nav-toggle-wrapper">
      <mobile-nav-toggle></mobile-nav-toggle>
    </div>
  </div>

  {{-- Mobile Menu Container --}}
  @if (has_nav_menu('primary_navigation'))
  <div id="mobile-menu-container" class="mobile-menu-container">
    <div class="mobile-menu-inner">
      <h2 class="mobile-menu-title">{{ __('Menu', 'charity-m3') }}</h2>
      <nav class="nav-mobile" aria-label="{{ wp_get_nav_menu_name('primary_navigation') }} - Mobile">
        {!! wp_nav_menu([
            'theme_location' => 'primary_navigation',
            'menu_class' => 'nav-mobile-list',
            'container' => false,
            'echo' => false
        ]) !!}
      </nav>
    </div>
  </div>
  @endif
</header>
