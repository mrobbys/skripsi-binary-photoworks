@php
  $tableHeaders = ['No', 'Nama Varian & Fasilitas', 'Harga', 'Durasi', 'Status'];
  if (auth()->user()->canany(['packageVariant-master-update', 'packageVariant-master-delete'])) {
      $tableHeaders[] = 'Aksi';
  }
@endphp

<div class="relative overflow-visible border border-stone-200 bg-stone-50 p-6">
  {{-- table header start --}}
  <x-backdoor.table.header>
    <x-slot:left>
      <h3 class="font-heading text-xl font-bold text-stone-900 md:text-2xl">
        Daftar Varian Sesi & Harga
      </h3>
    </x-slot:left>

    <x-slot:right>
      @can('packageVariant-master-create')
        <x-backdoor.table.add-button
          x-on:click="openVariantDrawer(state.packageSlug)"
          text="Tambah Varian"
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

        {{-- nama varian & fasilitas start --}}
        <x-backdoor.table.cell>
          <div
            class="flex flex-col gap-1"
            x-data="{ open: false }"
          >
            <div class="flex items-center gap-2">
              <span
                class="text-sm font-bold text-stone-900 md:text-base"
                x-text="item.name"
              ></span>
              <x-shared.badge
                value="WA Only"
                variant="neutral"
                icon="ri-whatsapp-line"
                alpine="item.is_whatsapp_only"
              />
            </div>

            <ul class="mt-1 flex flex-col gap-1">
              <template
                x-for="(feature, fIndex) in item.features || []"
                x-bind:key="feature.id || fIndex"
              >
                <li
                  x-show="open || fIndex < 2"
                  class="flex items-start gap-2 text-xs text-stone-600 md:text-sm"
                  x-transition
                  x-cloak
                >
                  <span class="mt-1.5 size-1.5 shrink-0 bg-stone-400"></span>
                  <span x-text="feature.description || feature"></span>
                </li>
              </template>
            </ul>

            <template x-if="(item.features?.length || 0) > 2">
              <button
                x-on:click="open = !open"
                type="button"
                class="group mt-1 inline-flex w-fit items-center gap-1 text-xs font-semibold text-stone-500 transition hover:text-stone-900 cursor-pointer"
              >
                <span
                  class="group-hover:underline"
                  x-text="open ? 'Sembunyikan' : 'Lihat ' + (item.features.length - 2) + ' fasilitas lainnya'"
                ></span>
                <i
                  class="ri-arrow-down-s-line transition-transform duration-200"
                  x-bind:class="open ? 'rotate-180' : ''"
                  aria-hidden="true"
                ></i>
              </button>
            </template>
          </div>
        </x-backdoor.table.cell>
        {{-- nama varian & fasilitas end --}}

        {{-- harga start --}}
        <x-backdoor.table.cell class="font-mono text-xs font-semibold text-stone-900">
          <span x-text="formatRupiah(item.price)"></span>
        </x-backdoor.table.cell>
        {{-- harga end --}}

        {{-- durasi start --}}
        <x-backdoor.table.cell class="font-semibold text-stone-900">
          <span
            class="whitespace-nowrap"
            x-text="item.duration"
          ></span>
          <span>Menit</span>
        </x-backdoor.table.cell>
        {{-- durasi end --}}

        {{-- status toggle start --}}
        <x-backdoor.table.cell>
          <x-backdoor.shared.toggle
            x-bind:checked="item.is_active"
            x-bind:disabled="state.isLoading || {{ auth()->user()->can('packageVariant-master-update') ? 'false' : 'true' }}"
            x-bind:aria-label="'Status aktif ' + item.name"
            x-on:change="toggleVariantStatus(state.packageSlug, item.id, $event)"
          />
        </x-backdoor.table.cell>
        {{-- status toggle end --}}

        {{-- aksi start --}}
        @canany(['packageVariant-master-update', 'packageVariant-master-delete'])
          <x-backdoor.table.actions>
            @can('packageVariant-master-update')
              <x-backdoor.table.action-item
                color="text-yellow-600"
                x-on:click="closeDropdown(); editVariant(item, state.packageSlug)"
                text="Edit"
              />
            @endcan
            @can('packageVariant-master-delete')
              <x-backdoor.table.action-item
                color="text-red-600"
                x-bind:disabled="state.isLoading"
                x-on:click="closeDropdown(); destroyVariant(state.packageSlug, item.id, item.name)"
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
</div>
