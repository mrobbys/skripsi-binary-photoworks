@props([
    'title' => '',
    'description' =>
        'Studio fotografi profesional yang melayani dokumentasi momen terbaik Anda dengan kualitas premium dan estetika tinggi.',
    'image' => asset('assets/binary-logo/binary-logo-text-black.png'),
])

@php
  $pageTitle = $title
      ? $title . ' | ' . config('app.name', 'Binary Photoworks')
      : config('app.name', 'Binary Photoworks');
  $currentUrl = url()->current();
@endphp

<meta charset="UTF-8">
<meta
  name="viewport"
  content="width=device-width, initial-scale=1.0"
>
<meta
  name="csrf-token"
  content="{{ csrf_token() }}"
>
<meta
  http-equiv="X-UA-Compatible"
  content="ie=edge"
>
<meta
  name="theme-color"
  content="#1c1917"
>

{{-- Basic SEO --}}
<title>{{ $pageTitle }}</title>
<meta
  name="description"
  content="{{ $description }}"
>
<link
  rel="canonical"
  href="{{ $currentUrl }}"
>

{{-- Open Graph (WhatsApp, Facebook, LinkedIn) --}}
<meta
  property="og:type"
  content="website"
>
<meta
  property="og:url"
  content="{{ $currentUrl }}"
>
<meta
  property="og:title"
  content="{{ $pageTitle }}"
>
<meta
  property="og:description"
  content="{{ $description }}"
>
<meta
  property="og:image"
  content="{{ $image }}"
>

{{-- Favicon --}}
<link
  rel="icon"
  href="{{ asset('assets/binary-logo/binary-logo-white.png') }}"
  type="image/png"
/>

{{-- Fonts --}}
<link
  rel="preconnect"
  href="https://fonts.googleapis.com"
>
<link
  rel="preconnect"
  href="https://fonts.gstatic.com"
  crossorigin
>
<link
  href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Libre+Baskerville:ital,wght@0,400..700;1,400..700&display=swap"
  rel="stylesheet"
  crossorigin
>

@vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
  [x-cloak] {
    display: none !important;
  }
</style>
