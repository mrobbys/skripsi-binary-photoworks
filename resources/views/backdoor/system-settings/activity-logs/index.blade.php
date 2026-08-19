@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Pengaturan Sistem', 'url' => '#'],
      ['label' => 'Activity Logs', 'url' => ''],
  ];

  $tableHeaders = ['No', 'Waktu Sesi', 'Pelaku', 'Modul & ID', 'Aktivitas'];
  if (auth()->user()->can('activityLog-management-view')) {
      $tableHeaders[] = 'Aksi';
  }
@endphp

<x-layouts.backdoor.index
  title="Activity Logs"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/system-settings/activity-logs/ActivityLogs"
>
  <x-slot:content>
    <div
      x-data="ActivityLogs"
      x-cloak
      class="w-full space-y-6"
    >

      <x-backdoor.shared.page-header title="Activity Logs" />

      {{-- table card start --}}
      <div class="relative overflow-visible border border-stone-200 p-4">

        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari pelaku..." />
          </x-slot:left>
        </x-backdoor.table.header>

        <x-backdoor.table.container :headers="$tableHeaders">
          <template
            x-for="(log, index) in table.data"
            :key="log.id"
          >
            <tr
              class="border-b border-stone-200 transition hover:bg-stone-100"
              x-show="!table.isLoading"
              x-cloak
            >

              {{-- no start --}}
              <x-backdoor.table.cell
                class="text-stone-500"
                x-text="index + 1 + ((table.pagination.current_page - 1) * table.pagination.per_page)"
              />
              {{-- no end --}}

              {{-- waktu sesi start --}}
              <x-backdoor.table.cell
                class="whitespace-nowrap font-mono text-sm text-stone-600"
                x-text="log.waktu_sesi"
              />
              {{-- waktu sesi end --}}

              {{-- pelaku start --}}
              <x-backdoor.table.cell
                class="font-semibold text-stone-900"
                x-text="log.pelaku"
              />
              {{-- pelaku end --}}

              {{-- modul & id start --}}
              <x-backdoor.table.cell
                class="whitespace-nowrap text-sm text-stone-600"
                x-text="log.modul"
              />
              {{-- modul & id end --}}

              {{-- aktivitas start --}}
              <x-backdoor.table.cell>
                <span
                  class="inline-block px-2 py-0.5 text-xs font-semibold uppercase tracking-wide"
                  :class="badgeClass(log.aktivitas)"
                  x-text="log.aktivitas"
                ></span>
              </x-backdoor.table.cell>
              {{-- aktivitas end --}}

              @can('activityLog-management-view')
                {{-- aksi btn detail start --}}
                <x-backdoor.table.cell>
                  <x-shared.button
                    x-on:click="openDetail(log.properties)"
                    variant="outline"
                    size="sm"
                    value="Lihat Detail"
                    class="whitespace-nowrap"
                  >
                    <x-slot:iconLeft>
                      <i
                        class="ri-file-list-line"
                        aria-hidden="true"
                      ></i>
                    </x-slot:iconLeft>
                  </x-shared.button>
                </x-backdoor.table.cell>
                {{-- aksi btn detail end --}}
              @endcan

            </tr>
          </template>
        </x-backdoor.table.container>

        <x-backdoor.table.pagination />

      </div>
      {{-- table card end --}}

      {{-- modal detail log start --}}
      <x-backdoor.system-settings.activity-logs.detail-modal />
      {{-- modal detail log end --}}

    </div>
  </x-slot:content>
</x-layouts.backdoor.index>
