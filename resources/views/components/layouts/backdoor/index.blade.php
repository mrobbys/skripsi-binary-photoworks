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

<body data-module="{{ $jsModule ?? '' }}">
  <div
    x-data="{ sidebarIsOpen: false }"
    class="relative flex w-full flex-col md:flex-row"
  >
    {{-- screen reader start --}}
    <a
      class="sr-only"
      href="#main-content"
    >
      skip to the main content
    </a>
    {{-- screen reader end --}}

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
    {{-- dark overlay berfungsi ketika sidebar di layar kecil terbuka start --}}

    <nav
      x-cloak
      class="fixed left-0 z-30 flex h-svh w-60 shrink-0 flex-col border-r border-neutral-300 bg-neutral-50 p-4 transition-transform duration-300 md:w-64 md:translate-x-0 md:relative space-y-8"
      x-bind:class="sidebarIsOpen ? 'translate-x-0' : '-translate-x-60'"
      aria-label="sidebar navigation"
    >
      {{-- logo binary start --}}
      <a
        href="#"
        class="ml-2 w-fit text-2xl font-bold text-neutral-900"
      >
        <span class="sr-only">homepage</span>
        <img
          src="{{ asset('assets/binary-logo/binary-logo-text-black.png') }}"
          alt="Logo Binary"
          class="w-full"
        >
      </a>
      {{-- logo binary end --}}

      {{-- sidebar links start --}}
      <div class="flex flex-col gap-2 overflow-y-auto pb-6">

        <a
          href="#"
          class="flex items-center rounded-sm gap-2 px-2 py-1.5 text-sm font-medium text-neutral-600 underline-offset-2 hover:bg-black/5 hover:text-neutral-900 focus-visible:underline focus:outline-hidden"
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
      </div>
      {{-- sidebar links end --}}
    </nav>

    {{-- top navbar & main content start --}}
    <div class="h-svh w-full overflow-y-auto bg-white">
      {{-- top navbar start --}}
      <nav
        class="sticky top-0 z-10 flex items-center justify-between border-b border-neutral-300 bg-neutral-50 px-8 py-6"
        aria-label="top navibation bar"
      >

        {{-- sidebar toggle button for small screens start --}}
        <button
          type="button"
          class="md:hidden inline-block text-neutral-600"
          x-on:click="sidebarIsOpen = true"
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
          <span class="sr-only">sidebar toggle</span>
        </button>
        {{-- sidebar toggle button for small screens end --}}

        {{-- breadcrumbs start --}}
        <nav
          class="hidden md:inline-block text-sm font-medium text-neutral-600"
          aria-label="breadcrumb"
        >
          <ol class="flex flex-wrap items-center gap-1">
            <li class="flex items-center gap-1">
              <a
                href="#"
                class="hover:text-neutral-900"
              >Dashboard</a>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                stroke="currentColor"
                fill="none"
                stroke-width="2"
                class="size-4"
                aria-hidden="true"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="m8.25 4.5 7.5 7.5-7.5 7.5"
                />
              </svg>
            </li>

            <li
              class="flex items-center gap-1 font-bold text-neutral-900"
              aria-current="page"
            >Marketing</li>
          </ol>
        </nav>
        {{-- breadcrumb end --}}
      </nav>
      {{-- main content start --}}
      <div
        id="main-content"
        class="p-8"
      >
        <div class="overflow-y-auto">
          {{ $content ?? '' }}
        </div>
      </div>
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
