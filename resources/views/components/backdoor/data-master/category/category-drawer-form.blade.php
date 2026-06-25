{{--
  * COMPONENT: CATEGORY DRAWER FORM
  * Drawer sliding panel untuk Create / Edit Kategori.
  *
  * Terhubung ke Alpine state dari Category.js:
  *   - state.isDrawerOpen, state.isEdit
  *   - state.form.{ category_code, name, is_active }
  *   - state.errors
  *
  * Events yang dipanggil dari luar:
  *   - closeDrawer()
  *   - submitCategory()
--}}

<div
  x-show="state.isDrawerOpen"
  x-on:keydown.escape.window="closeDrawer()"
  class="relative z-50"
  x-cloak>

  {{-- Backdrop --}}
  <div
    x-show="state.isDrawerOpen"
    x-transition.opacity.duration.600ms
    x-on:click="closeDrawer()"
    class="fixed inset-0 bg-stone-900/50"
    aria-hidden="true"></div>

  <div class="overflow-hidden fixed inset-0 pointer-events-none">
    <div class="overflow-hidden absolute inset-0">
      <div class="flex fixed inset-y-0 right-0 pl-10 max-w-full">

        {{-- Sliding Panel --}}
        <div
          x-show="state.isDrawerOpen"
          x-on:click.away="closeDrawer()"
          role="dialog"
          aria-modal="true"
          aria-labelledby="category-drawer-title"
          x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
          x-transition:enter-start="translate-x-full"
          x-transition:enter-end="translate-x-0"
          x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
          x-transition:leave-start="translate-x-0"
          x-transition:leave-end="translate-x-full"
          class="w-screen max-w-md pointer-events-auto">

          <form
            x-on:submit.prevent="submitCategory"
            class="flex flex-col h-full bg-stone-50 border-l shadow-2xl border-stone-200 overflow-hidden">

            {{-- Header --}}
            <div class="p-4 border-b-2 border-stone-300 bg-stone-200 flex justify-between items-center shrink-0">
              <h2
                class="text-2xl font-bold text-stone-900"
                id="category-drawer-title"
                x-text="state.isEdit ? 'Edit Kategori' : 'Tambah Kategori'">
              </h2>
              <button
                x-on:click="closeDrawer()"
                type="button"
                aria-label="Tutup drawer"
                class="flex items-center px-3 py-1.5 text-stone-600 transition active:scale-[0.97] cursor-pointer hover:text-stone-900">
                <i class="ri-close-line text-2xl" aria-hidden="true"></i>
              </button>
            </div>

            {{-- Body (scrollable) --}}
            <div class="flex-1 overflow-y-auto pt-6 pb-20 px-6 space-y-6">

              {{-- Kode Kategori --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-2">
                  Kode Kategori
                  <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
                </label>
                <input
                  type="text"
                  x-model="state.form.category_code"
                  placeholder="Masukkan kode kategori (ex: GRD, BTD)"
                  maxlength="3"
                  class="w-full border border-stone-300 bg-white p-3 uppercase text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 text-sm">
                <small
                  class="text-red-600 text-xs mt-1 block"
                  x-show="state.errors.category_code"
                  x-text="state.errors.category_code"></small>
              </div>

              {{-- Nama Kategori --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-2">
                  Nama Kategori
                  <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
                </label>
                <input
                  type="text"
                  x-model="state.form.name"
                  placeholder="Masukkan nama kategori (ex: Graduation, Birthday)"
                  maxlength="100"
                  class="w-full border border-stone-300 bg-white p-3 text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 text-sm">
                <small
                  class="text-red-600 text-xs mt-1 block"
                  x-show="state.errors.name"
                  x-text="state.errors.name"></small>
              </div>

              {{-- Separator --}}
              <div class="h-px bg-stone-200"></div>

              {{-- Toggle Status Aktif --}}
              <div class="flex justify-between items-center gap-4">
                <div class="flex flex-col">
                  <span class="text-sm font-semibold text-stone-900">Status Kategori Aktif</span>
                  <span class="text-xs text-stone-500 mt-1">
                    Jika aktif, kategori ini akan langsung muncul di halaman booking klien.
                  </span>
                </div>
                <x-backdoor.shared.toggle
                  class="shrink-0"
                  x-model="state.form.is_active" />
              </div>

            </div>

            {{-- Footer --}}
            <div class="p-4 border-t border-stone-200 bg-stone-100 flex justify-end items-center gap-8 shrink-0">
              <button
                x-on:click="closeDrawer()"
                type="button"
                x-bind:disabled="state.isLoading"
                class="text-stone-600 hover:text-stone-900 transition font-semibold text-sm cursor-pointer disabled:opacity-50">
                Batal
              </button>
              <button
                type="submit"
                x-bind:disabled="state.isLoading"
                class="bg-stone-700 text-stone-50 px-4 py-2 border border-stone-700 hover:bg-stone-800 transition active:scale-[0.97] font-semibold text-sm tracking-wide cursor-pointer disabled:opacity-50 disabled:pointer-events-none max-w-44">
                <span
                  x-text="state.isLoading
                    ? 'Menyimpan...'
                    : (state.isEdit ? 'Simpan Perubahan' : 'Simpan Kategori')"></span>
              </button>
            </div>

          </form>
        </div>

      </div>
    </div>
  </div>
</div>
