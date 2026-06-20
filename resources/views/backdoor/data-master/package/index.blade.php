@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard')],
      ['label' => 'Kelola Paket & Varian', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Kelola Paket & Varian"
  :breadcrumbs="$breadcrumbs"
  js-module="master-data/package/Package">
  >

  <x-slot:content>
    <div
      x-data="Package"
      x-init="state.totalPackages = {{ $totalPackages }};
      state.totalActivePackages = {{ $totalActivePackages }};
      state.totalActiveVariants = {{ $totalActiveVariants }};"
      class="w-full space-y-6">

      {{-- title section start --}}
      <x-backdoor.shared.page-header title="Kelola Paket & Varian" />
      {{-- title section end --}}

      <div class="grid grid-cols-4 gap-6">
        {{-- stats total paket start --}}
        <x-backdoor.shared.stats-card
          label="Total Paket"
          x-text="state.totalPackages"
          suffix="Paket" />
        {{-- stats total paket end --}}

        {{-- stats total paket aktif start --}}
        <x-backdoor.shared.stats-card
          label="Total Paket Aktif"
          x-text="state.totalActivePackages"
          suffix="Paket" />
        {{-- stats total paket aktif end --}}

        {{-- stats total varian aktif start --}}
        <x-backdoor.shared.stats-card
          label="Total Varian Aktif"
          x-text="state.totalActiveVariants"
          suffix="Varian" />
        {{-- stats total varian aktif end --}}
      </div>

      {{-- table start --}}
      <div class="bg-stone-50 border border-stone-200 p-6 relative overflow-visible">

        {{-- table header start --}}
        <x-backdoor.table.header placeholder="Cari nama paket atau kategori...">
          <x-backdoor.table.add-button
            x-on:click="openDrawer()"
            text="Tambah Paket" />
        </x-backdoor.table.header>
        {{-- table header end --}}

        {{-- table container start --}}
        <x-backdoor.table.container headers="No, Nama Paket & Kategori, Jumlah Varian, Rentang Harga, Status, Aksi">
          <template
            x-for="(item, index) in table.data"
            x-bind:key="item.slug">
            <tr
              class="hover:bg-stone-100 border-b border-stone-200 transition"
              x-show="!table.isLoading"
              x-cloak>

              {{-- No start --}}
              <x-backdoor.table.cell
                class="text-stone-600"
                x-text="(table.pagination.current_page - 1) * table.pagination.per_page + index + 1" />
              {{-- No end --}}

              {{-- Nama Paket & Kategori start --}}
              <x-backdoor.table.cell>
                <div class="flex flex-col gap-0.5">
                  <span class="font-bold text-stone-900" x-text="item.name"></span>
                  <span class="text-[10px] uppercase tracking-wider text-stone-500 font-medium">
                    CATEGORY: <span x-text="item.category.name"></span>
                  </span>
                </div>
              </x-backdoor.table.cell>
              {{-- Nama Paket & Kategori end --}}

              {{-- Jumlah Varian start --}}
              <x-backdoor.table.cell
                class="font-semibold text-stone-900">
                <span x-text="item.variants_count"></span>
                <span>Varian</span>
              </x-backdoor.table.cell>
              {{-- Jumlah Varian end --}}

              {{-- Rentang Harga start --}}
              <x-backdoor.table.cell
                class="font-semibold text-stone-900">
                <span class="whitespace-nowrap" x-text="formatRupiah(item.price_min)"></span>
                <span>-</span>
                <span class="whitespace-nowrap" x-text="formatRupiah(item.price_max)"></span>
              </x-backdoor.table.cell>
              {{-- Rentag Harga end --}}

              {{-- Status toggle start --}}
              <x-backdoor.table.cell>
                <x-backdoor.shared.toggle
                  x-bind:checked="item.is_active"
                  x-bind:disabled="state.isLoading"
                  x-on:change="togglePackageStatus(item.slug, $event)" />
              </x-backdoor.table.cell>
              {{-- Status toggle end --}}

              {{-- Aksi start --}}
              <x-backdoor.table.actions>
                <x-backdoor.table.action-item
                  color="text-yellow-600"
                  x-on:click="closeDropdown(); editPackage(item)"
                  text="Edit" />
                <x-backdoor.table.action-item
                  color="text-red-600"
                  x-on:click="closeDropdown(); destroyPackage(item)"
                  text="Hapus" />
              </x-backdoor.table.actions>
              {{-- Aksi end --}}

            </tr>
          </template>
        </x-backdoor.table.container>
        {{-- table container end --}}

        {{-- pagination start --}}
        <x-backdoor.table.pagination />
        {{-- pagination end --}}

      </div>
      {{-- table end --}}

      {{-- drawer form ( create / update ) start --}}
      <x-backdoor.data-master.package.package-drawer-form :categories="$categories" />
      {{-- drawer form ( create / update ) end --}}

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
