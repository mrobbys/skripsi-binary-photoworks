{{-- 
  * COMPONENT TABLE ACTIONS
  * Dropdown menu aksi (context menu) untuk setiap baris di tabel.
  *
  * Slot :
  *   * `slot` : Elemen tombol / link aksi yang dimasukkan di dalam dropdown (misal: Edit, Hapus).
--}}

<td {{ $attributes->merge(['class' => 'px-3 py-3 md:px-6 md:py-4']) }} x-data="tableActionDropdown">
  <button
    x-ref="btn"
    type="button"
    aria-label="Aksi baris"
    aria-haspopup="true"
    class="text-stone-500 hover:text-stone-900 p-1 transition focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-stone-400">
    <i class="ri-more-2-fill text-xl" aria-hidden="true"></i>
  </button>

  {{-- Wadah konten dropdown yang akan diambil oleh Tippy --}}
  <div x-ref="dropdown">
    <div class="w-36 bg-stone-50 border border-stone-400 py-1">
      {{ $slot }}
    </div>
  </div>
</td>
