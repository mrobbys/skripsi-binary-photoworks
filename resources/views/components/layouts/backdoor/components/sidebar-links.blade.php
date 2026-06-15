{{-- 
  * COMPONENT SIDEBAR LINKS

  * Slot : 
      - Berisikan nav item
--}}

{{-- sidebar links start --}}
<nav aria-label="Navigasi Menu Utama">
  <ul class="flex flex-col gap-2 overflow-y-auto pb-6">

    {{-- dashboard start --}}
    <x-layouts.backdoor.components.sidebar-link-item
      href="{{ route('backdoor.dashboard') }}"
      icon='ri-dashboard-line'
      title='Dashboard'
    />
    {{-- dashboard end --}}

    {{-- data master start --}}
    <x-layouts.backdoor.components.sidebar-link-item
      href="#"
      icon='ri-database-2-line'
      title='Data Master'
    />
    {{-- data master end --}}
  </ul>
</nav>
{{-- sidebar links end --}}
