<header class="bg-stone-50 border-b border-stone-200 sticky top-0 z-50">
  <nav x-data="{ mobileMenuIsOpen: false }" x-on:click.away="mobileMenuIsOpen = false"
    class="flex items-center justify-between px-6 py-4 max-w-7xl mx-auto" aria-label="Navigasi Utama">
    {{ $slot }}
  </nav>
</header>
