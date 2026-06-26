{{--
  * COMPONENT: ADDON DRAWER FORM
  * Drawer sliding panel untuk Create / Edit Add-On.
  *
  * Terhubung ke Alpine state dari Addon.js:
  *   - state.isDrawerOpen, state.isEdit
  *   - state.form.{ name, price, description, has_quantity, is_active }
  *   - state.errors
  *
  * Events yang dipanggil dari luar:
  *   - closeDrawer()
  *   - submitAddon()
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
          aria-labelledby="addon-drawer-title"
          x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
          x-transition:enter-start="translate-x-full"
          x-transition:enter-end="translate-x-0"
          x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
          x-transition:leave-start="translate-x-0"
          x-transition:leave-end="translate-x-full"
          class="w-screen max-w-md pointer-events-auto">

          <form
            x-on:submit.prevent="submitAddon"
            class="flex flex-col h-full bg-stone-50 border-l shadow-2xl border-stone-200 overflow-hidden">

            {{-- Header --}}
            <div class="p-4 border-b-2 border-stone-300 bg-stone-200 flex justify-between items-center shrink-0">
              <h2
                class="text-2xl font-bold text-stone-900"
                id="addon-drawer-title"
                x-text="state.isEdit ? 'Edit Add-On' : 'Tambah Add-On'">
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

              {{-- Nama Add-On --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-2">
                  Nama Add-On
                  <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
                </label>
                <input
                  type="text"
                  x-model="state.form.name"
                  placeholder="Contoh: Cetak Foto + Bingkai 10R"
                  maxlength="100"
                  class="w-full border border-stone-300 bg-white p-3 text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 text-sm">
                <small
                  class="text-red-600 text-xs mt-1 block"
                  x-show="state.errors.name"
                  x-text="state.errors.name"></small>
              </div>

              {{-- Harga --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-2">
                  Harga (Rp)
                  <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
                </label>
                <input
                  type="text"
                  inputmode="numeric"
                  x-bind:value="state.form.price ? new Intl.NumberFormat('id-ID').format(state.form.price) : ''"
                  x-on:input="
                    let val = parseInt($event.target.value.replace(/\D/g, '')) || 0;
                    if (val > 100000000) val = 100000000;
                    state.form.price = val || '';
                    $event.target.value = val ? new Intl.NumberFormat('id-ID').format(val) : '';
                  "
                  placeholder="Contoh: 75.000"
                  
                  class="w-full border border-stone-300 bg-white p-3 text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 text-sm" />
                <small
                  class="text-red-600 text-xs mt-1 block"
                  x-show="state.errors.price"
                  x-text="state.errors.price"></small>
              </div>

              {{-- Deskripsi --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-2">
                  Deskripsi
                  <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
                </label>
                <textarea
                  x-model="state.form.description"
                  placeholder="Contoh: Cetak resolusi tinggi termasuk bingkai kayu minimalis..."
                  rows="3"
                  maxlength="255"
                  class="w-full border border-stone-300 bg-white p-3 text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 text-sm resize-y min-h-12 max-h-64"></textarea>
                <div class="flex justify-between mt-1">
                  <small
                    class="text-red-600 text-xs block"
                    x-show="state.errors.description"
                    x-text="state.errors.description"></small>
                  <small
                    class="text-stone-400 text-xs ml-auto"
                    x-text="(state.form.description?.length ?? 0) + ' / 255'"></small>
                </div>
              </div>

              {{-- Separator --}}
              <div class="h-px bg-stone-200"></div>

              {{-- Tipe Input (has_quantity) --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-3">
                  Tipe Input
                  <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                  {{-- Pilihan: Checkbox (Single) --}}
                  <button
                    type="button"
                    x-on:click="state.form.has_quantity = false"
                    x-bind:class="!state.form.has_quantity ?
                        'border-stone-700 bg-stone-800 text-stone-50' :
                        'border-stone-300 bg-white text-stone-700 hover:border-stone-500'"
                    class="flex flex-col items-center gap-2 p-3 border-2 transition cursor-pointer">
                    <i class="ri-checkbox-line text-2xl" aria-hidden="true"></i>
                    <div>
                      <p class="text-xs font-bold">Checkbox (Single)</p>
                      <p class="text-xs opacity-70 mt-0.5">Pilih satu / ya-tidak</p>
                    </div>
                  </button>

                  {{-- Pilihan: Counter (Multi) --}}
                  <button
                    type="button"
                    x-on:click="state.form.has_quantity = true"
                    x-bind:class="state.form.has_quantity ?
                        'border-stone-700 bg-stone-800 text-stone-50' :
                        'border-stone-300 bg-white text-stone-700 hover:border-stone-500'"
                    class="flex flex-col items-center gap-2 p-3 border-2 transition cursor-pointer">
                    <i class="ri-add-circle-line text-2xl" aria-hidden="true"></i>
                    <div>
                      <p class="text-xs font-bold">Counter (Multi)</p>
                      <p class="text-xs opacity-70 mt-0.5">Bisa lebih dari satu</p>
                    </div>
                  </button>
                </div>
                <small
                  class="text-red-600 text-xs mt-1 block"
                  x-show="state.errors.has_quantity"
                  x-text="state.errors.has_quantity"></small>
              </div>

              {{-- Separator --}}
              <div class="h-px bg-stone-200"></div>

              {{-- Toggle Status Aktif --}}
              <div class="flex justify-between items-center gap-4">
                <div class="flex flex-col">
                  <span class="text-sm font-semibold text-stone-900">Status Add-On Aktif</span>
                  <span class="text-xs text-stone-500 mt-1">
                    Jika aktif, add-on ini bisa dipilih klien saat booking.
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
                    : (state.isEdit ? 'Simpan Perubahan' : 'Simpan Add-On')"></span>
              </button>
            </div>

          </form>
        </div>

      </div>
    </div>
  </div>
</div>
