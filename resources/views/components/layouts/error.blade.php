@props([
    'code' => '500',
    'title' => 'Terjadi Kesalahan',
    'description' => 'Terjadi kesalahan sistem yang tidak terduga.',
])

@php
    $targetUrl = route('frontdoor.home');
    $buttonLabel = 'Kembali ke Beranda';

    if (Auth::check() && !Auth::user()->hasRole('user')) {
        $targetUrl = route('backdoor.dashboard.index');
        $buttonLabel = 'Kembali ke Dashboard';
    }
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
  <x-layouts.shared.head :title="$code . ' — ' . $title" />
</head>
<body class="h-full bg-stone-50 font-sans text-stone-900 antialiased selection:bg-stone-700 selection:text-stone-100 flex items-center justify-center p-4 sm:p-6 lg:p-8">
  <main id="main-content" class="w-full max-w-md sm:max-w-lg border border-stone-300 bg-stone-100 p-6 sm:p-10 md:p-12 text-center rounded-none shadow-none">
    {{-- Kode Error Visual (untuk Screen Reader) --}}
    <div class="font-serif text-6xl sm:text-7xl md:text-8xl font-bold text-stone-700 tracking-tight" aria-hidden="true">
      {{ $code }}
    </div>

    {{-- Judul Halaman Utama  --}}
    <h1 class="mt-4 text-lg sm:text-xl md:text-2xl font-bold text-stone-900">
      <span class="sr-only">Error {{ $code }}: </span>{{ $title }}
    </h1>

    {{-- Penjelasan Deskripsi Error --}}
    <p class="mt-3 text-xs sm:text-sm md:text-base text-stone-600 leading-relaxed max-w-prose mx-auto">
      {{ $description }}
    </p>

    {{-- Tombol Navigasi Utama --}}
    <div class="mt-8 flex justify-center">
      <a href="{{ $targetUrl }}"
         class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center border border-stone-700 bg-stone-700 px-6 py-3 text-xs sm:text-sm font-semibold text-stone-50 transition-colors duration-150 hover:bg-stone-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-stone-700 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-100 active:bg-stone-900 rounded-none">
        {{ $buttonLabel }}
      </a>
    </div>
  </main>
</body>
</html>
