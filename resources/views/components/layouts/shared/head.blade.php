@props([
    'title' => '',
])

<meta charset="UTF-8">
<meta
  name="viewport"
  content="width=device-width, initial-scale=1.0">
<meta
  name="csrf-token"
  content="{{ csrf_token() }}">
<meta
  http-equiv="X-UA-Compatible"
  content="ie=edge">

{{-- TODO: buat logic ketika mode dark pakai yang 'binary-logo-white.png', jika tidak pakai 'binary-logo-black.png' --}}
<link
  rel="icon"
  href="{{ asset('assets/binary-logo/binary-logo-white.png') }}"
  type="image/png" />

{{-- fonts --}}
<link
  rel="preconnect"
  href="https://fonts.googleapis.com">
<link
  rel="preconnect"
  href="https://fonts.gstatic.com"
  crossorigin>
<link
  href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Libre+Baskerville:ital,wght@0,400..700;1,400..700&display=swap"
  rel="stylesheet">

<title>
  @isset($title)
    {{ $title }} |
  @endisset
  {{ config('app.name', 'Laravel') }}
</title>

@vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
  [x-cloak] {
    display: none !important;
  }
</style>
