{{-- 
  * COMPONENT SIDEBAR NAVIGATION

  * Slot : 
      - sidebar-links
  
--}}

<aside
  id="sidebar-navigation"
  x-cloak
  class="fixed left-0 z-30 flex h-dvh w-60 shrink-0 flex-col border-r border-neutral-300 bg-stone-700 py-4 px-2 transition-transform duration-300 md:w-64 md:translate-x-0 md:relative space-y-12"
  x-bind:class="sidebarIsOpen ? 'translate-x-0' : '-translate-x-60'"
  aria-label="Sidebar Menu"
>
  {{-- sidebar brand start --}}
  <x-layouts.backdoor.components.sidebar-brand />
  {{-- sidebar brand end --}}

  {{ $slot ?? '' }}

  {{-- sidebar profile start --}}
  <x-layouts.backdoor.components.sidebar-profile />
  {{-- sidebar profile end --}}
</aside>
