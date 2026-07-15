{{-- 
  * COMPONENT SIDEBAR LINKS

  * Slot : 
      - Berisikan nav item
--}}

{{-- sidebar links start --}}
<nav
  x-cloak
  aria-label="Navigasi Menu Utama"
  class="flex-1 overflow-y-auto min-h-0"
>
  <ul class="flex flex-col gap-2 pb-6">

    {{-- dashboard start --}}
    <x-layouts.backdoor.components.sidebar-link-item
      {{-- prettier-ignore --}}
      :href="route('backdoor.dashboard')"
      icon='ri-dashboard-line'
      title='Dashboard'
    />
    {{-- dashboard end --}}

    {{-- manajemen pemesanan start --}}
    <x-layouts.backdoor.components.sidebar-link-item
      :href="route('backdoor.booking-management.index')"
      icon='ri-calendar-check-line'
      title='Manajemen Pemesanan'
    />
    {{-- manajemen pemesanan end --}}

    {{-- data master start --}}
    <x-layouts.backdoor.components.sidebar-collapse-item
      title='Data Master'
      icon='ri-database-2-line'
      {{-- prettier-ignore --}}
      :active="request()->is('backdoor/data-master/*')">
      {{-- kategori start --}}
      <x-layouts.backdoor.components.sidebar-collapse-link
      {{-- prettier-ignore --}}
      :href="route('backdoor.data-master.category.index')"
      title='Kategori Foto'
    />
    {{-- kategori end --}}

    {{-- kelola paket & varian start --}}
    <x-layouts.backdoor.components.sidebar-collapse-link
      :href="route('backdoor.data-master.package.index')"
      :active="request()->routeIs('backdoor.data-master.package.*')"
      title='Kelola Paket & Varian'
    />
    {{-- kelola paket & varian end --}}

    {{-- background start --}}
    <x-layouts.backdoor.components.sidebar-collapse-link
      :href="route('backdoor.data-master.background.index')"
      :active="request()->routeIs('backdoor.data-master.background.*')"
      title='Background'
    />
    {{-- background end --}}

    {{-- layanan tambahan start --}}
    <x-layouts.backdoor.components.sidebar-collapse-link
      :href="route('backdoor.data-master.addon.index')"
      :active="request()->routeIs('backdoor.data-master.addon.*')"
      title='Layanan Tambahan'
    />
    {{-- layanan tambahan end --}}

    {{-- jadwal operasional start --}}
    <x-layouts.backdoor.components.sidebar-collapse-link
      :href="route('backdoor.data-master.schedule.index')"
      :active="request()->routeIs('backdoor.data-master.schedule.*')"
      title='Jadwal Operasional'
    />
    {{-- jadwal operasional end --}}

    </x-layouts.backdoor.components.sidebar-collapse-item>
    {{-- data master end --}}

  </ul>
</nav>
{{-- sidebar links end --}}
