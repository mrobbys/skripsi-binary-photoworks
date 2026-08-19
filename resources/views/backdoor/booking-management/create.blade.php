@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Manajemen Pemesanan', 'url' => route('backdoor.booking-management.index')],
      ['label' => 'Tambah Booking Manual', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Tambah Booking Manual"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/booking-management/create/Create"
>

  <x-slot:content>
    <div
      x-data="Create"
      x-init="init(
          {{ Js::from($packages) }},
          {{ Js::from($addons) }}
      )"
      x-cloak
      class="pb-16"
    >

      {{-- header section start --}}
      <x-backdoor.shared.page-header-back
        :href="route('backdoor.booking-management.index')"
        title="Tambah Booking Manual"
        backLabel="Kembali Ke Manajemen Pemesanan"
        class="pb-6"
      />
      {{-- header section end --}}

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- kolom kiri: formulir data sesi & add-ons start --}}
        <div class="space-y-5 lg:col-span-2">
          {{-- informasi klien start --}}
          <x-backdoor.booking-management.create.client-selection :users="$users" />
          {{-- informasi klien end --}}

          {{-- detail paket start --}}
          <x-backdoor.booking-management.create.package-selection
            :packages="$packages"
            :backgrounds="$backgrounds"
          />
          {{-- detail paket end --}}

          {{-- jadwal sesi start --}}
          <x-backdoor.booking-management.create.schedule-selection />
          {{-- jadwal sesi end --}}

          {{-- layanan tambahan start --}}
          <x-backdoor.booking-management.create.addon-selection />
          {{-- layanan tambahan end --}}
        </div>
        {{-- kolom kiri: formulir data sesi & add-ons end --}}

        {{-- kolom kanan: status, ringkasan, notifikasi & aksi start --}}
        <div class="space-y-4">
          {{-- status awal booking start --}}
          <x-backdoor.booking-management.create.status-selection />
          {{-- status awal booking end --}}

          {{-- ringkasan pesanan start --}}
          <x-backdoor.booking-management.create.order-summary />
          {{-- ringkasan pesanan end --}}

          {{-- notifikasi wa start --}}
          <x-backdoor.booking-management.create.notification-toggle />
          {{-- notifikasi wa end --}}

          {{-- tombol simpan start --}}
          <x-shared.button
            type="button"
            x-on:click="submit()"
            x-bind:disabled="isSubmitDisabled()"
            variant="charcoal"
            size="md"
            class="w-full"
          >
            <span x-text="state.isLoading ? 'Menyimpan...' : 'Simpan Pesanan'"></span>
          </x-shared.button>
          {{-- tombol simpan end --}}

          {{-- tombol batal start --}}
          <x-shared.button
            as="a"
            :href="route('backdoor.booking-management.index')"
            variant="secondary"
            size="md"
            class="w-full"
            value="Batal"
          />
          {{-- tombol batal end --}}
        </div>
        {{-- kolom kanan: status, ringkasan, notifikasi & aksi end --}}

      </div>

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
