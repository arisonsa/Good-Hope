{{--
  Template Name: Component Showcase
--}}

@extends('layouts.app')

@section('content')
  @php
    // Sample data for components
    $heroButtons = [
        ['text' => __('Donate Now', 'charity-m3'), 'href' => '#donate-showcase', 'type' => 'filled', 'icon' => 'favorite'],
        ['text' => __('Learn More', 'charity-m3'), 'href' => '#learn-more-showcase', 'type' => 'outlined']
    ];

    $cardItems = [
        [
            'title' => __('Our Mission', 'charity-m3'),
            'subtitle' => __('What We Do', 'charity-m3'),
            'imageUrl' => \App\Vite::uri('resources/images/placeholders/placeholder-16x9.jpg'),
            'text' => __('Dedicated to providing aid and relief to communities in need worldwide through sustainable programs.', 'charity-m3'),
            'actions' => [['text' => __('Read More', 'charity-m3'), 'href' => '#mission-detail', 'type' => 'text']]
        ],
        [
            'title' => __('Recent Projects', 'charity-m3'),
            'subtitle' => __('Impact Stories', 'charity-m3'),
            'imageUrl' => \App\Vite::uri('resources/images/placeholders/placeholder-16x9.jpg'),
            'text' => __('Discover the real impact of our recent projects and the lives we\'ve touched together with your support.', 'charity-m3'),
            'actions' => [['text' => __('View Projects', 'charity-m3'), 'href' => '#projects-detail', 'type' => 'text']]
        ],
        [
            'title' => __('Get Involved', 'charity-m3'),
            'subtitle' => __('Join Our Cause', 'charity-m3'),
            'imageUrl' => \App\Vite::uri('resources/images/placeholders/placeholder-16x9.jpg'),
            'text' => __('There are many ways you can contribute to our cause, from volunteering to donations and advocacy.', 'charity-m3'),
            'actions' => [['text' => __('Ways to Help', 'charity-m3'), 'href' => '#involved-detail', 'type' => 'text']]
        ]
    ];

    $ctaButtons = [
        ['text' => __('Support Our Work', 'charity-m3'), 'href' => '#support-work', 'type' => 'filled', 'icon' => 'volunteer_activism'],
        ['text' => __('Become a Partner', 'charity-m3'), 'href' => '#partner', 'type' => 'outlined']
    ];
  @endphp

  {{-- Showcase Hero Component --}}
  <x-m3.hero
      title="{{ __('Empowering Communities, Changing Lives', 'charity-m3') }}"
      subtitle="{{ __('Join us in our mission to bring hope and sustainable solutions to those who need it most. Your support makes a world of difference.', 'charity-m3') }}"
      background-image="{{ \App\Vite::uri('resources/images/placeholders/placeholder-16x9.jpg') }}"
      :show-overlay="true"
      text-color="var(--md-sys-color-on-primary)" {{-- Assuming dark image, light text --}}
      :buttons="$heroButtons"
      content-width="wide"
      text-alignment="text-center"
      min-height="70vh"
  />

  <div class="container mx-auto py-12 md:py-16">
    {{-- Showcase Grid of Cards --}}
    <x-m3.section-header
        title="{{ __('Highlights & Stories', 'charity-m3') }}"
        subtitle="{{ __('See how we make a difference every day.', 'charity-m3') }}"
        class="mb-8 text-center"
    />
    <x-m3.grid cols="responsive-default" gap="6">
      @foreach ($cardItems as $item)
        <x-m3.card
            :title="$item['title']"
            :subtitle="$item['subtitle']"
            :image-url="$item['imageUrl']"
            :href="$item['actions'][0]['href'] ?? '#'" {{-- Make card itself clickable to first action --}}
            variant="elevated"
            :interactive="true"
        >
          <p>{{ $item['text'] }}</p>
          <x-slot name="actions">
            @foreach ($item['actions'] as $action)
              <x-m3.button :type="$action['type']" :href="$action['href']">{{ $action['text'] }}</x-m3.button>
            @endforeach
          </x-slot>
        </x-m3.card>
      @endforeach
    </x-m3.grid>

    {{-- Showcase Newsletter Signup (using the block's render for now) --}}
    <div class="my-16 p-8 bg-surface-container rounded-xl text-center">
        @if (is_active_sidebar('sidebar-newsletter')) {{-- Example if using a widget area --}}
            @php dynamic_sidebar('sidebar-newsletter'); @endphp
        @else
            {{-- Manually render the block if its attributes are simple or fixed for showcase --}}
            @php
                // This assumes NewsletterSignupBlock is registered and available.
                // For a showcase, directly outputting its web component might be cleaner
                // if block context isn't easily mockable here.
                // echo do_blocks('<!-- wp:charity-m3/newsletter-signup {"title":"Stay Updated","description":"Get our latest news delivered to your inbox."} /-->');

                // Or, directly use the web component if attributes are known:
            @endphp
            <h3 class="md-typescale-headline-small mb-2">{{ __('Stay Updated', 'charity-m3')}}</h3>
            <p class="md-typescale-body-medium mb-4">{{__('Get our latest news delivered to your inbox.', 'charity-m3')}}</p>
            <div class="max-w-md mx-auto">
                <newsletter-signup-form
                    email-placeholder="{{ __('Enter your email', 'charity-m3') }}"
                    button-text="{{ __('Subscribe', 'charity-m3') }}"
                    form-action="{{ esc_url(add_query_arg(null, null)) }}"
                    nonce-value="{{ wp_create_nonce('charity_m3_subscribe_nonce_block') }}"
                    nonce-name="_wpnonce_newsletter_signup_block"
                    show-name-field {{-- Example: enable name field --}}
                    name-placeholder="{{ __('Your Name (Optional)', 'charity-m3') }}"
                ></newsletter-signup-form>
            </div>
        @endif
    </div>

  </div> {{-- End container --}}

  {{-- Showcase CTA Banner Component --}}
  <x-m3.cta-banner
      title="{{ __('Ready to Make an Impact?', 'charity-m3') }}"
      text="{{ __('Every contribution, big or small, helps us continue our vital work. Partner with us or make a donation today.', 'charity-m3') }}"
      :buttons="$ctaButtons"
      background-color="var(--md-sys-color-tertiary-container)"
      text-color="var(--md-sys-color-on-tertiary-container)"
      padding="4rem 1rem"
  />

  {{-- Placeholder for other components like FeatureList, Testimonial if they were built --}}

@endsection

{{-- Helper Blade component for section headers (optional, could be part of a layout or other components) --}}
@php
  if (!function_exists('render_section_header_component')) {
    \Illuminate\Support\Facades\Blade::component('m3.section-header', \App\View\Components\M3\SectionHeader::class);
  }
@endphp
{{-- Assume SectionHeader component would be:
app/View/Components/M3/SectionHeader.php
resources/views/components/m3/section-header.blade.php (renders h2 for title, p for subtitle)
--}}
