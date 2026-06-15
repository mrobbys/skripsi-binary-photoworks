{{--
   * COMPONENT BACKDOOR TOP HEADER
   * Topbar header untuk dashboard admin
--}}

<header
  class="sticky top-0 z-10 flex items-center justify-between border-b border-neutral-300 bg-neutral-50 px-8 py-6"
>
  {{-- Tombol Buka Sidebar (Mobile) --}}
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

  {{-- Slot untuk breadcrumbs / elemen kanan topbar --}}
  {{ $slot }}
</header>
