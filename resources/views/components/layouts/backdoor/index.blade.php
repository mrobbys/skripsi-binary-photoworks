{{--
  * COMPONENT LAYOUTS BACKDOOR

  * Digunakan pada bagian backdor / dashboard admin

  * Props :
      * `title` : string
        Judul halaman yang akan ditampilkan di title tag
      * `jsModule` : string
        Nama module javascript yang akan digunakan
      * `breadcrumbs` : array
        Array berisi daftar navigasi breadcrumb
      
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
    'breadcrumbs' => [['label' => 'Dashboard', 'url' => '#']],
])

<!DOCTYPE html>
<html lang="id">

<head>
  {{-- meta tag dan favicon --}}
  <x-layouts.shared.head :title="$title ?? ''" />

  {{-- isi head tambahan --}}
  {{ $heads ?? '' }}
</head>

<body
  data-module="{{ $jsModule ?? '' }}"
  class="overflow-x-hidden"
>
  <div
    x-data="{ sidebarIsOpen: false }"
    x-on:keydown.escape.window="sidebarIsOpen = false"
    class="relative flex w-full flex-col md:flex-row"
  >
    {{-- screen reader skip link start --}}
    <x-layouts.backdoor.components.skip-link />
    {{-- screen reader skip link end --}}

    {{-- dark overlay berfungsi ketika sidebar di layar kecil terbuka start --}}
    <x-layouts.backdoor.components.sidebar-overlay />
    {{-- dark overlay berfungsi ketika sidebar di layar kecil terbuka end --}}

    {{-- sidebar container start --}}
    <x-layouts.backdoor.components.sidebar>

      {{-- sidebar links start --}}
      <x-layouts.backdoor.components.sidebar-links />
      {{-- sidebar links end --}}
    </x-layouts.backdoor.components.sidebar>
    {{-- sidebar container end --}}

    {{-- main container start --}}
    <x-layouts.backdoor.components.main-container>
      {{-- top header start --}}
      <x-layouts.backdoor.components.top-header>
        {{-- breadcrumbs start --}}
        <x-layouts.backdoor.components.breadcrumbs :items="$breadcrumbs" />
        {{-- breadcrumbs end --}}
      </x-layouts.backdoor.components.top-header>
      {{-- top header end --}}

      {{-- main content start --}}
      <x-layouts.backdoor.components.main-content>
        {{ $content ?? '' }}
      </x-layouts.backdoor.components.main-content>
      {{-- main content end --}}

    </x-layouts.backdoor.components.main-container>
    {{-- main container end --}}
  </div>
  {{-- trigger alert dan toast berdasarkan session  --}}
  <x-scripts.alert-toast />

  {{-- for javascript --}}
  {{ $scripts ?? '' }}
</body>

</html>
