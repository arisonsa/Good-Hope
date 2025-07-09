<header class="banner bg-surface text-on-surface p-4 shadow-md"> {{-- M3 surface color and shadow --}}
  <div class="container mx-auto flex justify-between items-center">
    <a class="brand text-lg font-semibold" href="{{ home_url('/') }}">
      @php $custom_logo_id = get_theme_mod('custom_logo'); @endphp
      @if ($custom_logo_id)
        {!! wp_get_attachment_image($custom_logo_id, 'full', false, ['class' => 'custom-logo max-h-12 w-auto']) !!}
      @else
        {{ $siteName ?? get_bloginfo('name') }}
      @endif
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
