{{--
  * COMPONENT LAYOUTS FRONTDOOR

  * Digunakan pada bagian frontdoor

  * Props :
      * `title` : string
        Judul halaman yang akan ditampilkan di title tag
      * `jsModule` : string
        Nama module javascript yang akan digunakan
      
  * Slot :
      * `content` : untuk menampilkan konten
      * `heads`   : untuk menambahkan isi head tambahan
      * `scripts` : untuk menambahkan script tambahan

  * Catatan : 
      * 'title' : diteruskan ke komponen @component('layouts.components.head')
--}}

@props([
    'title' => '',
    'jsModule' => '',
])

<!DOCTYPE html>
<html lang="id">

<head>
  <x-layouts.shared.head :title="$title" />

  {{ $heads ?? '' }}
</head>

<body
  data-module="{{ $jsModule ?? '' }}"
  class="overflow-x-hidden"
>

  <x-layouts.frontdoor.components.navbar>
    <x-layouts.frontdoor.components.brand-logo />
    <x-layouts.frontdoor.components.desktop-menu />
    {{-- login button for desktop start --}}
    <x-layouts.frontdoor.components.auth-button />
    {{-- login button for desktop end --}}

    <x-layouts.frontdoor.components.mobile-menu-button />
    <x-layouts.frontdoor.components.mobile-menu />
  </x-layouts.frontdoor.components.navbar>

  <main class="mx-auto min-h-dvh w-full max-w-7xl px-6 py-4">
    {{ $content ?? '' }}
  </main>

  {{-- trigger alert dan toast berdasarkan session  --}}
  <x-scripts.alert-toast />
  {{-- for javascript --}}
  {{ $scripts ?? '' }}
</body>

</html>
