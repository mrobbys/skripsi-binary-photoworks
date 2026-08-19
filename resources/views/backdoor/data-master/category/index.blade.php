@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Data Master', 'url' => '#'],
      ['label' => 'Kategori Foto', 'url' => ''],
  ];

  $tableHeaders = ['No', 'Kode Kategori', 'Nama Kategori', 'Status'];
  if (auth()->user()->canany(['category-master-update', 'category-master-delete'])) {
      $tableHeaders[] = 'Aksi';
  }
@endphp

<x-layouts.backdoor.index
  title="Kategori Foto"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/master-data/category/Category"
>

  <x-slot:content>
    <div
      x-data="Category"
      x-cloak
      class="w-full space-y-6"
    >
      {{-- title section start --}}
      <x-backdoor.shared.page-header title="Kategori Foto" />
      {{-- title section end --}}

      {{-- stats card start --}}
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <x-backdoor.shared.stats-card
          label="Total Kategori"
          x-text="state.totalCategory"
          suffix="Kategori"
        />

        <x-backdoor.shared.stats-card
          label="Total Kategori Aktif"
          x-text="state.activeCount"
          suffix="Kategori"
        />
      </div>
      {{-- stats card end --}}

      {{-- table card start --}}
      <div class="relative overflow-visible border border-stone-200 p-4">

        {{-- table header start --}}
        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari kode, nama kategori..." />
          </x-slot:left>

          <x-slot:right>
            @can('category-master-create')
              <x-backdoor.table.add-button
                x-on:click="openDrawer()"
                text="Tambah Kategori"
              />
            @endcan
          </x-slot:right>
        </x-backdoor.table.header>
        {{-- table header end --}}

        {{-- table container start --}}
        <x-backdoor.table.container :headers="$tableHeaders">
          <template
            x-for="(item, index) in table.data"
            x-bind:key="item.slug"
          >
            <tr
              class="border-b border-stone-200 transition hover:bg-stone-100"
              x-show="!table.isLoading"
              x-cloak
            >
              {{-- No start --}}
              <x-backdoor.table.cell
                class="text-stone-600"
                x-text="(table.pagination.current_page - 1) * table.pagination.per_page + index + 1"
              />
              {{-- No end --}}

              {{-- Kode Kategori start --}}
              <x-backdoor.table.cell
                class="font-semibold uppercase text-stone-900"
                x-text="item.category_code"
              />
              {{-- Kode Kategori end --}}

              {{-- Nama Kategori start --}}
              <x-backdoor.table.cell
                class="font-semibold text-stone-900 min-w-32"
                x-text="item.name"
              />
              {{-- Nama Kategori end --}}

              {{-- Status toggle start --}}
              <x-backdoor.table.cell>
                <x-backdoor.shared.toggle
                  x-bind:checked="item.is_active"
                  x-bind:disabled="state.isLoading || {{ auth()->user()->can('category-master-update') ? 'false' : 'true' }}"
                  x-on:change="toggleCategoryStatus(item.slug, $event)"
                />
              </x-backdoor.table.cell>
              {{-- Status toggle end --}}

              {{-- Aksi start --}}
              @canany(['category-master-update', 'category-master-delete'])
                <x-backdoor.table.actions>
                  @can('category-master-update')
                    <x-backdoor.table.action-item
                      color="text-yellow-600"
                      x-on:click="closeDropdown(); editCategory(item)"
                      text="Edit"
                    />
                  @endcan
                  @can('category-master-delete')
                    <x-backdoor.table.action-item
                      color="text-red-600"
                      x-on:click="closeDropdown(); destroyCategory(item)"
                      text="Hapus"
                    />
                  @endcan
                </x-backdoor.table.actions>
              @endcanany
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
      @canany(['category-master-create', 'category-master-update'])
        <x-backdoor.data-master.category.category-drawer-form />
      @endcanany
      {{-- drawer form (create / update) end --}}
    </div>
  </x-slot:content>
</x-layouts.backdoor.index>
