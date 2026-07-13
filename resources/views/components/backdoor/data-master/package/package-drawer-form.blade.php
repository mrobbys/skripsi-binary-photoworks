@props(['categories' => ''])

<div x-show="state.isDrawerOpen" x-on:keydown.escape.window="closeDrawer()" x-on:click.outside="closeDrawer()"
  class="relative z-50" x-cloak>
  {{-- backdrop start --}}
  <div x-show="state.isDrawerOpen" x-transition.opacity.duration.600ms x-on:click="closeDrawer()"
    class="fixed inset-0 bg-stone-900/50" aria-hidden="true">
  </div>
  {{-- backdrop end --}}

  <div class="overflow-hidden fixed inset-0 pointer-events-none">
    <div class="overflow-hidden absolute inset-0">
      <div class="flex fixed inset-y-0 right-0 pl-10 max-w-full">

        {{-- sliding panel start --}}
        <div x-show="state.isDrawerOpen" x-on:click.away="closeDrawer()" role="dialog" aria-modal="true"
          aria-labelledby="slide-over-title"
          x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
          x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
          x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
          x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
          class="w-screen max-w-md pointer-events-auto">

          {{-- form start --}}
          <form x-on:submit.prevent="submitPackage"
            class="flex overflow-y-auto flex-col h-full bg-stone-50 border-l shadow-2xl border-stone-200">

            {{-- header start --}}
            <div class="p-4 border-b-2 border-stone-300 bg-stone-200 flex justify-between items-center">
              <h2 class="text-2xl font-bold text-stone-900" id="slide-over-title"
                x-text="state.isEdit ? 'Edit Paket' : 'Tambah Paket'">
              </h2>
              <button x-on:click="closeDrawer()" type="button" aria-label="Tutup"
                class="flex items-center px-3 py-1.5 text-stone-600 transition active:scale-[0.97] cursor-pointer">
                <i class="ri-close-line text-2xl" aria-hidden="true"></i>
              </button>
            </div>
            {{-- header end --}}

            {{-- body start --}}
            <div class="relative flex-1 pt-6 pb-20 px-6 overflow-y-auto space-y-6">

              {{-- Input Gambar via FilePond --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-2">
                  Gambar Paket
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
                <input type="file" accept="image/jpeg,image/jpg,image/png,image/webp" x-init="const pond = window.FilePond.create($el, {
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
                document.addEventListener('package:reset-filepond', () => {
                    pond.removeFiles();
                });">

                {{-- Hint saat mode edit --}}
                <template x-if="state.isEdit">
                  <p class="text-xs text-stone-500 mt-2 flex items-center gap-1">
                    <i class="ri-information-line shrink-0" aria-hidden="true"></i>
                    Biarkan kosong jika tidak ingin mengganti gambar yang sudah
                    ada.
                  </p>
                </template>

                <small class="text-red-600 text-xs mt-1 block" x-show="state.errors.image"
                  x-text="state.errors.image"></small>
              </div>

              {{-- nama paket start --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-2">Nama
                  Paket</label>
                <input type="text" placeholder="Nama paket" x-model="state.form.name"
                  class="w-full border border-stone-300 bg-white p-3 text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 text-sm" />
                <small class="text-red-600 text-xs mt-1 block" x-show="state.errors.name"
                  x-text="state.errors.name"></small>
              </div>
              {{-- nama paket end --}}

              {{-- kode kategori start --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-2">Kode
                  Kategori</label>
                <select x-data="choices({
                    placeholder: true,
                    placeholderValue: '--- Pilih Kategori ---',
                    searchPlaceholderValue: 'Ketika nama atau kode kategori... ',
                })" x-modelable="value" x-model="state.form.category_id">
                  @foreach ($categories as $category)
                    <option value="{{ $category->id }}">
                      {{ $category->category_code }} - {{ $category->name }}
                    </option>
                  @endforeach
                </select>
                <small class="text-red-600 text-xs mt-1 block" x-show="state.errors.category_id"
                  x-text="state.errors.category_id"></small>
              </div>
              {{-- kode kategori end --}}

              {{-- description start --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-2">Deskripsi</label>
                <textarea rows="4" placeholder="Masukkan deskripsi singkat untuk paket ini..." x-model="state.form.description"
                  maxlength="500"
                  class="w-full border border-stone-300 bg-white p-3 text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 text-sm resize-none"></textarea>
                <small class="text-red-600 text-xs mt-1 block" x-show="state.errors.description"
                  x-text="state.errors.description"></small>
              </div>
              {{-- description end --}}

              {{-- input keterangan --}}
              <div>
                <label class="block text-sm font-medium text-neutral-700 mb-2">Keterangan</label>

                <div class="space-y-2">
                  <template x-for="(feature, index) in state.form.features" :key="index">
                    <div class="flex items-center gap-2">
                      {{-- input keterangan features start --}}
                      <input type="text" x-model="state.form.features[index]"
                        class="feature-input block w-full border-neutral-300 shadow-sm focus:border-stone-500 focus:ring-stone-500 sm:text-sm p-3"
                        placeholder="Contoh: Cetak foto ukuran 4R..." />
                      {{-- input keterangan features start --}}

                      {{-- tombol remove feature start --}}
                      <button type="button" x-on:click="removeFeature(index)"
                        class="p-2 text-stone-400 hover:text-red-500 transition-colors">
                        <i class="ri-delete-bin-line text-lg"></i>
                      </button>
                      {{-- tombol remove feature end --}}
                    </div>
                  </template>
                </div>

                {{-- tombol tambah baris features start --}}
                <button type="button" x-on:click="addFeature()"
                  class="mt-3 inline-flex items-center text-sm font-medium text-stone-600 hover:text-stone-900">
                  <i class="ri-add-line mr-1"></i> Tambah Baris
                </button>
                {{-- tombol tambah baris features end --}}
              </div>

              {{-- toggle status aktif start --}}
              <div class="flex justify-between items-center gap-4 pt-2 border-t border-stone-200">
                <div class="flex flex-col">
                  <span class="text-sm font-semibold text-stone-900">Status
                    Paket Aktif</span>
                  <span class="text-xs text-stone-500 mt-1">Jika aktif, paket
                    ini akan langsung muncul di halaman
                    booking klien.</span>
                </div>
                <x-backdoor.shared.toggle class="shrink-0" x-model="state.form.is_active" />
              </div>
              {{-- toggle status aktif end --}}

            </div>
            {{-- body end --}}

            {{-- footer start --}}
            <div class="p-4 border-t border-stone-200 bg-stone-100 flex justify-end items-center gap-8">
              <button x-on:click="closeDrawer()" type="button" x-bind:disabled="state.isLoading"
                class="text-stone-600 hover:text-stone-900 transition font-semibold text-sm cursor-pointer disabled:opacity-50">
                Batal
              </button>
              <button type="submit" x-bind:disabled="state.isLoading"
                class="bg-stone-700 text-stone-50 px-4 py-2 border border-stone-700 hover:bg-stone-800 transition active:scale-[0.97] font-semibold text-sm tracking-wide cursor-pointer disabled:opacity-50 disabled:pointer-events-none max-w-44">
                <span
                  x-text="state.isLoading ? 'Menyimpan...' : (state.isEdit ? 'Simpan Perubahan' : 'Simpan & Lanjut Ke Varian')"></span>
              </button>
            </div>
            {{-- footer end --}}

          </form>
          {{-- form end --}}
        </div>
        {{-- sliding panel end --}}

      </div>
    </div>
  </div>
</div>
