{{-- 
  * COMPONENT SIDEBAR NAVIGATION

  * Slot : 
      - sidebar-links
  
--}}

<aside
  id="sidebar-navigation"
  x-cloak
  x-on:mouseleave="if (isHoverOpened && window.innerWidth >= 768) { sidebarIsOpen = false; isHoverOpened = false }"
  class="fixed left-0 z-30 flex h-dvh w-60 shrink-0 flex-col bg-stone-700 py-4 px-0 transition-all duration-300 md:relative"
  x-bind:class="sidebarIsOpen ? 'translate-x-0' : '-translate-x-60 md:-ml-60'"
  aria-label="Sidebar Menu">
  {{-- sidebar brand start --}}
  <x-layouts.backdoor.components.sidebar-brand />
  {{-- sidebar brand end --}}

  {{ $slot ?? "" }}

  {{-- sidebar profile start --}}
  <x-layouts.backdoor.components.sidebar-profile />
  {{-- sidebar profile end --}}
</aside>
