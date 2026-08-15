@props(['categories' => ''])

<x-shared.drawer
  openState="state.isDrawerOpen"
  closeAction="closeDrawer()"
  titleExpression="state.isEdit ? 'Edit Paket' : 'Tambah Paket'"
  ariaLabelledBy="package-drawer-title"
  formAction="submitPackage()"
  maxWidth="max-w-md"
>
  {{-- form body start --}}
  <div class="space-y-6">

    {{-- input gambar paket start --}}
    <x-shared.input.field
      name="image"
      label="GAMBAR PAKET"
      x-bind:required="!state.isEdit"
    >
      <input
        type="file"
        accept="image/jpeg,image/jpg,image/png,image/webp"
        x-init="initFilePond($el)"
      />

      {{-- hint saat mode edit --}}
      <template x-if="state.isEdit">
        <p class="flex items-center gap-1 text-xs text-stone-500">
          <i
            class="ri-information-line shrink-0"
            aria-hidden="true"
          ></i>
          Biarkan kosong jika tidak ingin mengganti gambar yang sudah ada
        </p>
      </template>
    </x-shared.input.field>
    {{-- input gambar paket end --}}

    {{-- input nama paket start --}}
    <x-shared.input.field
      name="name"
      label="NAMA PAKET"
      :required="true"
    >
      <x-shared.input.text
        type="text"
        name="name"
        placeholder="Contoh: Graduation Package A"
        x-model="state.form.name"
        x-on:blur="validateField('name')"
        x-on:input="validateField('name')"
        maxlength="100"
        required
      />
    </x-shared.input.field>
    {{-- input nama paket end --}}

    {{-- input kategori start --}}
    <x-shared.input.field
      name="category_id"
      label="KATEGORI"
      :required="true"
    >
      <select
        name="category_id"
        x-data="packageChoices({
            placeholder: true,
            placeholderValue: '--- Pilih Kategori ---',
            searchPlaceholderValue: 'Ketik nama atau kode kategori...',
        })"
        x-modelable="value"
        x-model="state.form.category_id"
        x-on:change="validateField('category_id', $event.target.value)"
        required
      >
        <option value="">--- Pilih Kategori ---</option>
        @foreach ($categories as $category)
          <option value="{{ $category->id }}">
            {{ $category->category_code }} - {{ $category->name }}
          </option>
        @endforeach
      </select>
    </x-shared.input.field>
    {{-- input kategori end --}}

    {{-- input deskripsi start --}}
    <x-shared.input.field
      name="description"
      label="DESKRIPSI"
      :required="true"
    >
      <x-shared.input.textarea
        name="description"
        placeholder="Masukkan deskripsi singkat untuk paket ini..."
        rows="3"
        maxlength="500"
        x-model="state.form.description"
        x-on:blur="validateField('description')"
        x-on:input="validateField('description')"
        required
      />
      <div class="mt-1 flex justify-end">
        <span
          class="font-mono text-[11px] text-stone-400"
          x-text="(state.form.description?.length ?? 0) + ' / 500'"
        ></span>
      </div>
    </x-shared.input.field>
    {{-- input deskripsi end --}}

    {{-- separator start --}}
    <div class="h-px bg-stone-200"></div>
    {{-- separator end --}}

    {{-- input fitur keterangan start --}}
    <x-shared.input.field
      name="features"
      label="FITUR / KETERANGAN PAKET"
    >
      <div class="space-y-2">
        <template
          x-for="(feature, index) in state.form.features"
          x-bind:key="index"
        >
          <div class="flex items-center gap-2">
            <x-shared.input.text
              type="text"
              name="feature_item"
              x-model="state.form.features[index]"
              placeholder="Contoh: Cetak foto ukuran 4R..."
            />
            <button
              type="button"
              x-on:click="removeFeature(index)"
              class="flex items-center justify-center p-2 text-stone-400 transition hover:text-red-600 cursor-pointer"
              aria-label="Hapus baris fitur"
            >
              <i
                class="ri-delete-bin-line text-lg"
                aria-hidden="true"
              ></i>
            </button>
          </div>
        </template>
      </div>

      <button
        type="button"
        x-on:click="addFeature()"
        class="mt-2 inline-flex items-center text-xs font-semibold text-stone-600 transition hover:text-stone-900 cursor-pointer"
      >
        <i
          class="ri-add-line mr-1 text-sm"
          aria-hidden="true"
        ></i>
        Tambah Baris Fitur
      </button>
    </x-shared.input.field>
    {{-- input fitur keterangan end --}}

    {{-- separator start --}}
    <div class="h-px bg-stone-200"></div>
    {{-- separator end --}}

    {{-- toggle status aktif start --}}
    <x-shared.input.field
      name="is_active"
      label="STATUS PAKET AKTIF"
    >
      <div class="flex items-center justify-between gap-4">
        <span class="text-xs text-stone-500">
          Jika aktif, paket ini akan langsung muncul di halaman booking klien
        </span>
        <x-backdoor.shared.toggle
          class="shrink-0"
          x-model="state.form.is_active"
        />
      </div>
    </x-shared.input.field>
    {{-- toggle status aktif end --}}

  </div>
  {{-- form body end --}}

  {{-- drawer footer start --}}
  <x-slot:footer>
    <x-shared.button
      type="button"
      variant="secondary"
      x-on:click="closeDrawer()"
      x-bind:disabled="state.isLoading"
      value="Batal"
    />
    <x-shared.button
      type="submit"
      variant="charcoal"
      x-bind:disabled="state.isLoading || !state.isFormValid"
    >
      <span x-text="state.isLoading ? 'Menyimpan...' : (state.isEdit ? 'Simpan Perubahan' : 'Simpan & Lanjut Ke Varian')"></span>
    </x-shared.button>
  </x-slot:footer>
  {{-- drawer footer end --}}
</x-shared.drawer>
