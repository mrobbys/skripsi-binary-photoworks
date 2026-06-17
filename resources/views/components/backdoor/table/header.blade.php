{{-- 
  * COMPONENT TABLE HEADER
  * Header untuk table

  * Props : 
      * placeholder : string
        untuk memberi placeholder pada input pencarian
  * Slot :
        elemen tambahan pada bagian pojok kanan (button tambah)
--}}

@props(['placeholder' => ''])

<div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
  {{-- search bar start --}}
  <div class="w-full sm:w-64 relative">
    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-stone-400">
      <i class="ri-search-line"></i>
    </span>
    <input type="text"
      x-model.debounce.1000ms="table.search"
      placeholder="{{ $placeholder }}"
      class="w-full border border-stone-300 bg-stone-50 pl-9 pr-9 py-2 text-sm text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 transition">
    <button
      x-show="table.search"
      x-cloak
      x-on:click="table.search = ''"
      type="button"
      class="absolute inset-y-0 right-0 pr-3 flex items-center text-stone-400 hover:text-stone-600 transition">
      <i class="ri-close-line text-lg"></i>
    </button>
  </div>
  {{-- search bar end --}}

  {{-- tombol aksi start --}}
  <div class="w-full sm:w-auto flex justify-end">
    {{ $slot }}
  </div>
  {{-- tombol aksi end --}}
</div>
