@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Data Master', 'url' => '#'],
      ['label' => 'Kelola Paket & Varian', 'url' => route('backdoor.data-master.package.index')],
      ['label' => 'Detail Paket', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index title="Detail Paket" :breadcrumbs="$breadcrumbs" jsModule="backdoor/master-data/package/ShowPackage">

  <x-slot:heads>
    <script>
      window.__packageSlug = "{{ $package->slug }}";
    </script>
  </x-slot:heads>

  <x-slot:content>
    <div class="w-full space-y-6" x-data="ShowPackage" x-init="setPackageInfo({{ json_encode(['slug' => $package->slug, 'category_id' => $package->category_id, 'category_name' => $package->category?->name, 'name' => $package->name, 'description' => $package->description, 'image_url' => $package->getFirstMediaUrl('package-image'), 'is_active' => $package->is_active, 'features' => $package->features->pluck('description')->values()->all()]) }})">
      {{-- title section start --}}
      <div class="space-y-6">
        <a href="{{ route('backdoor.data-master.package.index') }}"
          class="inline-flex items-center gap-1 text-[10px] md:text-xs font-bold tracking-wider text-stone-500 hover:text-stone-950 uppercase transition cursor-pointer">
          <i class="ri-arrow-left-line" aria-hidden="true"></i>
          <span>Kembali Ke Kelola Paket & Varian</span>
        </a>
        <x-backdoor.shared.page-header>Detail Paket: <span
            x-text="state.packageInfo?.name"></span></x-backdoor.shared.page-header>
      </div>
      {{-- title section end --}}

      {{-- info paket start --}}
      <div class="bg-stone-50 border border-stone-200 p-6 md:p-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
          {{-- image start --}}
          <div class="md:col-span-4 lg:col-span-3">
            <div class="aspect-4/5 w-full bg-stone-200 border border-stone-300">
              <template x-if="state.packageInfo?.image_url">
                <img x-bind:src="state.packageInfo?.image_url" x-bind:alt="state.packageInfo?.name"
                  class="w-full h-full object-cover select-none pointer-events-none">
              </template>
              <template x-if="!state.packageInfo?.image_url">
                <div
                  class="w-full h-full flex flex-col items-center justify-center text-stone-400 gap-2 p-4 text-center">
                  <i class="ri-image-line text-4xl" aria-hidden="true"></i>
                  <span class="text-xs font-semibold">Belum ada gambar</span>
                </div>
              </template>
            </div>
          </div>
          {{-- image end --}}

          {{-- text column start --}}
          <div class="md:col-span-8 lg:col-span-9 flex flex-col justify-between">
            <div>
              {{-- header info start --}}
              <div class="flex justify-between items-start gap-4 pb-6">
                <div>
                  <span class="text-[10px] md:text-xs font-semibold tracking-wider text-stone-500 uppercase"
                    x-text="state.packageInfo?.category_name"></span>
                  <h3 class="text-xl md:text-2xl font-bold font-heading text-stone-900 mt-1"
                    x-text="state.packageInfo?.name">
                  </h3>
                  <template x-if="state.packageInfo?.description">
                    <p class="mt-3 text-sm text-stone-600 leading-relaxed max-w-3xl"
                      x-text="state.packageInfo?.description"></p>
                  </template>
                </div>
                <button type="button" x-on:click="openEditDrawer(state.packageInfo)"
                  class="shrink-0 inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-stone-700 bg-white border border-stone-300 hover:bg-stone-50 transition active:scale-[0.97] cursor-pointer">
                  <i class="ri-pencil-fill"></i>
                  <span>Edit Info Paket</span>
                </button>
              </div>
              {{-- header info end --}}

              <div class="h-px bg-stone-200 mb-6"></div>

              {{-- keterangan (features) start --}}
              <div x-data="{ open: false }">
                <span class="text-[10px] md:text-xs font-semibold tracking-wider text-stone-500 uppercase">Keterangan
                  Global</span>
                <ul class="mt-4 grid grid-cols-1 xl:grid-cols-2 gap-x-8 gap-y-3">
                  <template x-for="(feature, index) in state.packageInfo?.features || []" :key="index">
                    <li x-show="open || index < 6" class="flex items-start gap-2.5 text-sm text-stone-700" x-transition
                      x-cloak>
                      <span class="size-1.5 bg-stone-400 mt-2 shrink-0"></span>
                      <span x-text="feature"></span>
                    </li>
                  </template>
                  <template x-if="!state.packageInfo?.features || state.packageInfo.features.length === 0">
                    <li class="col-span-full text-sm text-stone-400 italic">Tidak ada keterangan global.</li>
                  </template>
                </ul>

                <template x-if="(state.packageInfo?.features?.length || 0) > 6">
                  <div class="mt-4">
                    <button x-on:click="open = !open" type="button"
                      class="inline-flex items-center gap-1 text-xs font-semibold text-stone-500 hover:text-stone-900 transition cursor-pointer group">
                      <span x-text="open ? 'Tampilkan lebih sedikit' : 'Tampilkan selengkapnya'"
                        class="group-hover:underline"></span>
                      <i class="ri-arrow-down-s-line transition-transform duration-200"
                        :class="open ? 'rotate-180' : ''"></i>
                    </button>
                  </div>
                </template>
              </div>
              {{-- keterangan (features) end --}}
            </div>
          </div>
          {{-- text column end --}}
        </div>
      </div>
      {{-- info paket end --}}

      {{-- table daftar varian start --}}
      <div class="bg-stone-50 border border-stone-200 p-6 relative overflow-visible">
        {{-- table header start --}}
        <x-backdoor.table.header>
          <x-slot:left>
            <h3 class="text-xl md:text-2xl font-bold font-heading text-stone-900">
              Daftar Varian Sesi & Harga
            </h3>
          </x-slot:left>

          <x-slot:right>
            <x-backdoor.table.add-button x-on:click="openVariantDrawer('{{ $package->slug }}')" text="Tambah Varian" />
          </x-slot:right>
        </x-backdoor.table.header>
        {{-- table header end --}}

        {{-- table container start --}}
        <x-backdoor.table.container headers="No, Nama Varian & Fasilitas, Harga, Durasi, Status, Aksi">
          <template x-for="(item, index) in table.data" :key="item.id">
            <tr class="hover:bg-stone-100 border-b border-stone-200 transition" x-show="!table.isLoading" x-cloak>

              {{-- No start --}}
              <x-backdoor.table.cell class="text-stone-600"
                x-text="(table.pagination.current_page - 1) * table.pagination.per_page + index + 1" />
              {{-- No end --}}

              {{-- Nama Varian & Fasilitas start --}}
              <x-backdoor.table.cell>
                <div class="flex flex-col gap-1" x-data="{ open: false }">
                  <div class="flex items-center gap-2">
                    <span class="font-bold text-stone-900 text-sm md:text-base" x-text="item.name"></span>
                    <x-shared.badge value="WA Only" variant="neutral" icon="ri-whatsapp-line"
                      alpine="item.is_whatsapp_only" />
                  </div>

                  <ul class="flex flex-col gap-1 mt-1">
                    <template x-for="(feature, fIndex) in item.features || []" :key="feature.id || fIndex">
                      <li x-show="open || fIndex < 2" class="flex items-start gap-2 text-xs md:text-sm text-stone-600"
                        x-transition x-cloak>
                        <span class="size-1.5 bg-stone-400 mt-1.5 shrink-0"></span>
                        <span x-text="feature.description || feature"></span>
                      </li>
                    </template>
                  </ul>

                  <template x-if="(item.features?.length || 0) > 2">
                    <button x-on:click="open = !open" type="button"
                      class="inline-flex items-center gap-1 text-xs md:text-xs font-semibold text-stone-500 hover:text-stone-900 transition mt-1 w-fit group cursor-pointer">
                      <span class="group-hover:underline"
                        x-text="open ? 'Sembunyikan' : 'Lihat ' + (item.features.length - 2) + ' fasilitas lainnya'"></span>
                      <i class="ri-arrow-down-s-line transition-transform duration-200"
                        :class="open ? 'rotate-180' : ''"></i>
                    </button>
                  </template>
                </div>
              </x-backdoor.table.cell>
              {{-- Nama Varian & Fasilitas end --}}

              {{-- Harga start --}}
              <x-backdoor.table.cell class="font-semibold text-stone-900">
                <span x-text="formatRupiah(item.price)"></span>
              </x-backdoor.table.cell>
              {{-- Harga end --}}

              {{-- Durasi start --}}
              <x-backdoor.table.cell class="font-semibold text-stone-900">
                <span class="whitespace-nowrap" x-text="item.duration"></span>
                <span>Menit</span>
              </x-backdoor.table.cell>
              {{-- Durasi end --}}

              {{-- Status toggle start --}}
              <x-backdoor.table.cell>
                <x-backdoor.shared.toggle x-bind:checked="item.is_active" x-bind:disabled="state.isLoading"
                  x-on:change="toggleVariantStatus(window.__packageSlug, item.id, $event)" />
              </x-backdoor.table.cell>
              {{-- Status toggle end --}}

              {{-- Aksi start --}}
              <x-backdoor.table.actions>
                <x-backdoor.table.action-item color="text-yellow-600"
                  x-on:click="closeDropdown(); editVariant(item, window.__packageSlug)" text="Edit" />
                <x-backdoor.table.action-item x-bind:disabled="state.isLoading" color="text-red-600"
                  x-on:click="closeDropdown(); destroyVariant(window.__packageSlug, item.id, item.name)"
                  text="Hapus" />
              </x-backdoor.table.actions>
              {{-- Aksi end --}}

            </tr>
          </template>
        </x-backdoor.table.container>
        {{-- table container end --}}
      </div>
      {{-- table daftar varian end --}}

      {{-- drawer form edit package start --}}
      <x-backdoor.data-master.package.package-drawer-form :categories="$categories" />
      {{-- drawer form edit package end --}}

      {{-- drawer form ( create / update) variant start --}}
      <x-backdoor.data-master.package.variant-drawer-form />
      {{-- drawer form ( create / update) variant end --}}

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
