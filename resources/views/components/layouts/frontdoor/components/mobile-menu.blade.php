<ul x-cloak x-show="mobileMenuIsOpen"
  x-transition:enter="transition motion-reduce:transition-none ease-out duration-300"
  x-transition:enter-start="-translate-y-full" x-transition:enter-end="translate-y-0"
  x-transition:leave="transition motion-reduce:transition-none ease-out duration-300"
  x-transition:leave-start="translate-y-0" x-transition:leave-end="-translate-y-full" id="mobileMenu"
  class="fixed max-h-svh overflow-y-auto inset-x-0 top-0 z-10 flex flex-col divide-y divide-stone-100 rounded-b-md border-b border-stone-200 bg-stone-50 px-6 pb-6 pt-20 sm:hidden">
  <x-layouts.frontdoor.components.mobile-nav-link :href="route('frontdoor.home')" :active="request()->routeIs('frontdoor.home')">
    Home
  </x-layouts.frontdoor.components.mobile-nav-link>
  <x-layouts.frontdoor.components.mobile-nav-link :href="route('frontdoor.about')" :active="request()->routeIs('frontdoor.about')">
    About
  </x-layouts.frontdoor.components.mobile-nav-link>
  <x-layouts.frontdoor.components.mobile-nav-link :href="route('frontdoor.services')" :active="request()->routeIs('frontdoor.services')">
    Services
  </x-layouts.frontdoor.components.mobile-nav-link>
  <x-layouts.frontdoor.components.mobile-nav-link :href="route('frontdoor.reviews')" :active="request()->routeIs('frontdoor.reviews')">
    Reviews
  </x-layouts.frontdoor.components.mobile-nav-link>
  <x-layouts.frontdoor.components.mobile-nav-link :href="route('frontdoor.faq')" :active="request()->routeIs('frontdoor.faq')">
    FAQ
  </x-layouts.frontdoor.components.mobile-nav-link>
  <x-layouts.frontdoor.components.mobile-nav-link :href="route('frontdoor.contact-us')" :active="request()->routeIs('frontdoor.contact-us')">
    Contact Us
  </x-layouts.frontdoor.components.mobile-nav-link>
  <!-- CTA Button -->
  <li class="mt-4 w-full border-none">
    <x-shared.button as="a" href="{{ route('login') }}" variant="outline" value="Masuk" class="w-full text-center" />
  </li>
</ul>
