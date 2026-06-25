{{--
  * COMPONENT: BACKGROUND DRAWER FORM
  * Drawer sliding panel untuk Create / Edit Background.
  *
  * Terhubung ke Alpine state dari Background.js:
  *   - state.isDrawerOpen, state.isEdit
  *   - state.form.{ name, description, is_active }
  *   - state.errors
  *   - state.pendingImageFile
  *
  * Events yang dipanggil dari luar:
  *   - closeDrawer()
  *   - submitBackground()
  *
  * Custom events yang didengarkan:
  *   - background:reset-filepond (dari useBackgroundForm.js)
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
          aria-labelledby="background-drawer-title"
          x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
          x-transition:enter-start="translate-x-full"
          x-transition:enter-end="translate-x-0"
          x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
          x-transition:leave-start="translate-x-0"
          x-transition:leave-end="translate-x-full"
          class="w-screen max-w-md pointer-events-auto">

          <form
            x-on:submit.prevent="submitBackground"
            class="flex flex-col h-full bg-stone-50 border-l shadow-2xl border-stone-200 overflow-hidden">

            {{-- Header --}}
            <div class="p-4 border-b-2 border-stone-300 bg-stone-200 flex justify-between items-center shrink-0">
              <h2
                class="text-2xl font-bold text-stone-900"
                id="background-drawer-title"
                x-text="state.isEdit ? 'Edit Background' : 'Tambah Background'">
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

              {{-- Input Gambar via FilePond --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-2">
                  Gambar Background
                  <template x-if="!state.isEdit">
                    <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
                  </template>
                </label>

                {{--
                  FilePond diinisialisasi via x-init Alpine.
                  Saat user memilih file → state.pendingImageFile diupdate.
                  Saat drawer di-reset → event 'background:reset-filepond' ditrigger
                  oleh useBackgroundForm.js untuk membersihkan FilePond instance.
                --}}
                <input
                  type="file"
                  accept="image/jpeg,image/jpg,image/png,image/webp"
                  x-init="const pond = window.FilePond.create($el, {
                      credits: false,
                      allowMultiple: false,
                      acceptedFileTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
                      maxFileSize: '2MB',
                      labelIdle: 'Seret gambar ke sini atau <span class=\'filepond--label-action\'>Pilih File</span>',
                      labelMaxFileSizeExceeded: 'Gambar terlalu besar',
                      labelMaxFileSize: 'Maks. 2 MB',
                      labelFileTypeNotAllowed: 'Format tidak didukung. Gunakan JPEG, PNG, atau WebP.',
                      imagePreviewHeight: 180,
                  });
                  
                  pond.on('addfile', (error, fileItem) => {
                      if (!error) state.pendingImageFile = fileItem.file;
                  });
                  
                  pond.on('removefile', () => {
                      state.pendingImageFile = null;
                  });
                  
                  // Dengarkan event reset dari JS composable
                  document.addEventListener('background:reset-filepond', () => {
                      pond.removeFiles();
                  });">

                {{-- Hint saat mode edit --}}
                <template x-if="state.isEdit">
                  <p class="text-xs text-stone-500 mt-2 flex items-center gap-1">
                    <i class="ri-information-line shrink-0" aria-hidden="true"></i>
                    Biarkan kosong jika tidak ingin mengganti gambar yang sudah ada.
                  </p>
                </template>

                <small
                  class="text-red-600 text-xs mt-1 block"
                  x-show="state.errors.image"
                  x-text="state.errors.image"></small>
              </div>

              {{-- Nama Background --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-2">
                  Nama Background
                  <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
                </label>
                <input
                  type="text"
                  x-model="state.form.name"
                  placeholder="Contoh: Putih, Hitam, Abstrak Abu"
                  maxlength="50"
                  class="w-full border border-stone-300 bg-white p-3 text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 text-sm">
                <small
                  class="text-red-600 text-xs mt-1 block"
                  x-show="state.errors.name"
                  x-text="state.errors.name"></small>
              </div>

              {{-- Deskripsi --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-2">Deskripsi</label>
                <textarea
                  x-model="state.form.description"
                  placeholder="Contoh: Latar belakang putih bersih, cocok untuk foto formal atau keluarga..."
                  rows="3"
                  maxlength="255"
                  class="w-full border border-stone-300 bg-white p-3 text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 text-sm"></textarea>
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

              {{-- Toggle Status Aktif --}}
              <div class="flex justify-between items-center gap-4">
                <div class="flex flex-col">
                  <span class="text-sm font-semibold text-stone-900">Status Background Aktif</span>
                  <span class="text-xs text-stone-500 mt-1">
                    Jika aktif, background ini bisa dipilih klien saat booking.
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
                    : (state.isEdit ? 'Simpan Perubahan' : 'Simpan Background')"></span>
              </button>
            </div>

          </form>
        </div>

      </div>
    </div>
  </div>
</div>
