{{--
  * COMPONENT SHARED BUTTON

  * Digunakan untuk menampilkan tombol yang fleksibel, mendukung tag <button> dan <a>, 
  * serta memiliki berbagai varian warna dan slot untuk icon.

  * Props :
      * `value` : string (default: null)
        Teks utama tombol (jika menggunakan self-closing tag)
      * `as` : string (default: 'button')
        Menentukan tag HTML yang akan dirender ('button' atau 'a')
      * `variant` : string (default: 'primary')
        Menentukan gaya tombol ('primary', 'secondary', 'outline', 'ghost', 'danger')
      * `size` : string (default: 'md')
        Menentukan ukuran tombol ('sm', 'md', 'lg', 'icon')
      * `href` : string (default: null)
        URL tujuan jika prop `as` diset menjadi 'a'
      
  * Slot :
      * `default slot` : untuk menampilkan teks utama tombol (jika tidak menggunakan prop `value`)
      * `iconLeft`     : untuk menampilkan icon di sebelah kiri teks
      * `iconRight`    : untuk menampilkan icon di sebelah kanan teks

  * Penggunaan : 
      -- Self-closing tag (teks saja):
      <x-shared.button value="Masuk" variant="outline" as="a" href="/login" />

      -- Dengan icon (harus menggunakan tag pembuka dan penutup):
      <x-shared.button variant="primary">
        <x-slot:iconLeft>
          <i class="fas fa-plus"></i>
        </x-slot:iconLeft>
        Tambah Kategori
      </x-shared.button>
--}}

@props([
    'value' => null,
    'as' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
])

@php
  $baseClasses =
      'inline-flex items-center justify-center cursor-pointer disabled:cursor-not-allowed font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none';

  $sizeClasses =
      [
          'sm' => 'px-3 py-1.5 text-sm',
          'md' => 'px-4 py-2 text-base',
          'lg' => 'px-6 py-3 text-lg',
          'icon' => 'p-2',
      ][$size] ?? 'px-4 py-2 text-base';

  $variantClasses =
      [
          'primary' => 'bg-stone-800 text-white hover:bg-stone-700 focus:ring-stone-800 focus:ring-offset-white',

          'secondary' => 'bg-stone-500 text-white hover:bg-stone-600 focus:ring-stone-500 focus:ring-offset-white',

          'outline' =>
              'border border-stone-300 bg-transparent text-stone-800 hover:bg-stone-50 focus:ring-stone-300 focus:ring-offset-white',

          'ghost' => 'text-stone-800 hover:bg-stone-100 focus:ring-stone-200 focus:ring-offset-white',

          'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-600 focus:ring-offset-white',

          'danger-ghost' => 'text-red-600 hover:bg-red-50 focus:ring-red-200 focus:ring-offset-white',
      ][$variant] ?? 'bg-stone-800 text-white hover:bg-stone-700 focus:ring-stone-800 focus:ring-offset-white';
@endphp

@if ($as === 'a')
  <a href="{{ $href }}" {{ $attributes->merge(['class' => "$baseClasses $sizeClasses $variantClasses"]) }}>
    @if (isset($iconLeft))
      <span class="mr-2 inline-flex shrink-0">{{ $iconLeft }}</span>
    @endif

    {{ $value ?? $slot }}

    @if (isset($iconRight))
      <span class="ml-2 inline-flex shrink-0">{{ $iconRight }}</span>
    @endif
  </a>
@else
  <button {{ $attributes->merge(['class' => "$baseClasses $sizeClasses $variantClasses"]) }}>
    @if (isset($iconLeft))
      <span class="mr-2 inline-flex shrink-0">{{ $iconLeft }}</span>
    @endif

    {{ $value ?? $slot }}

    @if (isset($iconRight))
      <span class="ml-2 inline-flex shrink-0">{{ $iconRight }}</span>
    @endif
  </button>
@endif
