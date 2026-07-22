@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Data Klien', 'url' => route('backdoor.client-data.index')],
      ['label' => $client->name, 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Detail Klien — {{ $client->name }}"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/client-data/show/Show"
>
  <x-slot:content>
    <div
      id="show-root"
      data-user-uuid="{{ $client->uuid }}"
      x-data="Show"
      x-cloak
      class="w-full space-y-6"
    >

      <div class="space-y-6">
        <a
          href="{{ route('backdoor.client-data.index') }}"
          class="inline-flex cursor-pointer items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-stone-500 transition hover:text-stone-950 md:text-xs"
        >
          <i
            class="ri-arrow-left-line"
            aria-hidden="true"
          ></i>
          <span>Kembali Ke Data Klien</span>
        </a>
        <x-backdoor.shared.page-header title="Detail Klien" />
      </div>

      {{-- Profile & Bento Grid Stats --}}
      <div class="border border-stone-200 bg-stone-50 p-6">

        {{-- Atas: Info Dasar --}}
        <div class="mb-6 flex flex-col gap-6 sm:flex-row sm:items-start">
          <div class="flex flex-1 flex-col gap-1">
            <h2 class="text-xl font-bold text-stone-900">{{ $client->name }}</h2>
            <p class="text-sm text-stone-500">Bergabung sejak {{ $client->joined_at }}</p>
            <div class="mt-2 flex items-center gap-4 text-sm font-medium text-stone-700">
              <span class="flex items-center gap-1.5"><i class="ri-mail-line text-lg text-stone-400"></i>
                {{ $client->email }}</span>
              <span class="flex items-center gap-1.5"><i class="ri-phone-line text-lg text-stone-400"></i>
                {{ $client->phone ?? '-' }}</span>
            </div>
          </div>
        </div>

        {{-- Bawah: Bento Grid Statistik --}}
        <div class="grid grid-cols-1 gap-4 border-t border-stone-200 pt-6 sm:grid-cols-2 lg:grid-cols-4">

          {{-- Total Keseluruhan --}}
          <div class="flex flex-col justify-center bg-stone-100 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Total Booking</p>
            <p class="mt-1 text-3xl font-bold text-stone-900">{{ $client->total_all }} <span
                class="text-sm font-normal text-stone-500"
              >Sesi</span></p>
          </div>

          {{-- Menunggu & DP --}}
          <div class="flex flex-col justify-center gap-3 border-l-0 border-stone-200 pl-0 sm:border-l sm:pl-4">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-stone-600">Menunggu Pembayaran</span>
              <span
                class="border border-yellow-200 bg-yellow-50 px-2 py-0.5 text-xs font-bold text-yellow-600">{{ $client->total_pending }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-stone-600">DP Terbayar</span>
              <span
                class="border border-stone-300 bg-stone-200 px-2 py-0.5 text-xs font-bold text-stone-700">{{ $client->total_dp }}</span>
            </div>
          </div>

          {{-- Lunas & Selesai --}}
          <div
            class="flex flex-col justify-center gap-3 border-l-0 border-stone-200 pl-0 sm:border-l sm:pl-4 lg:border-l"
          >
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-stone-600">Lunas</span>
              <span
                class="border border-green-200 bg-green-50 px-2 py-0.5 text-xs font-bold text-lime-600">{{ $client->total_success }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-stone-600">Sesi Selesai</span>
              <span
                class="border border-green-200 bg-green-50 px-2 py-0.5 text-xs font-bold text-lime-600">{{ $client->total_done }}</span>
            </div>
          </div>

          {{-- Batal --}}
          <div class="flex flex-col justify-center gap-3 border-l-0 border-stone-200 pl-0 sm:border-l sm:pl-4">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-stone-600">Dibatalkan</span>
              <span
                class="border border-red-200 bg-red-50 px-2 py-0.5 text-xs font-bold text-red-600">{{ $client->total_cancel }}</span>
            </div>
          </div>

        </div>
      </div>

      {{-- Tabel Riwayat Booking --}}
      <div class="relative overflow-visible border border-stone-200 bg-stone-50 p-6">
        <div class="mb-4">
          <h3 class="text-base font-semibold text-stone-800">Riwayat Booking</h3>
          <p class="mt-0.5 text-sm text-stone-500">Semua data pemesanan yang pernah dilakukan oleh klien ini.</p>
        </div>

        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari kode booking atau nama paket..." />
          </x-slot:left>
        </x-backdoor.table.header>

        <x-backdoor.table.container headers="No,Kode Booking,Tanggal Sesi,Paket Foto,Status,Total Bayar,Aksi">
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
                class="text-stone-600"
                x-text="index + 1 + ((table.pagination.current_page - 1) * table.pagination.per_page)"
              />
              <x-backdoor.table.cell
                class="font-mono text-xs font-semibold uppercase text-stone-800"
                x-text="booking.booking_code"
              />
              <x-backdoor.table.cell
                class="text-sm text-stone-700"
                x-text="booking.formatted_date"
              />

              <x-backdoor.table.cell class="text-sm">
                <p
                  class="font-medium text-stone-900"
                  x-text="booking.package_name"
                ></p>
                <p
                  class="mt-0.5 text-xs text-stone-500"
                  x-text="booking.variant_name"
                ></p>
              </x-backdoor.table.cell>

              <x-backdoor.table.cell>
                <x-shared.badge
                  alpine="booking.status === 'Lunas' || booking.status === 'Selesai'"
                  variant="lime"
                  x-text="booking.status"
                />
                <x-shared.badge
                  alpine="booking.status === 'DP Terbayar'"
                  variant="secondary"
                  x-text="booking.status"
                />
                <x-shared.badge
                  alpine="booking.status === 'Menunggu'"
                  variant="warning"
                  x-text="booking.status"
                />
                <x-shared.badge
                  alpine="booking.status === 'Batal'"
                  variant="danger"
                  x-text="booking.status"
                />
              </x-backdoor.table.cell>

              <x-backdoor.table.cell
                class="font-semibold text-stone-800"
                x-text="booking.formatted_price"
              />

              <x-backdoor.table.actions>
                <x-backdoor.table.action-item
                  x-on:click="closeDropdown()"
                  x-bind:href="`{{ route('backdoor.booking-management.show', ':booking_code') }}`.replace(':booking_code', booking
                      .booking_code)"
                  color="text-blue-600"
                  text="Lihat Detail Booking"
                />
              </x-backdoor.table.actions>

            </tr>
          </template>
        </x-backdoor.table.container>

        <x-backdoor.table.pagination />
      </div>

    </div>
  </x-slot:content>
</x-layouts.backdoor.index>
