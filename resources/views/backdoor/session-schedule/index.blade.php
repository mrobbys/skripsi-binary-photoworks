@php
  use \Carbon\Carbon;
  use \App\Support\Formatter;

  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Jadwal Sesi', 'url' => '#'],
      ['label' => 'Daftar Jadwal', 'url' => ''],
  ];

  $jadwal_hari_ini = Formatter::dateId(Carbon::now(), 'l, d F Y');
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

      {{-- page header + tanggal hari ini --}}
      <div class="flex flex-col gap-1">
        <x-backdoor.shared.page-header title="Daftar Jadwal Sesi" />
        <p class="mt-2 text-sm text-stone-500">
          Data jadwal hari ini:
          <strong>{{ $jadwal_hari_ini }}</strong>
        </p>
      </div>

      {{-- stats section --}}
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

      {{-- table card --}}
      <div class="relative overflow-visible border border-stone-200 bg-stone-50 p-6">

        <x-backdoor.table.header>
          <x-slot:left>
            <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center">
              <x-backdoor.table.search placeholder="Cari nama klien, email, no. HP, kode booking, atau paket..." />

              <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-stone-400">
                  <i class="ri-calendar-line text-sm"></i>
                </span>
                <input
                  type="text"
                  x-ref="dateFilterInput"
                  placeholder="Filter tanggal..."
                  readonly
                  x-bind:disabled="table.isLoading"
                  class="w-full cursor-pointer border border-stone-300 bg-stone-50 py-2 pl-9 pr-9 text-sm text-stone-900 placeholder-stone-400 transition focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 sm:w-44"
                />
                <button
                  x-show="state.dateFilter"
                  x-cloak
                  x-on:click="clearDateFilter()"
                  x-bind:disabled="!state.dateFilter || table.isLoading"
                  type="button"
                  class="absolute inset-y-0 right-0 flex items-center pr-3 text-stone-400 transition hover:text-stone-600"
                >
                  <i class="ri-close-line text-lg"></i>
                </button>
              </div>

            </div>
          </x-slot:left>

          <x-slot:right>
            <x-shared.button
              as="a"
              href="{{ route('session-schedule.daily-report') }}"
              target="_blank"
              class="border border-stone-700 bg-stone-700 text-stone-50 hover:bg-stone-800 text-sm font-semibold tracking-wide"
            >
              <x-slot:iconLeft>
                <i class="ri-printer-line leading-none"></i>
              </x-slot:iconLeft>
              <span>Cetak Jadwal Hari Ini</span>
            </x-shared.button>
          </x-slot:right>
        </x-backdoor.table.header>

        <x-backdoor.table.container headers="No,Waktu Sesi,Nama Klien,Paket Foto,Background,Status Sesi,Aksi">
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
                class="text-stone-600"
                x-text="index + 1 + ((table.pagination.current_page - 1) * table.pagination.per_page)"
              />

              <x-backdoor.table.cell class="text-sm">
                <p
                  class="font-medium text-stone-900"
                  x-text="schedule.formatted_date"
                ></p>
                <p class="mt-1 flex items-center gap-1 text-xs text-stone-500">
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

              <x-backdoor.table.actions>
                {{-- lihat detail booking start --}}
                <x-backdoor.table.action-item
                  x-on:click="closeDropdown()"
                  x-bind:href="`{{ route('backdoor.session-schedule.list.show', ':booking_code') }}`
                  .replace(':booking_code', schedule.booking_code)"
                  color="text-blue-600"
                  text="Lihat Detail"
                />
                {{-- lihat detail booking end --}}
              </x-backdoor.table.actions>

            </tr>
          </template>
        </x-backdoor.table.container>

        <x-backdoor.table.pagination />

      </div>
      {{-- table card end --}}

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
