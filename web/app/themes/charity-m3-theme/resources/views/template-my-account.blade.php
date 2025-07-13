@extends('layouts.app')

@section('content')
  <div class="container mx-auto py-12 md:py-16">
    @while(have_posts()) @php(the_post())
      @include('partials.page-header')

      <div class="prose lg:prose-xl max-w-none">
        @php(the_content())
      </div>

      <div class="my-account-content mt-8">
        @if (is_user_logged_in())
          @php
            $user = wp_get_current_user();
            $stripe_customer_id = get_user_meta($user->ID, 'stripe_customer_id', true);
          @endphp

          <h2 class="md-typescale-headline-medium mb-4">{{ __('Welcome back,', 'charity-m3') }} {{ $user->display_name }}!</h2>

          <p class="md-typescale-body-large">{{ __('Thank you for your support. You can manage your donations, update payment methods, and view your contribution history through our secure portal.', 'charity-m3') }}</p>

          @if ($stripe_customer_id)
            <div class="mt-8">
              {{-- This form will submit to a page handler that generates the portal link and redirects --}}
              <form method="POST" action="{{ esc_url(admin_url('admin-post.php')) }}">
                <input type="hidden" name="action" value="charity_m3_create_customer_portal_session">
                @php(wp_nonce_field('create_customer_portal_session_nonce', '_wpnonce_customer_portal'))

                <button type="submit" class="charity-m3-portal-button">
                    <x-m3.button type="filled" class="w-full md:w-auto">
                        {{ __('Manage My Donations', 'charity-m3') }}
                    </x-m3.button>
                </button>
                <style>.charity-m3-portal-button { background: none; border: none; padding: 0; cursor: pointer; }</style>
              </form>
            </div>
          @else
            <p class="mt-8 md-typescale-body-medium">{{ __('We could not find any active recurring donations associated with this account.', 'charity-m3') }}</p>
          @endif
        @else
          <div class="p-8 border border-outline rounded-lg">
            <h3 class="md-typescale-title-large mb-4">{{ __('Please Log In', 'charity-m3') }}</h3>
            <p class="md-typescale-body-medium mb-4">{{ __('You must be logged in to manage your donations.', 'charity-m3') }}</p>
            {{-- You could include a login form here using wp_login_form() or a custom one --}}
            <x-m3.button type="filled" href="{{ esc_url(wp_login_url(get_permalink())) }}">
                {{ __('Log In', 'charity-m3') }}
            </x-m3.button>
          </div>
        @endif
      </div>
    @endwhile
  </div>
@endsection
