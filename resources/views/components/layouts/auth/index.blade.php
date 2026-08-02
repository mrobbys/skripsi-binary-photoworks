{{-- 
  * COMPONENT LAYOUTS AUTH

  * Digunakan untuk halaman fitur auth

  * Props :
      * `title` : string
        Judul halaman yang akan ditampilkan di title tag
      * `jsModule` : string
        Nama module javascript yang akan digunakan

  * Slot :
      * 'content' : untuk menampilkan konten
      * 'heads'   : untuk menambahkan isi head tambahan
      * 'scripts' : untuk menambahkan script tambahan

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
  <x-layouts.shared.head :title="$title ?? ''" />
  {{ $heads ?? '' }}
</head>

<body data-module="{{ $jsModule ?? '' }}" class="overflow-x-hidden">
  <main class="min-h-dvh w-full mx-auto max-w-7xl flex items-center justify-center p-4 py-8">
    {{ $content ?? '' }}
  </main>

  {{-- trigger alert dan toast berdasarkan session  --}}
  <x-scripts.alert-toast />
  {{-- for javascript --}}
  {{ $scripts ?? '' }}
</body>

</html>
