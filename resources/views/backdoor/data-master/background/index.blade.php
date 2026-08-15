@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Data Master', 'url' => '#'],
      ['label' => 'Background', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Kelola Background"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/master-data/background/Background"
>

  <x-slot:content>
    <div
      x-data="Background"
      x-cloak
      class="w-full space-y-6"
    >

      {{-- page header start --}}
      <x-backdoor.shared.page-header title="Kelola Background" />
      {{-- page header end --}}

      {{-- stats card start --}}
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <x-backdoor.shared.stats-card
          label="Total Background"
          x-text="state.totalBackgrounds"
          suffix="Background"
        />
        <x-backdoor.shared.stats-card
          label="Total Background Aktif"
          x-text="state.totalActiveBackgrounds"
          suffix="Background"
        />
      </div>
      {{-- stats card end --}}

      {{-- table card start --}}
      <div class="relative overflow-visible border border-stone-200 bg-stone-50 p-6">

        {{-- table header start --}}
        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari nama background..." />
          </x-slot:left>

          <x-slot:right>
            <x-backdoor.table.add-button
              x-on:click="openDrawer()"
              text="Tambah Background"
            />
          </x-slot:right>
        </x-backdoor.table.header>
        {{-- table header end --}}

        {{-- table container start --}}
        <x-backdoor.table.container headers="No,Preview,Nama Background,Deskripsi,Status,Aksi">
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

              {{-- preview gambar start --}}
              <x-backdoor.table.cell>
                <template x-if="item.image_url">
                  <button
                    type="button"
                    x-on:click="openImagePreview(item.original_url, item.name)"
                    class="group block h-16 w-16 overflow-hidden border border-stone-200 transition hover:border-stone-400 cursor-zoom-in"
                    title="Klik untuk preview"
                  >
                    <img
                      :src="item.image_url"
                      :alt="'Preview ' + item.name"
                      class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                    />
                  </button>
                </template>
                <template x-if="!item.image_url">
                  <div class="flex h-16 w-16 items-center justify-center border border-dashed border-stone-300 bg-stone-100">
                    <i
                      class="ri-image-line text-xl text-stone-400"
                      aria-hidden="true"
                    ></i>
                  </div>
                </template>
              </x-backdoor.table.cell>
              {{-- preview gambar end --}}

              {{-- nama start --}}
              <x-backdoor.table.cell
                class="font-semibold text-stone-900"
                x-text="item.name"
              />
              {{-- nama end --}}

              {{-- deskripsi start --}}
              <x-backdoor.table.cell>
                <span
                  x-tooltip="item.description"
                  x-bind:class="item.description ? 'cursor-help' : ''"
                  class="line-clamp-2 max-w-xs text-sm text-stone-600"
                  x-text="item.description || '—'"
                ></span>
              </x-backdoor.table.cell>
              {{-- deskripsi end --}}

              {{-- status toggle start --}}
              <x-backdoor.table.cell>
                <x-backdoor.shared.toggle
                  x-bind:checked="item.is_active"
                  x-bind:disabled="state.isLoading"
                  x-bind:aria-label="'Status aktif ' + item.name"
                  x-on:change="toggleBackgroundStatus(item.id)"
                />
              </x-backdoor.table.cell>
              {{-- status toggle end --}}

              {{-- aksi start --}}
              <x-backdoor.table.actions>
                <x-backdoor.table.action-item
                  color="text-yellow-600"
                  x-on:click="closeDropdown(); openEditDrawer(item)"
                  text="Edit"
                />
                <x-backdoor.table.action-item
                  color="text-red-600"
                  x-bind:disabled="state.isLoading"
                  x-on:click="closeDropdown(); destroyBackground(item.id, item.name)"
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

      {{-- preview image modal start --}}
      <x-shared.image-preview-modal />
      {{-- preview image modal end --}}

      {{-- drawer form start --}}
      <x-backdoor.data-master.background.background-drawer-form />
      {{-- drawer form end --}}

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
