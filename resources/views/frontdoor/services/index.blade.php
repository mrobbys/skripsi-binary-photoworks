<x-layouts.frontdoor.index
  title="Layanan Kami"
  jsModule="frontdoor/booking/Services"
>
  <x-slot:content>
    <div
      x-data="Services"
      x-init="init()"
      class="pb-12"
    >

      {{-- header start --}}
      <div class="mx-auto mb-16 max-w-3xl text-center">
        <h1 class="font-serif text-4xl font-extrabold tracking-tight text-stone-900 sm:text-5xl">
          Katalog Layanan Foto
        </h1>
        <p class="mt-4 text-lg leading-relaxed text-stone-600">
          Jelajahi koleksi lengkap layanan fotografi profesional kami. Setiap paket dirancang untuk mengabadikan momen
          berharga dengan kualitas terbaik.
        </p>
      </div>
      {{-- header end --}}

      {{-- filter kategori start --}}
      <div
        id="servicesTop"
        x-cloak
        class="mb-10 w-full md:w-64"
      >
        <label
          for="categoryFilter"
          class="sr-only"
        >Pilih Kategori Layanan</label>
        <select
          id="categoryFilter"
          x-ref="categorySelect"
          x-data="serviceChoices({ searchEnabled: false, shouldSort: false, itemSelectText: '' })"
          @change="onCategoryChange"
          class="w-full"
          aria-label="Filter Kategori"
        >
          <option value="Semua">Semua Kategori</option>
          @foreach ($categories as $category)
            @if ($category->packages_count > 0)
              <option value="{{ $category->name }}">{{ $category->name }}</option>
            @endif
          @endforeach
        </select>
      </div>
      {{-- filter kategori end --}}

      <div
        class="relative min-h-[400px]"
        aria-live="polite"
      >

        {{-- grid paket start --}}
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
          {{-- skeleton start --}}
          <template x-if="state.isLoading">
            <template
              x-for="i in 6"
              :key="i"
            >
              <x-skeleton.package-card />
            </template>
          </template>
          {{-- skeleton end --}}

          {{-- all paket start --}}
          <template x-if="!state.isLoading && state.data.length > 0">
            <template
              x-for="package in state.data"
              :key="package.id"
            >
              <x-frontdoor.services.package-card />
            </template>
          </template>
          {{-- all paket end --}}

          {{-- empty state start --}}
          <template x-if="!state.isLoading && state.data.length === 0">
            <div
              class="col-span-1 flex flex-col items-center justify-center py-20 text-center md:col-span-2 lg:col-span-3"
            >
              <i class="ri-folder-open-line mb-4 text-4xl text-stone-300"></i>
              <h3 class="font-serif text-lg font-bold text-stone-900">Belum Ada Layanan</h3>
              <p class="mt-1 text-stone-500">Kategori ini belum memiliki paket layanan yang tersedia.</p>
            </div>
          </template>
          {{-- empty state end --}}
        </div>
        {{-- grid paket end --}}
      </div>

      {{-- pagiation start --}}
      <div class="mt-12 border-t border-stone-200 pt-6">
        <x-frontdoor.shared.pagination />
      </div>
      {{-- pagination end --}}

    </div>
  </x-slot:content>
</x-layouts.frontdoor.index>
