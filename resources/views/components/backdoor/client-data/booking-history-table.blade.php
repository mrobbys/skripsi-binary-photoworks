<div class="relative overflow-visible border border-stone-200 p-4">
  <div class="mb-4">
    <h3 class="text-base font-semibold text-stone-800">Riwayat Booking</h3>
    <p class="mt-0.5 text-sm text-stone-500">Semua data pemesanan yang pernah dilakukan oleh klien ini.</p>
  </div>

  <x-backdoor.table.header>
    <x-slot:left>
      <x-backdoor.table.search placeholder="Cari kode booking, paket..." />
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
