{{--
   * COMPONENT LAYOUTS BACKDOOR

   * Digunakan pada bagian backdor / dashboard admin

   * Props:
      * `title` : string
        Judul halaman yang akan ditampilkan di title tag
      * `jsModule` : string
        Nama module javascript yang akan digunakan

   * Slot:
      * `content` : untuk menampilkan konten
      * `heads`   : untuk menambahkan isi head tambahan
      * `scripts` : untuk menambahkan script tambahan
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
  <x-layouts.components.head />

  {{-- judul halaman --}}
  <title>
    @isset($title)
      {{ $title }} |
    @endisset
    {{ config('app.name', 'Laravel') }}
  </title>

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
    <a
      class="sr-only"
      href="#main-content"
    >
      Lompati ke konten utama
    </a>
    {{-- screen reader skip link end --}}

    {{-- dark overlay berfungsi ketika sidebar di layar kecil terbuka start --}}
    <div
      x-cloak
      x-show="sidebarIsOpen"
      class="fixed inset-0 z-20 bg-neutral-950/10 backdrop-blur-xs md:hidden"
      aria-hidden="true"
      x-on:click="sidebarIsOpen = false"
      x-transition.opacity
    >
    </div>
    {{-- dark overlay berfungsi ketika sidebar di layar kecil terbuka end --}}

    {{-- sidebar navigation container start --}}
    <aside
      id="sidebar-navigation"
      x-cloak
      class="fixed left-0 z-30 flex h-dvh w-60 shrink-0 flex-col border-r border-neutral-300 bg-stone-700 p-4 transition-transform duration-300 md:w-64 md:translate-x-0 md:relative space-y-12"
      x-bind:class="sidebarIsOpen ? 'translate-x-0' : '-translate-x-60'"
      aria-label="Sidebar Menu"
    >
      {{-- close button for mobile screens start --}}
      <button
        type="button"
        class="md:hidden self-end p-2 text-neutral-600 overflow-hidden focus-visible:outline focus-visible:outline-neutral-950"
        x-on:click="sidebarIsOpen = false"
        x-bind:aria-expanded="sidebarIsOpen"
        aria-controls="sidebar-navigation"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 16 16"
          fill="currentColor"
          class="size-6"
          aria-hidden="true"
        >
          <path
            d="M5.28 4.22a.75.75 0 0 0-1.06 1.06L6.94 8l-2.72 2.72a.75.75 0 1 0 1.06 1.06L8 9.06l2.72 2.72a.75.75 0 1 0 1.06-1.06L9.06 8l2.72-2.72a.75.75 0 0 0-1.06-1.06L8 6.94z"
          />
        </svg>
        <span class="sr-only">Tutup sidebar</span>
      </button>
      {{-- close button for mobile screens end --}}

      {{-- logo binary start --}}
      <a
        href="/"
        class="ml-2 w-fit text-2xl font-bold text-neutral-900 focus-visible:outline focus-visible:outline-neutral-950"
      >
        <img
          src="{{ asset('assets/binary-logo/binary-logo-text-white.png') }}"
          alt="Binary Photoworks Homepage"
          class="w-full object-cover"
        >
      </a>
      {{-- logo binary end --}}

      {{-- sidebar links start --}}
      <nav aria-label="Navigasi Menu Utama">
        <ul class="flex flex-col gap-2 overflow-y-auto pb-6">
          <li>
            <a
              href="#"
              class="flex items-center rounded-sm gap-2 px-2 py-1.5 text-sm font-medium text-stone-50 underline-offset-2 hover:bg-stone-500"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                class="size-5 shrink-0"
                aria-hidden="true"
              >
                <path
                  d="M15.5 2A1.5 1.5 0 0 0 14 3.5v13a1.5 1.5 0 0 0 1.5 1.5h1a1.5 1.5 0 0 0 1.5-1.5v-13A1.5 1.5 0 0 0 16.5 2h-1ZM9.5 6A1.5 1.5 0 0 0 8 7.5v9A1.5 1.5 0 0 0 9.5 18h1a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 10.5 6h-1ZM3.5 10A1.5 1.5 0 0 0 2 11.5v5A1.5 1.5 0 0 0 3.5 18h1A1.5 1.5 0 0 0 6 16.5v-5A1.5 1.5 0 0 0 4.5 10h-1Z"
                />
              </svg>
              <span>Dashboard</span>
            </a>
          </li>
        </ul>
      </nav>
      {{-- sidebar links end --}}
    </aside>
    {{-- sidebar navigation container end --}}

    {{-- top navbar & main content start --}}
    <div class="h-svh w-full overflow-y-auto">
      {{-- top header start --}}
      <header
        class="sticky top-0 z-10 flex items-center justify-between border-b border-neutral-300 bg-neutral-50 px-8 py-6"
      >
        {{-- sidebar toggle button for small screens start --}}
        <button
          type="button"
          class="md:hidden inline-block text-neutral-600 focus-visible:outline focus-visible:outline-neutral-950"
          x-on:click="sidebarIsOpen = true"
          x-bind:aria-expanded="sidebarIsOpen"
          aria-controls="sidebar-navigation"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 16 16"
            fill="currentColor"
            class="size-5"
            aria-hidden="true"
          >
            <path
              d="M0 3a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm5-1v12h9a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1zM4 2H2a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h2z"
            />
          </svg>
          <span class="sr-only">Buka sidebar menu</span>
        </button>
        {{-- sidebar toggle button for small screens end --}}

        {{-- breadcrumbs start --}}
        <x-layouts.backdoor.components.breadcrumbs :items="$breadcrumbs" />
        {{-- breadcrumbs end --}}
      </header>
      {{-- top header end --}}

      {{-- main content start --}}
      <main
        id="main-content"
        class="p-8 focus:outline-hidden"
        tabindex="-1"
      >
        <div class="overflow-y-auto">
          {{ $content ?? '' }}

        </div>
      </main>
      {{-- main content end --}}
    </div>
    {{-- top navbar & main content end --}}
  </div>
  {{-- trigger alert dan toast berdasarkan session  --}}
  <x-scripts.alert-toast />

  {{-- for javascript --}}
  {{ $scripts ?? '' }}
</body>

</html>
