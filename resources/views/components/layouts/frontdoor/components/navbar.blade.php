<header class="bg-white border-b border-stone-200">
  <nav x-data="{ mobileMenuIsOpen: false }" x-on:click.away="mobileMenuIsOpen = false"
    class="flex items-center justify-between px-6 py-4 max-w-7xl mx-auto" aria-label="penguin ui menu">
    {{ $slot }}
  </nav>
</header>
