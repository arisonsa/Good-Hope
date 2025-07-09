@php
  // Map attributes for the correct MWC component tag
  $componentAttributes = $attributes->merge([
      'has-icon' => $icon ? true : null, // md-button specific attribute for spacing if icon is present
      'trailing-icon' => $icon && $trailingIcon ? true : null,
      'href' => $elType === 'a' ? $href : null, // For link buttons
      // Other common attributes like 'disabled', 'type' (for form buttons) can be passed directly
  ]);
@endphp

<{{ $tag }} {!! $componentAttributes !!}>
  @if ($icon && !$trailingIcon)
    <md-icon slot="icon">{{ $icon }}</md-icon>
  @endif
  {{ $slot }}
  @if ($icon && $trailingIcon)
    <md-icon slot="icon">{{ $icon }}</md-icon>
  @endif
</{{ $tag }}>

{{--
Example Usage in another Blade file:

<x-m3.button type="filled" icon="add">
  Create New
</x-m3.button>

<x-m3.button type="outlined" icon="arrow_back" :trailing-icon="false" href="{{ home_url('/') }}">
  Go Home
</x-m3.button>

<x-m3.button type="text" class="custom-class">
  Learn More
</x-m3.button>
--}}
