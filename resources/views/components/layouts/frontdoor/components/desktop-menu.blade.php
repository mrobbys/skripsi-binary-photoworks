{{-- Menu Desktop --}}
<ul class="hidden items-center gap-8 sm:flex">
  {{-- home start --}}
  <x-layouts.frontdoor.components.nav-link
    :href="route('frontdoor.home')"
    :active="request()->routeIs('frontdoor.home')"
    title="Home" />
  {{-- home end --}}

  {{-- about start --}}
  <x-layouts.frontdoor.components.nav-link
    :href="route('frontdoor.about')"
    :active="request()->routeIs('frontdoor.about')"
    title="About" />
  {{-- about end --}}

  {{-- services start --}}
  <x-layouts.frontdoor.components.nav-link
    :href="route('frontdoor.services.index')"
    :active="request()->routeIs('frontdoor.services.*') || request()->is('services*')"
    title="Services" />
  {{-- services end --}}

  {{-- reviews start --}}
  <x-layouts.frontdoor.components.nav-link
    :href="route('frontdoor.reviews')"
    :active="request()->routeIs('frontdoor.reviews')"
    title="Reviews" />
  {{-- reviews end --}}

  {{-- faq start --}}
  <x-layouts.frontdoor.components.nav-link
    :href="route('frontdoor.faq')"
    :active="request()->routeIs('frontdoor.faq')"
    title="FAQ" />
  {{-- faq end --}}

  {{-- contact us start --}}
  <x-layouts.frontdoor.components.nav-link
    :href="route('frontdoor.contact-us')"
    :active="request()->routeIs('frontdoor.contact-us')"
    title="Contact Us" />
  {{-- contact us end --}}
</ul>