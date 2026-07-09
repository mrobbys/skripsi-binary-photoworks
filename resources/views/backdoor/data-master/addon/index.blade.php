@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard')],
      ['label' => 'Data Master', 'url' => '#'],
      ['label' => 'Layanan Tambahan', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Kelola Layanan Tambahan"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/master-data/addon/Addon">

  <x-slot:content>
    <div
      x-data="Addon"
      x-init="state.totalAddons = {{ $totalAddons }};
      state.totalActiveAddons = {{ $totalActiveAddons }};"
      class="w-full space-y-6">

      {{-- Page Header --}}
      <x-backdoor.shared.page-header title="Layanan Tambahan (Add-ons)" />

      {{-- Stats Card --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <x-backdoor.shared.stats-card
          label="Total Add-Ons"
          x-text="state.totalAddons"
          suffix="Item" />
        <x-backdoor.shared.stats-card
          label="Total Add-Ons Aktif"
          x-text="state.totalActiveAddons"
          suffix="Item" />
      </div>

      {{-- Table Card --}}
      <div class="bg-stone-50 border border-stone-200 p-6 relative overflow-visible">

        {{-- Table Header (Search + Add Button) --}}
        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari nama add-on..." />
          </x-slot:left>

          <x-slot:right>
            <x-backdoor.table.add-button
              x-on:click="openDrawer()"
              text="Tambah Add-On" />
          </x-slot:right>
        </x-backdoor.table.header>

        {{-- Table --}}
        <x-backdoor.table.container headers="No,Nama Add-On,Harga,Tipe Input,Deskripsi,Status,Aksi">
          <template
            x-for="(item, index) in table.data"
            x-bind:key="item.id">
            <tr
              class="hover:bg-stone-100 border-b border-stone-200 transition"
              x-show="!table.isLoading"
              x-cloak>

              {{-- No --}}
              <x-backdoor.table.cell
                class="text-stone-600"
                x-text="(table.pagination.current_page - 1) * table.pagination.per_page + index + 1" />

              {{-- Nama Add-On --}}
              <x-backdoor.table.cell
                class="font-semibold text-stone-900"
                x-text="item.name" />

              {{-- Harga --}}
              <x-backdoor.table.cell
                class="text-stone-700 whitespace-nowrap"
                x-text="formatRupiah(item.price)" />

              {{-- Tipe Input --}}
              <x-backdoor.table.cell>
                <span
                  x-on:mouseenter="if (!$el._tippy) window.tippy($el, {
                      content: item.has_quantity ?
                          'Counter: Klien dapat menambah/mengurangi jumlah item (contoh: Tambahan Orang)' :
                          'Checkbox: Klien hanya bisa memilih Ya/Tidak (jumlah tetap 1)',
                      showOnCreate: true,
                      placement: 'top'
                  })"
                  x-bind:class="item.has_quantity ?
                      'bg-stone-800 text-stone-50 border border-stone-800 cursor-help' :
                      'bg-stone-100 text-stone-600 border border-stone-200 cursor-help'"
                  class="flex flex-col items-center text-center text-xs font-semibold px-2.5 py-1 leading-tight">
                  <span x-text="item.has_quantity ? 'Counter' : 'Checkbox'"></span>
                  <span class="text-[10px] opacity-75 font-medium"
                    x-text="item.has_quantity ? '(Multi)' : '(Single)'"></span>
                </span>
              </x-backdoor.table.cell>

              {{-- Deskripsi --}}
              <x-backdoor.table.cell>
                <span
                  x-on:mouseenter="if (!$el._tippy && item.description) window.tippy($el, { content: item.description, showOnCreate: true, placement: 'top' })"
                  class="text-sm text-stone-600 line-clamp-2 max-w-xs cursor-help"
                  x-text="item.description || '—'">
                </span>
              </x-backdoor.table.cell>

              {{-- Status Toggle --}}
              <x-backdoor.table.cell>
                <x-backdoor.shared.toggle
                  x-bind:checked="item.is_active"
                  x-bind:disabled="state.isLoading"
                  x-on:change="toggleAddonStatus(item.id, item.is_active)" />
              </x-backdoor.table.cell>

              {{-- Aksi --}}
              <x-backdoor.table.actions>
                <x-backdoor.table.action-item
                  color="text-yellow-600"
                  x-on:click="closeDropdown(); openEditDrawer(item)"
                  text="Edit" />
                <x-backdoor.table.action-item
                  color="text-red-600"
                  x-bind:disabled="state.isLoading"
                  x-on:click="closeDropdown(); destroyAddon(item.id, item.name)"
                  text="Hapus" />
              </x-backdoor.table.actions>

            </tr>
          </template>
        </x-backdoor.table.container>

        {{-- Pagination --}}
        <x-backdoor.table.pagination />

      </div>

      {{-- Drawer Form --}}
      <x-backdoor.data-master.addon.addon-drawer-form />

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
