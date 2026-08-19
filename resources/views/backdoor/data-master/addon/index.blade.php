@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Data Master', 'url' => '#'],
      ['label' => 'Layanan Tambahan (Addon)', 'url' => ''],
  ];

  $tableHeaders = ['No', 'Nama Add-On', 'Harga', 'Tipe Input', 'Deskripsi', 'Status'];
  if (auth()->user()->canany(['addon-master-update', 'addon-master-delete'])) {
      $tableHeaders[] = 'Aksi';
  }
@endphp

<x-layouts.backdoor.index
  title="Kelola Layanan Tambahan"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/master-data/addon/Addon"
>

  <x-slot:content>
    <div
      x-data="Addon"
      x-cloak
      class="w-full space-y-6"
    >

      {{-- page header start --}}
      <x-backdoor.shared.page-header title="Layanan Tambahan (Add-ons)" />
      {{-- page header end --}}

      {{-- stats card start --}}
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <x-backdoor.shared.stats-card
          label="Total Add-Ons"
          x-text="state.totalAddons"
          suffix="Item"
        />
        <x-backdoor.shared.stats-card
          label="Total Add-Ons Aktif"
          x-text="state.totalActiveAddons"
          suffix="Item"
        />
      </div>
      {{-- stats card end --}}

      {{-- table card start --}}
      <div class="relative overflow-visible border border-stone-200 bg-stone-50 p-6">

        {{-- table header start --}}
        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari nama add-on..." />
          </x-slot:left>

          <x-slot:right>
            @can('addon-master-create')
              <x-backdoor.table.add-button
                x-on:click="openDrawer()"
                text="Tambah Add-On"
              />
            @endcan
          </x-slot:right>
        </x-backdoor.table.header>
        {{-- table header end --}}

        {{-- table container start --}}
        <x-backdoor.table.container :headers="$tableHeaders">
          <template
            x-for="(item, index) in table.data"
            x-bind:key="item.id"
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

              {{-- nama addon start --}}
              <x-backdoor.table.cell
                class="font-semibold text-stone-900"
                x-text="item.name"
              />
              {{-- nama addon end --}}

              {{-- harga start --}}
              <x-backdoor.table.cell
                class="whitespace-nowrap text-stone-700 font-mono text-xs font-semibold"
                x-text="formatRupiah(item.price)"
              />
              {{-- harga end --}}

              {{-- tipe input start --}}
              <x-backdoor.table.cell>
                <span
                  x-tooltip="item.has_quantity ? 'Counter: Klien dapat menambah/mengurangi jumlah item (contoh: Tambahan Orang)' : 'Checkbox: Klien hanya bisa memilih Ya/Tidak (jumlah tetap 1)'"
                  x-bind:class="item.has_quantity ?
                      'bg-stone-800 text-stone-50 border border-stone-800 cursor-help' :
                      'bg-stone-100 text-stone-600 border border-stone-200 cursor-help'"
                  class="flex flex-col items-center px-2.5 py-1 text-center text-xs font-semibold leading-tight"
                >
                  <span x-text="item.has_quantity ? 'Counter' : 'Checkbox'"></span>
                  <span
                    class="text-[10px] font-medium opacity-75"
                    x-text="item.has_quantity ? '(Multi)' : '(Single)'"
                  ></span>
                </span>
              </x-backdoor.table.cell>
              {{-- tipe input end --}}

              {{-- deskripsi start --}}
              <x-backdoor.table.cell>
                <span
                  x-tooltip="item.description"
                  x-bind:class="item.description ? 'cursor-help' : ''"
                  class="line-clamp-2 max-w-xs text-sm text-stone-600"
                  x-text="item.description || '—'"
                >
                </span>
              </x-backdoor.table.cell>
              {{-- deskripsi end --}}

              {{-- status toggle start --}}
              <x-backdoor.table.cell>
                <x-backdoor.shared.toggle
                  x-bind:checked="item.is_active"
                  x-bind:disabled="state.isLoading || {{ auth()->user()->can('addon-master-update') ? 'false' : 'true' }}"
                  x-bind:aria-label="'Status aktif ' + item.name"
                  x-on:change="toggleAddonStatus(item.id)"
                />
              </x-backdoor.table.cell>
              {{-- status toggle end --}}

              {{-- aksi start --}}
              @canany(['addon-master-update', 'addon-master-delete'])
                <x-backdoor.table.actions>
                  @can('addon-master-update')
                    <x-backdoor.table.action-item
                      color="text-yellow-600"
                      x-on:click="closeDropdown(); openEditDrawer(item)"
                      text="Edit"
                    />
                  @endcan
                  @can('addon-master-delete')
                    <x-backdoor.table.action-item
                      color="text-red-600"
                      x-bind:disabled="state.isLoading"
                      x-on:click="closeDropdown(); destroyAddon(item.id, item.name)"
                      text="Hapus"
                    />
                  @endcan
                </x-backdoor.table.actions>
              @endcanany
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
      @canany(['addon-master-create', 'addon-master-update'])
        <x-backdoor.data-master.addon.addon-drawer-form />
      @endcanany
      {{-- drawer form end --}}

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
