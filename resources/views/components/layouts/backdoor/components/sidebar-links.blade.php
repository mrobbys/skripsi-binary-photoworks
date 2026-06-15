{{-- 
  * COMPONENT SIDEBAR LINKS

  * Slot : 
      - Berisikan nav item
--}}

{{-- sidebar links start --}}
<nav x-cloak aria-label="Navigasi Menu Utama" class="flex-1 overflow-y-auto min-h-0">
  <ul class="flex flex-col gap-2 pb-6">

    {{-- dashboard start --}}
    <x-layouts.backdoor.components.sidebar-link-item
      :href="route('backdoor.dashboard')"
      icon='ri-dashboard-line'
      title='Dashboard'
    />
    {{-- dashboard end --}}

    {{-- data master start --}}
    <x-layouts.backdoor.components.sidebar-collapse-item
      title='Data Master'
      icon='ri-database-2-line'
      :active="request()->is('backdoor/data-master/*')"
    >
      <x-layouts.backdoor.components.sidebar-collapse-link
        :href="route('backdoor.data-master.category')"
        title='Kategori Foto'
      />
    </x-layouts.backdoor.components.sidebar-collapse-item>
    {{-- data master end --}}

  </ul>
</nav>
{{-- sidebar links end --}}
