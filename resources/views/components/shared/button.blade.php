{{--
  * COMPONENT SHARED BUTTON

  * Digunakan untuk menampilkan tombol yang fleksibel, mendukung tag <button> dan <a>, 
  * Pewarnaan dilakukan manual, disetiap pemanggilan komponen..

  * Props :
      * `value` : string (default: null)
        Teks utama tombol (jika menggunakan self-closing tag)
      * `as` : string (default: 'button')
        Menentukan tag HTML yang akan dirender ('button' atau 'a')
      * `size` : string (default: 'md')
        Menentukan ukuran tombol ('sm', 'md', 'lg', 'icon')
      * `href` : string (default: null)
        URL tujuan jika prop `as` diset menjadi 'a'
      
  * Slot :
      * `default slot` : untuk menampilkan teks utama tombol (jika tidak menggunakan prop `value`)
      * `iconLeft`     : untuk menampilkan icon di sebelah kiri teks
      * `iconRight`    : untuk menampilkan icon di sebelah kanan teks
--}}

@props([
    'value' => null,
    'as' => 'button',
    'size' => 'md',
    'href' => null,
])

@php
  $baseClasses =
      'inline-flex items-center justify-center cursor-pointer transition-colors duration-300 disabled:cursor-not-allowed font-medium focus:outline-none disabled:opacity-50';

  $sizeClasses =
      [
          'sm' => 'px-3 py-1.5 text-sm',
          'md' => 'px-4 py-2 text-base',
          'lg' => 'px-6 py-3 text-lg',
          'icon' => 'p-2',
      ][$size] ?? 'px-4 py-2 text-base';
@endphp

@if ($as === 'a')
  <a href="{{ $href }}" {{ $attributes->merge(['class' => "$baseClasses $sizeClasses"]) }}>
    @if (isset($iconLeft))
      <span class="mr-2 inline-flex shrink-0">{{ $iconLeft }}</span>
    @endif

    {{ $value ?? $slot }}

    @if (isset($iconRight))
      <span class="ml-2 inline-flex shrink-0">{{ $iconRight }}</span>
    @endif
  </a>
@else
  <button {{ $attributes->merge(['class' => "$baseClasses $sizeClasses"]) }}>
    @if (isset($iconLeft))
      <span class="mr-2 inline-flex shrink-0">{{ $iconLeft }}</span>
    @endif

    {{ $value ?? $slot }}

    @if (isset($iconRight))
      <span class="ml-2 inline-flex shrink-0">{{ $iconRight }}</span>
    @endif
  </button>
@endif
