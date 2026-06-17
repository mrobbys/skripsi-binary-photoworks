<div
  x-show="state.isModalOpen"
  style="display: none;"
  class="fixed inset-0 bg-stone-900/40 backdrop-blur-xs flex items-center justify-center p-4 z-50">
  <div
    class="bg-stone-50 border border-stone-300 w-full max-w-lg flex flex-col shadow-none"
    x-on:click.away="closeModal()">
    <!-- Header -->
    <div class="bg-stone-200/50 border-b border-stone-300 px-6 py-4 flex justify-between items-center">
      <h2
        class="text-xl font-bold text-stone-900"
        x-text="state.isEdit ? 'Edit Kategori' : 'Tambah Kategori'"></h2>
      <button
        x-on:click="closeModal()"
        class="text-stone-500 hover:text-stone-900 transition">
        <i class="ri-close-line text-xl"></i>
      </button>
    </div>

    <form x-on:submit.prevent="submitForm">
      <!-- Body -->
      <div class="px-6 py-8 space-y-6">
        <!-- Input: category_code -->
        <div>
          <label class="block text-sm font-semibold text-stone-900 mb-2">Kode Kategori</label>
          <input
            type="text"
            x-model="state.form.category_code"
            maxlength="3"
            placeholder="Masukkan kode kategori (ex: GRD, BTD)"
            class="w-full border border-stone-300 bg-white p-3 uppercase text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 text-sm"
            required>
          <small
            class="text-red-600 text-xs mt-1 block"
            x-show="state.errors.category_code"
            x-text="state.errors.category_code"></small>
        </div>

        <!-- Input: name -->
        <div>
          <label class="block text-sm font-semibold text-stone-900 mb-2">Nama Kategori</label>
          <input
            type="text"
            x-model="state.form.name"
            placeholder="Masukkan nama kategori (ex: Graduation, Birthday)"
            class="w-full border border-stone-300 bg-white p-3 text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 text-sm"
            required>
          <small
            class="text-red-600 text-xs mt-1 block"
            x-show="state.errors.name"
            x-text="state.errors.name"></small>
        </div>

        <!-- Input: is_active (Toggle switch aligned horizontally with metadata) -->
        <div class="flex justify-between items-center gap-4">
          <div class="flex flex-col">
            <span class="text-sm font-semibold text-stone-900">Status Kategori Aktif</span>
            <span class="text-xs text-stone-500 mt-1">Jika aktif, kategori ini akan langsung muncul di halaman
              booking klien.</span>
          </div>
          <x-backdoor.shared.toggle
            class="shrink-0"
            x-model="state.form.is_active" />
        </div>
      </div>

      <!-- Footer -->
      <div class="bg-stone-200/50 border-t border-stone-300 px-6 py-4 flex justify-end items-center gap-5">
        <button
          type="button"
          x-on:click="closeModal()"
          class="text-sm font-semibold text-stone-500 hover:text-stone-900 transition">Batal</button>
        <button
          type="submit"
          class="px-6 py-3 text-stone-50 bg-[#44403c] hover:bg-[#322f2c] transition font-semibold text-sm"
          x-bind:disabled="state.isLoading">
          <span
            x-text="state.isLoading ? 'Menyimpan...' : (state.isEdit ? 'Simpan Perubahan' : 'Simpan Kategori')"></span>
        </button>
      </div>
    </form>
  </div>
</div>
