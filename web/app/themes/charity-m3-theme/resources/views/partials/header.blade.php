<header class="banner bg-surface text-on-surface p-4 shadow-md sticky top-0 z-40"> {{-- Make header sticky --}}
  <div class="container mx-auto flex justify-between items-center">
    <a class="brand text-lg font-semibold z-50" href="{{ home_url('/') }}">
      @php $custom_logo_id = get_theme_mod('custom_logo'); @endphp
      @if ($custom_logo_id)
        {!! wp_get_attachment_image($custom_logo_id, 'full', false, ['class' => 'custom-logo max-h-12 w-auto']) !!}
      @else
        {{ $siteName ?? get_bloginfo('name') }}
      @endif
    </a>

    {{-- Desktop Navigation --}}
    @if (has_nav_menu('primary_navigation'))
      <nav class="nav-primary hidden lg:flex" aria-label="{{ wp_get_nav_menu_name('primary_navigation') }}">
        {!! wp_nav_menu([
            'theme_location' => 'primary_navigation',
            'menu_class' => 'nav flex items-center space-x-6', // M3 style would be more nuanced
            'container' => false,
            'echo' => false
        ]) !!}
      </nav>
    @endif

    {{-- Mobile Navigation Toggle --}}
    <div class="lg:hidden z-50">
      <mobile-nav-toggle></mobile-nav-toggle>
    </div>
  </div>

  {{-- Mobile Menu Container --}}
  @if (has_nav_menu('primary_navigation'))
  <div id="mobile-menu-container" class="mobile-menu-container fixed inset-0 bg-background dark:bg-background-dark z-40 transform -translate-x-full transition-transform duration-300 ease-in-out lg:hidden">
    <div class="p-8">
      <h2 class="md-typescale-title-large mb-8">{{ __('Menu', 'charity-m3') }}</h2>
      <nav class="nav-mobile" aria-label="{{ wp_get_nav_menu_name('primary_navigation') }} - Mobile">
        {!! wp_nav_menu([
            'theme_location' => 'primary_navigation',
            'menu_class' => 'nav flex flex-col space-y-4',
            'container' => false,
            'echo' => false
        ]) !!}
      </nav>
    </div>
  </div>
  @endif
</header>
