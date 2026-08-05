<button x-on:click="mobileMenuIsOpen = !mobileMenuIsOpen" x-bind:aria-expanded="mobileMenuIsOpen"
  x-bind:class="mobileMenuIsOpen ? 'fixed top-6 right-6 z-20' : null" type="button"
  class="flex text-stone-800 lg:hidden" aria-label="mobile menu"
  aria-controls="mobileMenu">
  <i class="ri-menu-line text-2xl" x-cloak x-show="!mobileMenuIsOpen"></i>
  <i class="ri-close-line text-2xl mt-1" x-cloak x-show="mobileMenuIsOpen"></i>
</button>
