<x-layouts.frontdoor.index title="Layanan Kami" jsModule="frontdoor/booking/Services">
  <x-slot:content>
    <div class="py-12"
      x-data="Services"
      x-init="initServices($refs.categorySelect)">

      {{-- header start --}}
      <div class="text-center max-w-3xl mx-auto mb-16">
        <h1 class="text-4xl sm:text-5xl font-extrabold text-stone-900 tracking-tight font-serif">
          Katalog Layanan Foto
        </h1>
        <p class="mt-4 text-stone-600 text-lg leading-relaxed">
          Jelajahi koleksi lengkap layanan fotografi profesional kami. Setiap paket dirancang untuk mengabadikan momen
          berharga dengan kualitas terbaik.
        </p>
      </div>
      {{-- header end --}}

      {{-- filter kategori start --}}
      <div x-cloak class="w-full md:w-64 mb-10">
        <select x-ref="categorySelect" class="w-full">
          <option value="Semua">Semua Kategori</option>
          @foreach ($categories as $category)
            @if ($category->packages->isNotEmpty())
              <option value="{{ $category->name }}">{{ $category->name }}</option>
            @endif
          @endforeach
        </select>
      </div>
      {{-- filter kategori end --}}

      <div class="relative min-h-[400px]">

        {{-- grid paket start --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {{-- skeleton start --}}
          <template x-if="state.isLoading">
            <template x-for="i in 6" :key="i">
              <x-skeleton.package-card />
            </template>
          </template>
          {{-- skeleton end --}}

          {{-- all paket start --}}
          <template x-if="!state.isLoading">
            <template x-for="package in state.data" :key="package.id">
              <x-frontdoor.services.package-card />
            </template>
          </template>
          {{-- all paket end --}}
        </div>
        {{-- grid paket end --}}
      </div>

      {{-- pagiation start --}}
      <div class="mt-12 pt-6 border-t border-stone-200">
        <x-frontdoor.shared.pagination />
      </div>
      {{-- pagination end --}}

    </div>
  </x-slot:content>
</x-layouts.frontdoor.index>
