{{--
   * COMPONENT BACKDOOR TOP HEADER
   * Topbar header untuk dashboard admin
--}}

<header
  class="sticky top-0 z-10 flex items-center justify-between border-b border-stone-300 bg-stone-50 py-6 px-8">
  <div class="flex items-center gap-4">
    {{-- Tombol Toggle Sidebar --}}
    <button
      type="button"
      class="inline-block text-stone-700 focus-visible:outline-none cursor-pointer transition-transform duration-150 hover:scale-110"
      x-on:click="sidebarIsOpen = !sidebarIsOpen"
      x-bind:aria-expanded="sidebarIsOpen"
      aria-controls="sidebar-navigation">
      <i class="ri-menu-fill text-2xl"></i>
      <span class="sr-only">Toggle sidebar menu</span>
    </button>

    {{-- Slot untuk breadcrumbs / elemen kiri topbar --}}
    {{ $slot }}
  </div>
</header>
