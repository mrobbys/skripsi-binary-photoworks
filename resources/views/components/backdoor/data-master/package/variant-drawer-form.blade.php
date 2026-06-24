{{-- 
  * COMPONENT: VARIANT DRAWER FORM
  * Drawer sliding panel untuk Create / Edit Varian Paket.
  *
  * Bind ke Alpine state:
  *   - state.isVariantDrawerOpen
  *   - state.isVariantEdit
  *   - state.variantForm.*
  *   - state.variantErrors.*
  *
  * Events:
  *   - closeVariantDrawer()
  *   - submitVariant()
  *   - addVariantFeature()
  *   - removeVariantFeature(index)
--}}

<div
  x-show="state.isVariantDrawerOpen"
  x-on:keydown.escape.window="closeVariantDrawer()"
  class="relative z-50"
  x-cloak>

  {{-- Backdrop --}}
  <div
    x-show="state.isVariantDrawerOpen"
    x-transition.opacity.duration.600ms
    x-on:click="closeVariantDrawer()"
    class="fixed inset-0 bg-stone-900/50"
    aria-hidden="true"></div>

  <div class="overflow-hidden fixed inset-0 pointer-events-none">
    <div class="overflow-hidden absolute inset-0">
      <div class="flex fixed inset-y-0 right-0 pl-10 max-w-full">

        {{-- Sliding Panel --}}
        <div
          x-show="state.isVariantDrawerOpen"
          x-on:click.away="closeVariantDrawer()"
          role="dialog"
          aria-modal="true"
          aria-labelledby="variant-drawer-title"
          x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
          x-transition:enter-start="translate-x-full"
          x-transition:enter-end="translate-x-0"
          x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
          x-transition:leave-start="translate-x-0"
          x-transition:leave-end="translate-x-full"
          class="w-screen max-w-md pointer-events-auto">

          <form x-on:submit.prevent="submitVariant"
            class="flex overflow-y-auto flex-col h-full bg-stone-50 border-l shadow-2xl border-stone-200">

            {{-- Header --}}
            <div class="p-4 border-b-2 border-stone-300 bg-stone-200 flex justify-between items-center">
              <h2
                class="text-2xl font-bold text-stone-900"
                id="variant-drawer-title"
                x-text="state.isVariantEdit ? 'Edit Varian' : 'Tambah Varian Baru'">
              </h2>
              <button
                x-on:click="closeVariantDrawer()"
                type="button"
                aria-label="Tutup"
                class="flex items-center px-3 py-1.5 text-stone-600 transition active:scale-[0.97] cursor-pointer">
                <i class="ri-close-line text-2xl" aria-hidden="true"></i>
              </button>
            </div>

            {{-- Body --}}
            <div class="relative space-y-6 flex-1 pt-6 px-6 pb-20 overflow-y-auto">

              {{-- Nama Varian --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-2">Nama Varian</label>
                <input
                  type="text"
                  x-model="state.variantForm.name"
                  placeholder="Contoh: Paket 4, Paket Premium"
                  class="w-full border border-stone-300 bg-white p-3 text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 text-sm">
                <small
                  class="text-red-600 text-xs mt-1 block"
                  x-show="state.variantErrors.name"
                  x-text="state.variantErrors.name"></small>
              </div>

              {{-- Harga Varian --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-2">Harga Varian (Rp)</label>
                <input
                  type="text"
                  inputmode="numeric"
                  x-bind:value="state.variantForm.price ? new Intl.NumberFormat('id-ID').format(state.variantForm.price) : ''"
                  x-on:input="state.variantForm.price = $event.target.value.replace(/\D/g, '');$event.target.value = state.variantForm.price ? new Intl.NumberFormat('id-ID').format(state.variantForm.price) : '';"
                  placeholder="Contoh: 500.000"
                  class="w-full border border-stone-300 bg-white p-3 text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 text-sm" />
                <small
                  class="text-red-600 text-xs mt-1 block"
                  x-show="state.variantErrors.price"
                  x-text="state.variantErrors.price"></small>
              </div>

              {{-- Durasi Sesi --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-2">Durasi Sesi (Menit)</label>
                <input
                  type="number"
                  x-model="state.variantForm.duration"
                  placeholder="Contoh: 60"
                  min="1"
                  max="1000"
                  class="w-full border border-stone-300 bg-white p-3 text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 text-sm">
                <small
                  class="text-red-600 text-xs mt-1 block"
                  x-show="state.variantErrors.duration"
                  x-text="state.variantErrors.duration"></small>
              </div>

              {{-- Separator --}}
              <div class="h-px bg-stone-200"></div>

              {{-- Toggle: WhatsApp Only --}}
              <div class="flex justify-between items-center gap-4">
                <div class="flex flex-col">
                  <span class="text-sm font-semibold text-stone-900">WhatsApp Only (Alur Manual)</span>
                  <span class="text-xs text-stone-500 mt-1">Aktifkan jika varian ini harus dinegosiasikan via
                    WhatsApp
                    (ex: sesi outdoor).</span>
                </div>
                <x-backdoor.shared.toggle
                  class="shrink-0"
                  x-model="state.variantForm.is_whatsapp_only" />
              </div>

              {{-- Toggle: Status Aktif --}}
              <div class="flex justify-between items-center gap-4">
                <div class="flex flex-col">
                  <span class="text-sm font-semibold text-stone-900">Status Varian Aktif</span>
                  <span class="text-xs text-stone-500 mt-1">Jika mati, varian ini tidak bisa dipilih klien.</span>
                </div>
                <x-backdoor.shared.toggle
                  class="shrink-0"
                  x-model="state.variantForm.is_active" />
              </div>

              {{-- Separator --}}
              <div class="h-px bg-stone-200"></div>

              {{-- Fasilitas Spesifik Varian --}}
              <div>
                <label class="text-sm font-semibold text-stone-900 mb-2">Fasilitas Spesifik Varian</label>

                <div class="space-y-2">
                  <template x-for="(feature, index) in state.variantForm.features" :key="index">
                    <div class="flex items-center gap-2">
                      <input
                        type="text"
                        x-model="state.variantForm.features[index]"
                        placeholder="Contoh: 15 Foto Edit"
                        class="block w-full border border-stone-300 bg-white p-3 text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 text-sm">
                      <button
                        type="button"
                        x-on:click="removeVariantFeature(index)"
                        class="p-2 text-stone-400 hover:text-red-500 transition-colors shrink-0">
                        <i class="ri-delete-bin-line text-lg"></i>
                      </button>
                    </div>
                  </template>
                </div>

                <!-- Tombol Tambah Baris -->
                <button
                  type="button"
                  x-on:click="addVariantFeature()"
                  class="mt-3 inline-flex items-center text-sm font-medium text-stone-600 hover:text-stone-900">
                  <i class="ri-add-line mr-1"></i> Tambah Baris
                </button>
              </div>

            </div>

            {{-- Footer --}}
            <div class="p-4 border-t border-stone-200 bg-stone-100 flex justify-end items-center gap-8">
              <button
                x-on:click="closeVariantDrawer()"
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
                  x-text="state.isLoading ? 'Menyimpan...' : (state.isVariantEdit ? 'Simpan Perubahan' : 'Simpan Varian')"></span>
              </button>
            </div>

          </form>
        </div>

      </div>
    </div>
  </div>
</div>
