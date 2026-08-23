@props([
    'class' => 'mb-8',
])

<div class="{{ $class }}">
  <a
    href="{{ route('frontdoor.home') }}"
    class="inline-block transition-transform duration-300 hover:scale-105"
  >
    <img
      src="{{ asset('assets/binary-logo/binary-logo-text-black.png') }}"
      alt="Binary Photoworks Logo"
      class="h-12 w-auto object-contain"
    >
  </a>
</div>
