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
      <span
        x-cloak
        x-show="{{ $xLoading }}"
        class="inline-flex shrink-0 items-center justify-center leading-none"
      >
        <svg
          class="h-5 w-5 shrink-0 animate-spin"
          viewBox="0 0 1024 1024"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            fill="currentColor"
            d="M512 64a32 32 0 0132 32v192a32 32 0 01-64 0V96a32 32 0 0132-32zm0 640a32 32 0 0132 32v192a32 32 0 11-64 0V736a32 32 0 0132-32zm448-192a32 32 0 01-32 32H736a32 32 0 110-64h192a32 32 0 0132 32zm-640 0a32 32 0 01-32 32H96a32 32 0 010-64h192a32 32 0 0132 32zM195.2 195.2a32 32 0 0145.248 0L376.32 331.008a32 32 0 01-45.248 45.248L195.2 240.448a32 32 0 010-45.248zm452.544 452.544a32 32 0 0145.248 0L828.8 783.552a32 32 0 01-45.248 45.248L647.744 692.992a32 32 0 010-45.248zM828.8 195.264a32 32 0 010 45.184L692.992 376.32a32 32 0 01-45.248-45.248l135.808-135.808a32 32 0 0145.248 0zm-452.544 452.48a32 32 0 010 45.248L240.448 828.8a32 32 0 01-45.248-45.248l135.808-135.808a32 32 0 0145.248 0z"
          />
        </svg>
      </span>
    @elseif ($loading)
      <span class="inline-flex shrink-0 items-center justify-center leading-none">
        <svg
          class="h-5 w-5 shrink-0 animate-spin"
          viewBox="0 0 1024 1024"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            fill="currentColor"
            d="M512 64a32 32 0 0132 32v192a32 32 0 01-64 0V96a32 32 0 0132-32zm0 640a32 32 0 0132 32v192a32 32 0 11-64 0V736a32 32 0 0132-32zm448-192a32 32 0 01-32 32H736a32 32 0 110-64h192a32 32 0 0132 32zm-640 0a32 32 0 01-32 32H96a32 32 0 010-64h192a32 32 0 0132 32zM195.2 195.2a32 32 0 0145.248 0L376.32 331.008a32 32 0 01-45.248 45.248L195.2 240.448a32 32 0 010-45.248zm452.544 452.544a32 32 0 0145.248 0L828.8 783.552a32 32 0 01-45.248 45.248L647.744 692.992a32 32 0 010-45.248zM828.8 195.264a32 32 0 010 45.184L692.992 376.32a32 32 0 01-45.248-45.248l135.808-135.808a32 32 0 0145.248 0zm-452.544 452.48a32 32 0 010 45.248L240.448 828.8a32 32 0 01-45.248-45.248l135.808-135.808a32 32 0 0145.248 0z"
          />
        </svg>
      </span>
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
      <span
        x-cloak
        x-show="{{ $xLoading }}"
        class="inline-flex shrink-0 items-center justify-center leading-none"
      >
        <svg
          class="h-5 w-5 shrink-0 animate-spin"
          viewBox="0 0 1024 1024"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            fill="currentColor"
            d="M512 64a32 32 0 0132 32v192a32 32 0 01-64 0V96a32 32 0 0132-32zm0 640a32 32 0 0132 32v192a32 32 0 11-64 0V736a32 32 0 0132-32zm448-192a32 32 0 01-32 32H736a32 32 0 110-64h192a32 32 0 0132 32zm-640 0a32 32 0 01-32 32H96a32 32 0 010-64h192a32 32 0 0132 32zM195.2 195.2a32 32 0 0145.248 0L376.32 331.008a32 32 0 01-45.248 45.248L195.2 240.448a32 32 0 010-45.248zm452.544 452.544a32 32 0 0145.248 0L828.8 783.552a32 32 0 01-45.248 45.248L647.744 692.992a32 32 0 010-45.248zM828.8 195.264a32 32 0 010 45.184L692.992 376.32a32 32 0 01-45.248-45.248l135.808-135.808a32 32 0 0145.248 0zm-452.544 452.48a32 32 0 010 45.248L240.448 828.8a32 32 0 01-45.248-45.248l135.808-135.808a32 32 0 0145.248 0z"
          />
        </svg>
      </span>
    @elseif ($loading)
      <span class="inline-flex shrink-0 items-center justify-center leading-none">
        <svg
          class="h-5 w-5 shrink-0 animate-spin"
          viewBox="0 0 1024 1024"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            fill="currentColor"
            d="M512 64a32 32 0 0132 32v192a32 32 0 01-64 0V96a32 32 0 0132-32zm0 640a32 32 0 0132 32v192a32 32 0 11-64 0V736a32 32 0 0132-32zm448-192a32 32 0 01-32 32H736a32 32 0 110-64h192a32 32 0 0132 32zm-640 0a32 32 0 01-32 32H96a32 32 0 010-64h192a32 32 0 0132 32zM195.2 195.2a32 32 0 0145.248 0L376.32 331.008a32 32 0 01-45.248 45.248L195.2 240.448a32 32 0 010-45.248zm452.544 452.544a32 32 0 0145.248 0L828.8 783.552a32 32 0 01-45.248 45.248L647.744 692.992a32 32 0 010-45.248zM828.8 195.264a32 32 0 010 45.184L692.992 376.32a32 32 0 01-45.248-45.248l135.808-135.808a32 32 0 0145.248 0zm-452.544 452.48a32 32 0 010 45.248L240.448 828.8a32 32 0 01-45.248-45.248l135.808-135.808a32 32 0 0145.248 0z"
          />
        </svg>
      </span>
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
