<ul
  x-cloak
  x-show="mobileMenuIsOpen"
  x-transition:enter="transition motion-reduce:transition-none ease-out duration-300"
  x-transition:enter-start="-translate-y-full"
  x-transition:enter-end="translate-y-0"
  x-transition:leave="transition motion-reduce:transition-none ease-out duration-300"
  x-transition:leave-start="translate-y-0"
  x-transition:leave-end="-translate-y-full"
  id="mobileMenu"
  class="fixed inset-x-0 top-0 z-10 flex max-h-svh flex-col divide-y divide-stone-100 overflow-y-auto border-b border-stone-200 bg-stone-50 px-6 pb-6 pt-4 lg:hidden"
>
  {{-- brand logo start --}}
  <li class="flex items-center justify-between border-none pb-2">
    <x-layouts.frontdoor.components.brand-logo />
  </li>
  {{-- brand logo end --}}
  
  {{-- home start --}}
  <x-layouts.frontdoor.components.mobile-nav-link
    :href="route('frontdoor.home')"
    :active="request()->routeIs('frontdoor.home')"
    title="Home"
  />
  {{-- home end --}}

  {{-- about start --}}
  <x-layouts.frontdoor.components.mobile-nav-link
    :href="route('frontdoor.about')"
    :active="request()->routeIs('frontdoor.about')"
    title="About"
  />
  {{-- about end --}}

  {{-- services start --}}
  <x-layouts.frontdoor.components.mobile-nav-link
    :href="route('frontdoor.services.index')"
    :active="request()->routeIs('frontdoor.services.*') || request()->is('services*')"
    title="Services"
  />
  {{-- services end --}}

  {{-- reviews start --}}
  <x-layouts.frontdoor.components.mobile-nav-link
    :href="route('frontdoor.reviews')"
    :active="request()->routeIs('frontdoor.reviews')"
    title="Reviews"
  />
  {{-- reviews end --}}

  {{-- faq start --}}
  <x-layouts.frontdoor.components.mobile-nav-link
    :href="route('frontdoor.faq')"
    :active="request()->routeIs('frontdoor.faq')"
    title="FAQ"
  />
  {{-- faq end --}}

  {{-- contact us start --}}
  <x-layouts.frontdoor.components.mobile-nav-link
    :href="route('frontdoor.contact-us')"
    :active="request()->routeIs('frontdoor.contact-us')"
    title="Contact Us"
  />
  {{-- contact us end --}}

  {{-- login btn start --}}
  <li class="mt-4 w-full border-none">
    <x-layouts.frontdoor.components.auth-button />
  </li>
  {{-- login btn end --}}
</ul>
