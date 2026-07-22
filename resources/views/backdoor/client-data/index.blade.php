@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Data Klien', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Data Klien"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/client-data/index/Index"
>

  <x-slot:content>
    <div
      x-data="Index"
      x-cloak
      class="w-full space-y-6"
    >

      {{-- Page Header --}}
      <x-backdoor.shared.page-header title="Data Klien" />

      {{-- Stats Cards --}}
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <x-backdoor.shared.stats-card
          label="Total Klien Terdaftar"
          value="{{ $totalClients }}"
          suffix="Klien"
        />
        <x-backdoor.shared.stats-card
          label="Klien Baru Bulan Ini"
          value="{{ $newClientsThisMonth }}"
          suffix="Klien"
        />
      </div>
      {{-- Stats Cards End --}}

      {{-- Table Card --}}
      <div class="relative overflow-visible border border-stone-200 bg-stone-50 p-6">

        {{-- Table Header (search only, no right-side button) --}}
        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari nama, email, atau nomor HP..." />
          </x-slot:left>
        </x-backdoor.table.header>

        {{-- Table --}}
        <x-backdoor.table.container headers="No,Nama Klien,Email,Nomor HP,Tgl. Bergabung,Total Booking,Aksi">
          <template
            x-for="(client, index) in table.data"
            :key="client.uuid"
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
                class="font-semibold text-stone-900"
                x-text="client.name"
              />

              <x-backdoor.table.cell
                class="text-stone-600"
                x-text="client.email"
              />

              <x-backdoor.table.cell
                class="text-stone-600"
                x-text="client.phone ?? '-'"
              />

              <x-backdoor.table.cell
                class="text-sm text-stone-500"
                x-text="client.joined_at"
              />

              <x-backdoor.table.cell>
                <span
                  class="font-semibold text-stone-800"
                  x-text="client.bookings_count"
                ></span>
                <span class="text-xs text-stone-500"> Sesi</span>
              </x-backdoor.table.cell>

              <x-backdoor.table.actions>
                <x-backdoor.table.action-item
                  x-on:click="closeDropdown()"
                  x-bind:href="`{{ route('backdoor.client-data.show', ':uuid') }}`
                  .replace(':uuid', client.uuid)"
                  color="text-blue-600"
                  text="Lihat Detail"
                />
              </x-backdoor.table.actions>

            </tr>
          </template>
        </x-backdoor.table.container>

        <x-backdoor.table.pagination />

      </div>
      {{-- Table Card End --}}

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
