<x-shared.drawer
  openState="state.isDrawerOpen"
  closeAction="closeDrawer()"
  titleExpression="state.isEdit ? 'Edit Background' : 'Tambah Background'"
  ariaLabelledBy="background-drawer-title"
  formAction="submitBackground()"
  maxWidth="max-w-md"
>
  {{-- form body start --}}
  <div class="space-y-6">

    {{-- input upload gambar start --}}
    <x-shared.input.field
      name="image"
      label="GAMBAR BACKGROUND"
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
    {{-- input upload gambar end --}}

    {{-- input nama background start --}}
    <x-shared.input.field
      name="name"
      label="NAMA BACKGROUND"
      :required="true"
    >
      <x-shared.input.text
        type="text"
        name="name"
        placeholder="Contoh: Putih, Hitam, Abstrak Abu"
        x-model="state.form.name"
        x-on:blur="validateField('name')"
        x-on:input="validateField('name')"
        maxlength="50"
        required
      />
    </x-shared.input.field>
    {{-- input nama background end --}}

    {{-- input deskripsi start --}}
    <x-shared.input.field
      name="description"
      label="DESKRIPSI"
    >
      <x-shared.input.textarea
        name="description"
        placeholder="Contoh: Latar belakang putih bersih, cocok untuk foto formal atau keluarga..."
        rows="3"
        maxlength="255"
        x-model="state.form.description"
        x-on:blur="validateField('description')"
        x-on:input="validateField('description')"
      />
      <div class="mt-1 flex justify-end">
        <span
          class="font-mono text-[11px] text-stone-400"
          x-text="(state.form.description?.length ?? 0) + ' / 255'"
        ></span>
      </div>
    </x-shared.input.field>
    {{-- input deskripsi end --}}

    {{-- separator start --}}
    <div class="h-px bg-stone-200"></div>
    {{-- separator end --}}

    {{-- toggle status aktif start --}}
    <x-shared.input.field
      name="is_active"
      label="STATUS BACKGROUND AKTIF"
    >
      <div class="flex items-center justify-between gap-4">
        <span class="text-xs text-stone-500">
          Jika aktif, background ini bisa dipilih klien saat booking
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
      <span x-text="state.isLoading ? 'Menyimpan...' : (state.isEdit ? 'Simpan Perubahan' : 'Simpan Background')"></span>
    </x-shared.button>
  </x-slot:footer>
  {{-- drawer footer end --}}
</x-shared.drawer>
