@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Manajemen Pemesanan', 'url' => ''],
  ];

  $tableHeaders = ['No', 'Kode Booking', 'Nama Klien', 'Jadwal Sesi', 'Paket & Varian', 'Total Bayar', 'Status'];
  if (auth()->user()->canany(['booking-management-view', 'booking-management-update'])) {
      $tableHeaders[] = 'Aksi';
  }
@endphp

<x-layouts.backdoor.index
  title="Manajemen Pemesanan"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/booking-management/index/Index"
>

  <x-slot:content>
    <div
      x-data="Index"
      x-cloak
      class="w-full space-y-6"
    >

      {{-- title section start --}}
      <x-backdoor.shared.page-header title="Manajemen Pemesanan" />
      {{-- title section end --}}

      {{-- stats section start --}}
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3">
        <x-backdoor.shared.stats-card
          label="Total Transaksi Sukses"
          x-text="formatRupiah(state.totalRevenue)"
        />
        <x-backdoor.shared.stats-card
          label="Booking Lunas"
          x-text="state.countSuccess"
          suffix="Sesi"
        />
        <x-backdoor.shared.stats-card
          label="DP Terbayar"
          x-text="state.countDpPaid"
          suffix="Sesi"
        />
      </div>
      {{-- stats section end --}}

      {{-- table card start --}}
      <div class="relative overflow-visible border border-stone-200 p-4">

        {{-- table header start --}}
        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari kode booking, nama..." />
          </x-slot:left>

          <x-slot:right>
            {{-- tambah booking button start --}}
            @can('booking-management-create')
              <x-backdoor.table.add-button
                as="a"
                :href="route('backdoor.booking-management.create')"
                text="Tambah Booking"
              />
            @endcan
            {{-- tambah booking button end --}}
          </x-slot:right>
        </x-backdoor.table.header>
        {{-- table header end --}}

        {{-- table container start --}}
        <x-backdoor.table.container :headers="$tableHeaders">
          <template
            x-for="(booking, index) in table.data"
            :key="booking.id"
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

              <x-backdoor.table.cell
                class="font-mono text-xs font-semibold uppercase text-stone-800 whitespace-nowrap"
                x-text="booking.booking_code"
              />

              <x-backdoor.table.cell
                class="text-sm font-semibold text-stone-900"
                x-text="booking.user_name || '-'"
              />

              <x-backdoor.table.cell class="text-sm">
                <p
                  class="font-medium text-stone-900 whitespace-nowrap"
                  x-text="booking.formatted_date"
                ></p>
                <p class="mt-1 flex items-center gap-1 text-xs text-stone-500 whitespace-nowrap">
                  <i class="ri-time-line"></i>
                  <span x-text="booking.formatted_time"></span>
                </p>
              </x-backdoor.table.cell>

              <x-backdoor.table.cell>
                <div class="flex flex-col text-sm">
                  <span
                    class="font-medium text-stone-900"
                    x-text="booking.package_name || ''"
                  ></span>
                  <span
                    class="text-xs text-stone-500"
                    x-text="booking.variant_name || ''"
                  ></span>
                </div>
              </x-backdoor.table.cell>

              <x-backdoor.table.cell class="text-sm font-semibold text-stone-800">
                <div class="flex flex-col gap-0.5">
                  <span
                    class="font-semibold text-stone-800 whitespace-nowrap"
                    x-text="booking.formatted_total_price"
                  ></span>
                  <span
                    class="text-xs font-medium text-stone-500 whitespace-nowrap"
                    x-text="booking.payment_detail_label"
                  ></span>
                </div>
              </x-backdoor.table.cell>

              <x-backdoor.table.cell>
                <div class="flex flex-col items-start gap-1">
                  <x-shared.badge
                    alpine="booking.status === 'Lunas' || booking.status === 'Selesai'"
                    size="xs"
                    variant="lime"
                    x-text="booking.status"
                  />
                  <x-shared.badge
                    alpine="booking.status === 'DP Terbayar'"
                    size="xs"
                    variant="secondary"
                    x-text="booking.status"
                  />
                  <x-shared.badge
                    alpine="booking.status === 'Menunggu'"
                    size="xs"
                    variant="warning"
                    x-text="booking.status"
                  />
                  <x-shared.badge
                    alpine="booking.status === 'Batal'"
                    size="xs"
                    variant="danger"
                    x-text="booking.status"
                  />
                </div>
              </x-backdoor.table.cell>

              @canany(['booking-management-view', 'booking-management-update'])
                <x-backdoor.table.actions>
                  {{-- lihat detail start --}}
                  @can('booking-management-view')
                    <x-backdoor.table.action-item
                      x-on:click="closeDropdown()"
                      x-bind:href="`{{ route('backdoor.booking-management.show', ':booking_code') }}`
                      .replace(':booking_code', booking.booking_code)"
                      color="text-blue-600"
                      text="Lihat Detail"
                    />
                  @endcan
                  {{-- lihat detail end --}}

                  @can('booking-management-update')
                    {{-- tandai lunas start --}}
                    <template x-if="booking.status === 'DP Terbayar'">
                      <x-backdoor.table.action-item
                        x-on:click="closeDropdown(); settle(booking.booking_code)"
                        color="text-lime-600"
                        text="Tandai Lunas"
                      />
                    </template>
                    {{-- tandai lunas end --}}

                    {{-- batalkan booking start --}}
                    <template x-if="booking.status !== 'Batal' && booking.status !== 'Selesai'">
                      <x-backdoor.table.action-item
                        x-on:click="closeDropdown(); cancel(booking.booking_code)"
                        color="text-red-600"
                        text="Batalkan"
                      />
                    </template>
                    {{-- batalkan booking end --}}
                  @endcan
                </x-backdoor.table.actions>
              @endcanany
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
