@php
  use Carbon\Carbon;
  use App\Support\Formatter;

  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Jadwal Sesi', 'url' => '#'],
      ['label' => 'Daftar Jadwal', 'url' => ''],
  ];

  $jadwal_hari_ini = Formatter::dateId(Carbon::now(), 'l, d F Y');

  $tableHeaders = ['No', 'Waktu Sesi', 'Nama Klien', 'Paket Foto', 'Background', 'Status Sesi'];
  if (auth()->user()->can('scheduleSession-view')) {
      $tableHeaders[] = 'Aksi';
  }
@endphp

<x-layouts.backdoor.index
  title="Daftar Jadwal Sesi"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/session-schedule/index/Index"
>

  <x-slot:content>
    <div
      x-data="Index"
      x-cloak
      class="w-full space-y-6"
    >

      {{-- header section start --}}
      <div class="flex flex-col gap-1">
        <x-backdoor.shared.page-header title="Daftar Jadwal Sesi" />
        <p class="mt-2 text-sm text-stone-500">
          Data jadwal hari ini:
          <strong>{{ $jadwal_hari_ini }}</strong>
        </p>
      </div>
      {{-- header section end --}}

      {{-- stats section start --}}
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <x-backdoor.shared.stats-card
          label="Total Sesi Foto Hari Ini"
          x-text="state.totalToday"
          suffix="Sesi"
        />
        <x-backdoor.shared.stats-card
          label="Total Sesi Selesai Hari Ini"
          x-text="state.doneToday"
          suffix="Sesi"
        />
        <x-backdoor.shared.stats-card
          label="Total Jadwal Mendatang"
          x-text="state.upcomingTotal"
          suffix="Sesi"
        />
      </div>
      {{-- stats section end --}}

      {{-- table card start --}}
      <div class="relative overflow-visible border border-stone-200 p-4">

        {{-- table header start --}}
        <x-backdoor.table.header>
          <x-slot:left>
            <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center">
              <x-backdoor.table.search placeholder="Cari nama klien, booking..." />

              {{-- date filter input start --}}
              <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-stone-400">
                  <i class="ri-calendar-line text-sm"></i>
                </span>
                <input
                  type="text"
                  x-ref="dateFilterInput"
                  placeholder="Filter tanggal..."
                  readonly
                  class="w-full cursor-pointer border border-stone-300 bg-stone-50 py-2 pl-9 pr-9 text-sm text-stone-900 placeholder-stone-400 transition focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 sm:w-44"
                />
                <button
                  x-show="state.dateFilter"
                  x-cloak
                  x-on:click="clearDateFilter()"
                  x-bind:disabled="!state.dateFilter || table.isLoading"
                  type="button"
                  aria-label="Hapus filter tanggal"
                  class="absolute inset-y-0 right-0 flex items-center pr-3 text-stone-400 transition hover:text-stone-600"
                >
                  <i class="ri-close-line text-lg"></i>
                </button>
              </div>
              {{-- date filter input end --}}

            </div>
          </x-slot:left>

          <x-slot:right>
            {{-- cetak jadwal button start --}}
            @can('report-view')
              <x-shared.button
                as="a"
                :href="route('session-schedule.daily-report')"
                target="_blank"
                variant="charcoal"
                size="md"
                value="Cetak Jadwal Hari Ini"
              >
                <x-slot:iconLeft>
                  <i class="ri-printer-line leading-none"></i>
                </x-slot:iconLeft>
              </x-shared.button>
            @endcan
            {{-- cetak jadwal button end --}}
          </x-slot:right>
        </x-backdoor.table.header>
        {{-- table header end --}}

        {{-- table container start --}}
        <x-backdoor.table.container :headers="$tableHeaders">
          <template
            x-for="(schedule, index) in table.data"
            :key="schedule.id"
          >
            <tr
              class="border-b border-stone-200 transition hover:bg-stone-100"
              x-show="!table.isLoading"
              x-cloak
            >

              <x-backdoor.table.cell
                class="text-xs text-stone-600"
                x-text="index + 1 + ((table.pagination.current_page - 1) * table.pagination.per_page)"
              />

              <x-backdoor.table.cell class="text-sm">
                <p
                  class="font-medium text-stone-900 whitespace-nowrap"
                  x-text="schedule.formatted_date"
                ></p>
                <p class="mt-1 flex items-center gap-1 text-xs text-stone-500 whitespace-nowrap">
                  <i class="ri-time-line"></i>
                  <span x-text="schedule.formatted_time"></span>
                </p>
              </x-backdoor.table.cell>

              <x-backdoor.table.cell class="text-sm">
                <p
                  class="font-semibold text-stone-900"
                  x-text="schedule.user_name"
                ></p>
                <p
                  class="mt-0.5 text-xs text-stone-500"
                  x-text="schedule.user_phone"
                ></p>
              </x-backdoor.table.cell>

              <x-backdoor.table.cell class="text-sm">
                <div class="flex flex-col">
                  <span
                    class="font-medium text-stone-900"
                    x-text="schedule.package_name.split(' — ')[0]"
                  ></span>
                  <span
                    class="text-xs text-stone-500"
                    x-text="schedule.variant_name"
                  ></span>
                </div>
              </x-backdoor.table.cell>

              <x-backdoor.table.cell
                class="text-sm text-stone-700"
                x-text="schedule.background_name"
              />

              <x-backdoor.table.cell>
                <x-shared.badge
                  alpine="schedule.session_status === 'MENDATANG'"
                  size="sm"
                  variant="secondary"
                  value="MENDATANG"
                />
                <x-shared.badge
                  alpine="schedule.session_status === 'MENUNGGU'"
                  size="sm"
                  variant="warning"
                  value="MENUNGGU"
                />
                <x-shared.badge
                  alpine="schedule.session_status === 'SEDANG BERLANGSUNG'"
                  size="sm"
                  variant="info"
                  value="SEDANG BERLANGSUNG"
                />
                <x-shared.badge
                  alpine="schedule.session_status === 'SELESAI'"
                  size="sm"
                  variant="lime"
                  value="SELESAI"
                />
                <x-shared.badge
                  alpine="schedule.session_status === 'MENUNGGU PELUNASAN'"
                  size="sm"
                  variant="danger"
                  value="MENUNGGU PELUNASAN"
                />
                <x-shared.badge
                  alpine="schedule.session_status === 'MENUNGGU UPLOAD'"
                  size="sm"
                  variant="info"
                  value="MENUNGGU UPLOAD"
                />
              </x-backdoor.table.cell>

              @can('scheduleSession-view')
                {{-- aksi btn detail start --}}
                <x-backdoor.table.cell>
                  <x-shared.button
                    as="a"
                    x-bind:href="`{{ route('backdoor.session-schedule.list.show', ':booking_code') }}`.replace(':booking_code', schedule.booking_code)"
                    variant="outline"
                    size="sm"
                    value="Lihat Detail"
                    class="whitespace-nowrap"
                  />
                </x-backdoor.table.cell>
                {{-- aksi btn detail end --}}
              @endcan

            </tr>
          </template>
        </x-backdoor.table.container>
        {{-- table container end --}}

        <x-backdoor.table.pagination />

      </div>
      {{-- table card end --}}

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
