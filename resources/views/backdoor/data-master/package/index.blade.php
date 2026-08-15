@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Data Master', 'url' => '#'],
      ['label' => 'Paket Studio', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Kelola Paket & Varian"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/master-data/package/Package"
>

  <x-slot:content>
    <div
      x-data="Package"
      x-cloak
      class="w-full space-y-6"
    >

      {{-- page header start --}}
      <x-backdoor.shared.page-header title="Kelola Paket & Varian" />
      {{-- page header end --}}

      {{-- stats card start --}}
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        {{-- stats total paket start --}}
        <x-backdoor.shared.stats-card
          label="Total Paket"
          x-text="state.totalPackages"
          suffix="Paket"
        />
        {{-- stats total paket end --}}

        {{-- stats total paket aktif start --}}
        <x-backdoor.shared.stats-card
          label="Total Paket Aktif"
          x-text="state.totalActivePackages"
          suffix="Paket"
        />
        {{-- stats total paket aktif end --}}

        {{-- stats total varian aktif start --}}
        <x-backdoor.shared.stats-card
          label="Total Varian Aktif"
          x-text="state.totalActiveVariants"
          suffix="Varian"
        />
        {{-- stats total varian aktif end --}}
      </div>
      {{-- stats card end --}}

      {{-- table card start --}}
      <div class="relative overflow-visible border border-stone-200 bg-stone-50 p-6">

        {{-- table header start --}}
        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari nama paket, kategori..." />
          </x-slot:left>

          <x-slot:right>
            <x-backdoor.table.add-button
              x-on:click="openDrawer()"
              text="Tambah Paket"
            />
          </x-slot:right>
        </x-backdoor.table.header>
        {{-- table header end --}}

        {{-- table container start --}}
        <x-backdoor.table.container headers="No,Nama Paket & Kategori,Jumlah Varian,Rentang Harga,Status,Aksi">
          <template
            x-for="(item, index) in table.data"
            x-bind:key="item.slug"
          >
            <tr
              class="border-b border-stone-200 transition hover:bg-stone-100"
              x-show="!table.isLoading"
              x-cloak
            >

              {{-- no start --}}
              <x-backdoor.table.cell
                class="text-stone-600"
                x-text="(table.pagination.current_page - 1) * table.pagination.per_page + index + 1"
              />
              {{-- no end --}}

              {{-- nama paket & kategori start --}}
              <x-backdoor.table.cell>
                <div class="flex flex-col gap-0.5">
                  <span
                    class="font-bold text-stone-900"
                    x-text="item.name"
                  ></span>
                  <span class="font-medium text-[10px] uppercase tracking-wider text-stone-500">
                    CATEGORY: <span x-text="item.category.name"></span>
                  </span>
                </div>
              </x-backdoor.table.cell>
              {{-- nama paket & kategori end --}}

              {{-- jumlah varian start --}}
              <x-backdoor.table.cell class="font-semibold text-stone-900">
                <span x-text="item.variants_count"></span>
                <span>Varian</span>
              </x-backdoor.table.cell>
              {{-- jumlah varian end --}}

              {{-- rentang harga start --}}
              <x-backdoor.table.cell class="font-semibold text-stone-900 font-mono text-xs">
                <span
                  class="whitespace-nowrap"
                  x-text="formatRupiah(item.price_min)"
                ></span>
                <span>-</span>
                <span
                  class="whitespace-nowrap"
                  x-text="formatRupiah(item.price_max)"
                ></span>
              </x-backdoor.table.cell>
              {{-- rentang harga end --}}

              {{-- status toggle start --}}
              <x-backdoor.table.cell>
                <x-backdoor.shared.toggle
                  x-bind:checked="item.is_active"
                  x-bind:disabled="state.isLoading"
                  x-bind:aria-label="'Status aktif ' + item.name"
                  x-on:change="togglePackageStatus(item.slug, $event)"
                />
              </x-backdoor.table.cell>
              {{-- status toggle end --}}

              {{-- aksi start --}}
              <x-backdoor.table.actions>
                <x-backdoor.table.action-item
                  color="text-blue-600"
                  x-bind:href="`{{ route('backdoor.data-master.package.show', ':slug') }}`.replace(':slug', item.slug)"
                  text="Detail"
                />
                <x-backdoor.table.action-item
                  color="text-yellow-600"
                  x-on:click="closeDropdown(); openEditDrawer(item)"
                  text="Edit"
                />
                <x-backdoor.table.action-item
                  color="text-red-600"
                  x-bind:disabled="state.isLoading"
                  x-on:click="closeDropdown(); destroyPackage(item)"
                  text="Hapus"
                />
              </x-backdoor.table.actions>
              {{-- aksi end --}}

            </tr>
          </template>
        </x-backdoor.table.container>
        {{-- table container end --}}

        {{-- pagination start --}}
        <x-backdoor.table.pagination />
        {{-- pagination end --}}

      </div>
      {{-- table card end --}}

      {{-- drawer form start --}}
      <x-backdoor.data-master.package.package-drawer-form :categories="$categories" />
      {{-- drawer form end --}}

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
