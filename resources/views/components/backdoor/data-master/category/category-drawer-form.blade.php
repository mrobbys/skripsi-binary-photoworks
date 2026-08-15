<x-shared.drawer
  openState="state.isDrawerOpen"
  closeAction="closeDrawer()"
  titleExpression="state.isEdit ? 'Edit Kategori' : 'Tambah Kategori'"
  ariaLabelledBy="category-drawer-title"
  formAction="submitCategory()"
  maxWidth="max-w-md"
>
  {{-- form body start --}}
  <div class="space-y-6">

    {{-- input kode kategori start --}}
    <x-shared.input.field
      name="category_code"
      label="KODE KATEGORI"
      :required="true"
    >
      <x-shared.input.text
        type="text"
        name="category_code"
        placeholder="Contoh: GRD, BTD"
        x-model="state.form.category_code"
        x-on:blur="validateField('category_code')"
        x-on:input="validateField('category_code')"
        maxlength="3"
        class="uppercase"
        required
      />
    </x-shared.input.field>
    {{-- input kode kategori end --}}

    {{-- input nama kategori start --}}
    <x-shared.input.field
      name="name"
      label="NAMA KATEGORI"
      :required="true"
    >
      <x-shared.input.text
        type="text"
        name="name"
        placeholder="Contoh: Graduation, Birthday"
        x-model="state.form.name"
        x-on:blur="validateField('name')"
        x-on:input="validateField('name')"
        maxlength="100"
        required
      />
    </x-shared.input.field>
    {{-- input nama kategori end --}}

    {{-- separator start --}}
    <div class="h-px bg-stone-200"></div>
    {{-- separator end --}}

    {{-- toggle status aktif start --}}
    <x-shared.input.field
      name="is_active"
      label="STATUS KATEGORI AKTIF"
    >
      <div class="flex items-center justify-between gap-4">
        <span class="text-xs text-stone-500">
          Jika aktif, kategori ini akan langsung muncul di halaman booking klien.
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
      <span x-text="state.isLoading ? 'Menyimpan...' : (state.isEdit ? 'Simpan Perubahan' : 'Simpan Kategori')"></span>
    </x-shared.button>
  </x-slot:footer>
  {{-- drawer footer end --}}
</x-shared.drawer>
