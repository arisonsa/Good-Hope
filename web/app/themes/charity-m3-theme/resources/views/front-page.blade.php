@extends('layouts.app')

{{-- Homepage often has no default padding on main content area --}}
@section('main-class', 'flex-grow')

@section('content')
  @php
    // Check for the active crisis alert from the Customizer
    $active_alert_id = get_theme_mod('homepage_active_alert', 'none');
  @endphp

  @if ($active_alert_id && $active_alert_id !== 'none' && get_post_status($active_alert_id) === 'publish')
    @php
      // An alert is selected, fetch its data
      $alert_post = get_post($active_alert_id);
      $alert_title = $alert_post->post_title;
      $alert_subtitle = apply_filters('the_content', $alert_post->post_content); // Use editor content as subtitle
      $alert_button_text = get_post_meta($alert_post->ID, '_alert_button_text', true) ?: __('Donate Now', 'charity-m3');
      $alert_button_href = get_post_meta($alert_post->ID, '_alert_link_url', true) ?: '#donate';

      // Use a more urgent color scheme for the alert
      $alert_bg_color = 'var(--md-sys-color-error-container)';
      $alert_text_color = 'var(--md-sys-color-on-error-container)';
    @endphp

    {{-- Render the Hero component with the Alert data --}}
    <x-m3.hero
      :title="$alert_title"
      :subtitle="$alert_subtitle"
      :background-color="$alert_bg_color"
      :text-color="$alert_text_color"
      :show-overlay="false"
      :buttons="[
          ['text' => $alert_button_text, 'href' => $alert_button_href, 'type' => 'filled'],
      ]"
      content-width="wide"
      text-alignment="text-center"
      min-height="40vh"
    />
  @else
    {{-- No active alert, render the default homepage hero --}}
    {{-- This could come from page meta fields or a default configuration --}}
    <x-m3.hero
        title="{{ __('Empowering Communities, Changing Lives', 'charity-m3') }}"
        subtitle="{{ __('Join us in our mission to bring hope and sustainable solutions to those who need it most.', 'charity-m3') }}"
        background-image="https://picsum.photos/seed/default-hero/1920/1080"
        :show-overlay="true"
        text-color="var(--md-sys-color-on-primary)"
        :buttons="[
            ['text' => __('Donate Now', 'charity-m3'), 'href' => '#donate', 'type' => 'filled', 'icon' => 'favorite'],
            ['text' => __('Our Programs', 'charity-m3'), 'href' => '/programs', 'type' => 'outlined']
        ]"
        content-width="wide"
        text-alignment="text-center"
        min-height="70vh"
    />
  @endif

  {{-- The rest of the homepage content --}}
  {{-- This could be a flexible content area built with other Gutenberg blocks --}}
  <div class="container mx-auto py-16">
    @while(have_posts()) @php(the_post())
        <div class="content-area">
            @php(the_content())
        </div>
    @endwhile
  </div>

@endsection
