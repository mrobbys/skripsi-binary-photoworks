{{-- 
  * COMPONENT SIDEBAR LINKS

  * Slot : 
      - Berisikan nav item
--}}

{{-- sidebar links start --}}
<nav
  x-cloak
  aria-label="Navigasi Menu Utama"
  class="min-h-0 flex-1 overflow-y-auto"
>
  <ul class="flex flex-col gap-2 pb-6">

    {{-- dashboard start --}}
    <x-layouts.backdoor.components.sidebar-link-item
      :href="route('backdoor.dashboard')"
      icon='ri-dashboard-line'
      title='Dashboard'
    />
    {{-- dashboard end --}}

    {{-- jadwal sesi start --}}
    <x-layouts.backdoor.components.sidebar-collapse-item
      title='Jadwal Sesi'
      icon='ri-camera-line'
      :active="request()->is('backdoor/session-schedule/*')"
    >
      {{-- daftar jadwal start --}}
      <x-layouts.backdoor.components.sidebar-collapse-link
        :href="route('backdoor.session-schedule.list')"
        :active="request()->routeIs('backdoor.session-schedule.list.*')"
        title='Daftar Jadwal'
      />
      {{-- daftar jadwal end --}}

      {{-- kalender sesi start --}}
      <x-layouts.backdoor.components.sidebar-collapse-link
        :href="route('backdoor.session-schedule.calendar')"
        :active="request()->routeIs('backdoor.session-schedule.calendar')"
        title='Kalender'
      />
      {{-- kalender sesi end --}}
    </x-layouts.backdoor.components.sidebar-collapse-item>
    {{-- jadwal sesi end --}}

    {{-- manajemen pemesanan start --}}
    <x-layouts.backdoor.components.sidebar-link-item
      :href="route('backdoor.booking-management.index')"
      :active="request()->routeIs('backdoor.booking-management.*')"
      icon='ri-calendar-check-line'
      title='Manajemen Pemesanan'
    />
    {{-- manajemen pemesanan end --}}

    {{-- data klien start --}}
    <x-layouts.backdoor.components.sidebar-link-item
      :href="route('backdoor.client-data.index')"
      :active="request()->routeIs('backdoor.client-data.*')"
      icon='ri-team-line'
      title='Data Klien'
    />
    {{-- data klien end --}}

    {{-- data master start --}}
    <x-layouts.backdoor.components.sidebar-collapse-item
      title='Data Master'
      icon='ri-database-2-line'
      :active="request()->is('backdoor/data-master/*')"
    >
      {{-- kategori start --}}
      <x-layouts.backdoor.components.sidebar-collapse-link
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

    {{-- ulasan klien start --}}
    <x-layouts.backdoor.components.sidebar-link-item
      :href="route('backdoor.client-reviews.index')"
      :active="request()->routeIs('backdoor.client-reviews.*')"
      icon='ri-chat-quote-line'
      title='Ulasan Klien'
    />
    {{-- ulasan klien end --}}

    {{-- laporan start --}}
    {{-- TODO: tambahkan href, active,  --}}
    <x-layouts.backdoor.components.sidebar-link-item
      icon='ri-file-pdf-line'
      title='Laporan'
    />
    {{-- laporan end --}}

    {{-- pengaturan sistem start --}}
    {{-- TODO: tambahkan halaman lain, href, active, icon --}}
    <x-layouts.backdoor.components.sidebar-collapse-item
      title='Pengaturan Sistem'
      icon='ri-settings-3-line'
      :active="request()->routeIs('backdoor.system-settings.*')"
    >
      <x-layouts.backdoor.components.sidebar-collapse-link title='Manajemen User' />

      <x-layouts.backdoor.components.sidebar-collapse-link
        :href="route('backdoor.system-settings.roles.index')"
        :active="request()->routeIs('backdoor.system-settings.roles.*')"
        title='Manajemen Role'
      />

      <x-layouts.backdoor.components.sidebar-collapse-link
        :href="route('backdoor.system-settings.activity-logs.index')"
        :active="request()->routeIs('backdoor.system-settings.activity-logs.*')"
        title='Activity Logs'
      />
    </x-layouts.backdoor.components.sidebar-collapse-item>
    {{-- pengaturan sistem end --}}

  </ul>
</nav>
{{-- sidebar links end --}}
