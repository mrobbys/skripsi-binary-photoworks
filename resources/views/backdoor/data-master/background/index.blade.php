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
  jsModule="backdoor/master-data/background/Background">

  <x-slot:content>
    <div
      x-data="Background"
      class="w-full space-y-6">

      {{-- title section start --}}
      <x-backdoor.shared.page-header title="Kelola Background" />
      {{-- title section end --}}

      {{-- stats section start --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <x-backdoor.shared.stats-card
          label="Total Background"
          x-text="state.totalBackgrounds"
          suffix="Background" />
        <x-backdoor.shared.stats-card
          label="Total Background Aktif"
          x-text="state.totalActiveBackgrounds"
          suffix="Background" />
      </div>
      {{-- stats section end --}}

      {{-- table card start --}}
      <div class="bg-stone-50 border border-stone-200 p-6 relative overflow-visible">

        {{-- table header start --}}
        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari nama background..." />
          </x-slot:left>

          <x-slot:right>
            <x-backdoor.table.add-button
              x-on:click="openDrawer()"
              text="Tambah Background" />
          </x-slot:right>
        </x-backdoor.table.header>
        {{-- table header end --}}

        {{-- table container start --}}
        <x-backdoor.table.container headers="No,Preview,Nama Background,Deskripsi,Status,Aksi">
          <template
            x-for="(item, index) in table.data"
            x-bind:key="item.id">
            <tr
              class="hover:bg-stone-100 border-b border-stone-200 transition"
              x-show="!table.isLoading"
              x-cloak>
              {{-- No start --}}
              <x-backdoor.table.cell
                class="text-stone-600"
                x-text="(table.pagination.current_page - 1) * table.pagination.per_page + index + 1" />
              {{-- No end --}}

              {{-- Preview Gambar start --}}
              <x-backdoor.table.cell>
                <template x-if="item.image_url">
                  <button
                    type="button"
                    x-on:click="openImagePreview(item.original_url, item.name)"
                    class="block w-16 h-16 overflow-hidden border border-stone-200 hover:border-stone-400 transition cursor-zoom-in group"
                    title="Klik untuk preview">
                    <img
                      :src="item.image_url"
                      :alt="'Preview ' + item.name"
                      class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                  </button>
                </template>
                <template x-if="!item.image_url">
                  <div
                    class="w-16 h-16 bg-stone-100 border border-dashed border-stone-300 flex items-center justify-center">
                    <i class="ri-image-line text-stone-400 text-xl" aria-hidden="true"></i>
                  </div>
                </template>
              </x-backdoor.table.cell>
              {{-- Preview Gambar end --}}

              {{-- Nama start --}}
              <x-backdoor.table.cell
                class="font-semibold text-stone-900"
                x-text="item.name" />
              {{-- Nama end --}}

              {{-- Deskripsi start --}}
              <x-backdoor.table.cell>
                <span
                  x-on:mouseenter="if (!$el._tippy && item.description) window.tippy($el, { content: item.description, showOnCreate: true, placement: 'top' })"
                  class="text-sm text-stone-600 line-clamp-2 max-w-xs cursor-help"
                  x-text="item.description || '—'"></span>
              </x-backdoor.table.cell>
              {{-- Deskripsi end --}}

              {{-- Status toggle start --}}
              <x-backdoor.table.cell>
                <x-backdoor.shared.toggle
                  x-bind:checked="item.is_active"
                  x-bind:disabled="state.isLoading"
                  x-on:change="toggleBackgroundStatus(item.id, item.is_active)" />
              </x-backdoor.table.cell>
              {{-- Status toggle end --}}

              {{-- Aksi start --}}
              <x-backdoor.table.actions>
                <x-backdoor.table.action-item
                  color="text-yellow-600"
                  x-on:click="closeDropdown(); openEditDrawer(item)"
                  text="Edit" />
                <x-backdoor.table.action-item
                  color="text-red-600"
                  x-bind:disabled="state.isLoading"
                  x-on:click="closeDropdown(); destroyBackground(item.id, item.name)"
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

      {{-- Preview Image Modal --}}
      <div
        x-show="state.isPreviewOpen"
        x-on:keydown.escape.window="closeImagePreview()"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/85 p-4"
        x-cloak>
        <div
          x-on:click.self="closeImagePreview()"
          class="relative w-full max-w-3xl">

          <button
            type="button"
            x-on:click="closeImagePreview()"
            class="absolute -top-10 right-0 text-stone-300 hover:text-white transition cursor-pointer"
            aria-label="Tutup preview">
            <i class="ri-close-line text-3xl" aria-hidden="true"></i>
          </button>

          <div class="bg-stone-900 border border-stone-700 overflow-hidden">
            <img
              :src="state.previewImageUrl"
              :alt="'Preview ' + state.previewImageName"
              class="w-full h-auto max-h-[80vh] object-contain">
            <div class="px-4 py-3 text-center border-t border-stone-700">
              <span class="text-stone-300 text-sm font-medium" x-text="state.previewImageName"></span>
            </div>
          </div>

        </div>
      </div>

      {{-- Drawer Form --}}
      <x-backdoor.data-master.background.background-drawer-form />

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
