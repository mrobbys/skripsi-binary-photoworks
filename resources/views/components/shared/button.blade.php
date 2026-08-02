{{--
  * COMPONENT SHARED BUTTON
  * Digunakan untuk menampilkan tombol yang fleksibel, mendukung tag <button> dan <a>.
  *
  * Props :
  *   `value`       : string (default: null) - Teks utama tombol
  *   `as`          : string (default: 'button') - Tag HTML ('button' atau 'a')
  *   `variant`     : string (default: 'primary') - Preset warna ('primary', 'secondary', 'outline', 'ghost', 'danger', 'custom')
  *   `size`        : string (default: 'md') - Ukuran ('sm', 'md', 'lg', 'icon')
  *   `href`        : string (default: null) - URL jika as='a'
  *   `loading`     : boolean (default: false) - State loading statis (PHP Server-side)
  *   `xLoading`    : string (default: null) - Expression state loading reaktif (Alpine.js Client-side)
  *   `loadingText` : string (default: null) - Teks alternatif saat loading
  *
  * Slot :
  *   `default slot`: Teks utama tombol
  *   `iconLeft`    : Icon di sebelah kiri teks
  *   `iconRight`   : Icon di sebelah kanan teks
--}}

@props([
    'value' => null,
    'as' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'loading' => false,
    'xLoading' => null,
    'loadingText' => null,
])

@php
  $baseClasses =
      'inline-flex items-center justify-center gap-2 cursor-pointer transition-all duration-300 font-semibold focus:outline-none disabled:cursor-not-allowed disabled:opacity-50';

  $sizeClasses =
      [
          'sm' => 'px-3 py-1.5 text-xs',
          'md' => 'px-4 py-2.5 text-sm',
          'lg' => 'px-6 py-3.5 text-base',
          'icon' => 'p-2 text-base',
      ][$size] ?? 'px-4 py-2.5 text-sm';

  $variantClasses =
      [
          'primary' => 'bg-stone-800 text-stone-50 hover:bg-stone-900 border border-transparent',
          'secondary' => 'bg-stone-200 text-stone-800 hover:bg-stone-300 border border-transparent',
          'outline' => 'border border-stone-300 bg-stone-50 text-stone-800 hover:bg-stone-100',
          'ghost' => 'bg-transparent text-stone-700 hover:bg-stone-100 border border-transparent',
          'danger' => 'bg-red-700 text-red-50 hover:bg-red-800 border border-transparent',
          'custom' => '',
      ][$variant] ?? '';
@endphp

@if ($as === 'a')
  <a
    href="{{ $href }}"
    @if ($xLoading) x-bind:class="{{ $xLoading }} ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''" @endif
    {{ $attributes->merge(['class' => "$baseClasses $sizeClasses $variantClasses"]) }}
  >
    @if ($xLoading)
      <template x-if="{{ $xLoading }}">
        <i class="ri-loader-4-line inline-block animate-spin text-lg"></i>
      </template>
    @elseif ($loading)
      <i class="ri-loader-4-line inline-block animate-spin text-lg"></i>
    @endif

    @if (!$loading && isset($iconLeft))
      <span
        class="inline-flex shrink-0"
        @if ($xLoading) x-show="!({{ $xLoading }})" @endif
      >{{ $iconLeft }}</span>
    @endif

    @if ($xLoading && $loadingText)
      <span
        x-text="{{ $xLoading }} ? @js($loadingText) : @js($value ?? $slot)">{{ $value ?? $slot }}</span>
    @else
      <span>{{ $loading && $loadingText ? $loadingText : $value ?? $slot }}</span>
    @endif

    @if (!$loading && isset($iconRight))
      <span
        class="inline-flex shrink-0"
        @if ($xLoading) x-show="!({{ $xLoading }})" @endif
      >{{ $iconRight }}</span>
    @endif
  </a>
@else
  <button
    {{ $loading ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => "$baseClasses $sizeClasses $variantClasses"]) }}
  >
    @if ($xLoading)
      <template x-if="{{ $xLoading }}">
        <i class="ri-loader-4-line inline-block animate-spin text-lg"></i>
      </template>
    @elseif ($loading)
      <i class="ri-loader-4-line inline-block animate-spin text-lg"></i>
    @endif

    @if (!$loading && isset($iconLeft))
      <span
        class="inline-flex shrink-0"
        @if ($xLoading) x-show="!({{ $xLoading }})" @endif
      >{{ $iconLeft }}</span>
    @endif

    @if ($xLoading && $loadingText)
      <span
        x-text="{{ $xLoading }} ? @js($loadingText) : @js($value ?? $slot)">{{ $value ?? $slot }}</span>
    @else
      <span>{{ $loading && $loadingText ? $loadingText : $value ?? $slot }}</span>
    @endif

    @if (!$loading && isset($iconRight))
      <span
        class="inline-flex shrink-0"
        @if ($xLoading) x-show="!({{ $xLoading }})" @endif
      >{{ $iconRight }}</span>
    @endif
  </button>
@endif
