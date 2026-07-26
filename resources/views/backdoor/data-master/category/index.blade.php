@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Data Master', 'url' => '#'],
      ['label' => 'Kategori Foto', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Kategori Foto"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/master-data/category/Category">

  <x-slot:content>
    <div
      x-data="Category"
      class="w-full space-y-6">
      {{-- title section start --}}
      <x-backdoor.shared.page-header title="Kategori Foto" />
      {{-- title section end --}}

      {{-- stats card start --}}
      <x-backdoor.shared.stats-card
        label="Total Kategori Aktif"
        x-text="state.activeCount"
        suffix="Kategori" />
      {{-- stats card end --}}

      {{-- table card start --}}
      <div class="bg-stone-50 border border-stone-200 p-6 relative overflow-visible">

        {{-- table header start --}}
        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari Kategori..." />
          </x-slot:left>

          <x-slot:right>
            <x-backdoor.table.add-button
              x-on:click="openDrawer()"
              text="Tambah Kategori" />
          </x-slot:right>
        </x-backdoor.table.header>
        {{-- table header end --}}

        {{-- table container start --}}
        <x-backdoor.table.container headers="No,Kode Kategori,Nama Kategori,Status,Aksi">
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

              {{-- Kode Kategori start --}}
              <x-backdoor.table.cell
                class="font-semibold text-stone-900 uppercase"
                x-text="item.category_code" />
              {{-- Kode Kategori end --}}

              {{-- Nama Kategori start --}}
              <x-backdoor.table.cell
                class="font-semibold text-stone-900"
                x-text="item.name" />
              {{-- Nama Kategori end --}}

              {{-- Status toggle start --}}
              <x-backdoor.table.cell>
                <x-backdoor.shared.toggle
                  x-bind:checked="item.is_active"
                  x-bind:disabled="state.isLoading"
                  x-on:change="toggleCategoryStatus(item.slug, $event)" />
              </x-backdoor.table.cell>
              {{-- Status toggle end --}}

              {{-- Aksi start --}}
              <x-backdoor.table.actions>
                <x-backdoor.table.action-item
                  color="text-yellow-600"
                  x-on:click="closeDropdown(); editCategory(item)"
                  text="Edit" />
                <x-backdoor.table.action-item
                  color="text-red-600"
                  x-on:click="closeDropdown(); destroyCategory(item)"
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
      {{-- table card end --}}

      {{-- drawer form (create / update) start --}}
      <x-backdoor.data-master.category.category-drawer-form />
      {{-- drawer form (create / update) end --}}
    </div>
  </x-slot:content>
</x-layouts.backdoor.index>
