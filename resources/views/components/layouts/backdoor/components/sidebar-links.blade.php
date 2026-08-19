{{-- 
  * COMPONENT SIDEBAR LINKS

  * Slot : 
      - Berisikan nav item
--}}

{{-- sidebar links start --}}
<nav
  x-cloak
  aria-label="Navigasi Menu Utama"
  class="no-scrollbar min-h-0 flex-1 overflow-y-auto"
>
  <ul class="flex flex-col gap-2 pb-6">

    {{-- dashboard start --}}
    @can('dashboard-admin-view')
      <x-layouts.backdoor.components.sidebar-link-item
        :href="route('backdoor.dashboard.index')"
        icon='ri-dashboard-line'
        title='Dashboard'
      />
    @endcan
    {{-- dashboard end --}}

    {{-- jadwal sesi start --}}
    @canany(['scheduleSession-view', 'scheduleSession-calendar'])
      <x-layouts.backdoor.components.sidebar-collapse-item
        title='Jadwal Sesi'
        icon='ri-camera-line'
        :active="request()->is('backdoor/session-schedule/*')"
      >
        {{-- daftar jadwal start --}}
        @can('scheduleSession-view')
          <x-layouts.backdoor.components.sidebar-collapse-link
            :href="route('backdoor.session-schedule.list')"
            :active="request()->routeIs('backdoor.session-schedule.list*')"
            title='Daftar Jadwal'
          />
        @endcan
        {{-- daftar jadwal end --}}

        {{-- kalender sesi start --}}
        @can('scheduleSession-calendar')
          <x-layouts.backdoor.components.sidebar-collapse-link
            :href="route('backdoor.session-schedule.calendar')"
            :active="request()->routeIs('backdoor.session-schedule.calendar')"
            title='Kalender'
          />
        @endcan
        {{-- kalender sesi end --}}
      </x-layouts.backdoor.components.sidebar-collapse-item>
    @endcanany
    {{-- jadwal sesi end --}}

    {{-- manajemen pemesanan start --}}
    @can('booking-management-view')
      <x-layouts.backdoor.components.sidebar-link-item
        :href="route('backdoor.booking-management.index')"
        :active="request()->routeIs('backdoor.booking-management.*')"
        icon='ri-calendar-check-line'
        title='Manajemen Pemesanan'
      />
    @endcan
    {{-- manajemen pemesanan end --}}

    {{-- data klien start --}}
    @can('clientData-view')
      <x-layouts.backdoor.components.sidebar-link-item
        :href="route('backdoor.client-data.index')"
        :active="request()->routeIs('backdoor.client-data.*')"
        icon='ri-team-line'
        title='Data Klien'
      />
    @endcan
    {{-- data klien end --}}

    {{-- data master start --}}
    @canany(['category-master-view', 'packageVariant-master-view', 'background-master-view', 'addon-master-view', 'schedule-master-view'])
      <x-layouts.backdoor.components.sidebar-collapse-item
        title='Data Master'
        icon='ri-database-2-line'
        :active="request()->is('backdoor/data-master/*')"
      >
        {{-- kategori start --}}
        @can('category-master-view')
          <x-layouts.backdoor.components.sidebar-collapse-link
            :href="route('backdoor.data-master.category.index')"
            title='Kategori Foto'
          />
        @endcan
        {{-- kategori end --}}

        {{-- kelola paket & varian start --}}
        @can('packageVariant-master-view')
          <x-layouts.backdoor.components.sidebar-collapse-link
            :href="route('backdoor.data-master.package.index')"
            :active="request()->routeIs('backdoor.data-master.package.*')"
            title='Kelola Paket & Varian'
          />
        @endcan
        {{-- kelola paket & varian end --}}

        {{-- background start --}}
        @can('background-master-view')
          <x-layouts.backdoor.components.sidebar-collapse-link
            :href="route('backdoor.data-master.background.index')"
            :active="request()->routeIs('backdoor.data-master.background.*')"
            title='Background'
          />
        @endcan
        {{-- background end --}}

        {{-- layanan tambahan start --}}
        @can('addon-master-view')
          <x-layouts.backdoor.components.sidebar-collapse-link
            :href="route('backdoor.data-master.addon.index')"
            :active="request()->routeIs('backdoor.data-master.addon.*')"
            title='Layanan Tambahan'
          />
        @endcan
        {{-- layanan tambahan end --}}

        {{-- jadwal operasional start --}}
        @can('schedule-master-view')
          <x-layouts.backdoor.components.sidebar-collapse-link
            :href="route('backdoor.data-master.schedule.index')"
            :active="request()->routeIs('backdoor.data-master.schedule.*')"
            title='Jadwal Operasional'
          />
        @endcan
        {{-- jadwal operasional end --}}

      </x-layouts.backdoor.components.sidebar-collapse-item>
    @endcanany
    {{-- data master end --}}

    {{-- ulasan klien start --}}
    @can('review-client-view')
      <x-layouts.backdoor.components.sidebar-link-item
        :href="route('backdoor.client-reviews.index')"
        :active="request()->routeIs('backdoor.client-reviews.*')"
        icon='ri-chat-quote-line'
        title='Ulasan Klien'
      />
    @endcan
    {{-- ulasan klien end --}}

    {{-- laporan start --}}
    @can('report-view')
      <x-layouts.backdoor.components.sidebar-link-item
        :href="route('backdoor.reports.index')"
        :active="request()->routeIs('backdoor.reports.*')"
        icon='ri-file-pdf-line'
        title='Laporan'
      />
    @endcan
    {{-- laporan end --}}

    {{-- pengaturan sistem start --}}
    @canany(['user-management-view', 'role-management-view', 'activityLog-management-view'])
      <x-layouts.backdoor.components.sidebar-collapse-item
        title='Pengaturan Sistem'
        icon='ri-settings-3-line'
        :active="request()->routeIs('backdoor.system-settings.*')"
      >
        @can('user-management-view')
          <x-layouts.backdoor.components.sidebar-collapse-link
            :href="route('backdoor.system-settings.users.index')"
            :active="request()->routeIs('backdoor.system-settings.users.*')"
            title='Manajemen User'
          />
        @endcan

        @can('role-management-view')
          <x-layouts.backdoor.components.sidebar-collapse-link
            :href="route('backdoor.system-settings.roles.index')"
            :active="request()->routeIs('backdoor.system-settings.roles.*')"
            title='Manajemen Role'
          />
        @endcan

        @can('activityLog-management-view')
          <x-layouts.backdoor.components.sidebar-collapse-link
            :href="route('backdoor.system-settings.activity-logs.index')"
            :active="request()->routeIs('backdoor.system-settings.activity-logs.*')"
            title='Activity Logs'
          />
        @endcan
      </x-layouts.backdoor.components.sidebar-collapse-item>
    @endcanany
    {{-- pengaturan sistem end --}}

  </ul>
</nav>
{{-- sidebar links end --}}
