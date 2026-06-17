{{-- 
  * COMPONENT TABLE ACTIONS
  * Dropdown menu aksi (context menu) untuk setiap baris di tabel.
  *
  * Slot :
  *   * `slot` : Elemen tombol / link aksi yang dimasukkan di dalam dropdown (misal: Edit, Hapus).
--}}

<td {{ $attributes->merge(["class" => "px-6 py-4"]) }} x-data="{
    tippyInstance: null,
    closeDropdown() {
        if (this.tippyInstance) this.tippyInstance.hide();
    },
    init() {
        // Pastikan DOM sudah siap
        this.$nextTick(() => {
            // Hilangkan class hidden dari elemen dropdown agar bisa dirender Tippy
            this.$refs.dropdown.classList.remove('hidden');

            this.tippyInstance = tippy(this.$refs.btn, {
                content: this.$refs.dropdown,
                interactive: true,
                trigger: 'click',
                placement: 'bottom-end',
                appendTo: 'parent',
                popperOptions: {
                    strategy: 'fixed',
                },
                arrow: false,
                theme: 'custom',
                offset: [0, 5],
                // agar ketika diklik di dalam dropdown (misal tombol hapus), tippy tertutup
                onClickOutside: (instance) => instance.hide(),
            });
        });
    }
}">
  <button
    x-ref="btn"
    class="text-stone-500 hover:text-stone-900 p-1 transition">
    <i class="ri-more-2-fill text-xl"></i>
  </button>

  {{-- Wadah konten dropdown yang akan diambil oleh Tippy --}}
  <div x-ref="dropdown" class="hidden">
    <div class="w-32 bg-stone-50 border border-stone-300 py-1 shadow-md">
      {{ $slot }}
    </div>
  </div>
</td>