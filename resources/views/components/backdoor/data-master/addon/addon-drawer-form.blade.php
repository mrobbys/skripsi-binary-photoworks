<x-shared.drawer
  openState="state.isDrawerOpen"
  closeAction="closeDrawer()"
  titleExpression="state.isEdit ? 'Edit Add-On' : 'Tambah Add-On'"
  ariaLabelledBy="addon-drawer-title"
  formAction="submitAddon()"
  maxWidth="max-w-md"
>
  {{-- form body start --}}
  <div class="space-y-6">

    {{-- input nama add-on start --}}
    <x-shared.input.field
      name="name"
      label="NAMA ADD-ON"
      :required="true"
    >
      <x-shared.input.text
        type="text"
        name="name"
        placeholder="Contoh: Cetak Foto + Bingkai 10R"
        x-model="state.form.name"
        x-on:blur="validateField('name')"
        x-on:input="validateField('name')"
        maxlength="100"
        required
      />
    </x-shared.input.field>
    {{-- input nama add-on end --}}

    {{-- input harga start --}}
    <x-shared.input.field
      name="price"
      label="HARGA (RP)"
      :required="true"
    >
      <x-shared.input.text
        type="text"
        name="price"
        placeholder="Contoh: 75.000"
        inputmode="numeric"
        x-bind:value="state.form.price ? new Intl.NumberFormat('id-ID').format(state.form.price) : ''"
        x-on:input="
          let val = parseInt($event.target.value.replace(/\D/g, '')) || 0;
          if (val > 100000000) val = 100000000;
          state.form.price = val || '';
          $event.target.value = val ? new Intl.NumberFormat('id-ID').format(val) : '';
          validateField('price');
        "
        x-on:blur="validateField('price')"
        required
      />
    </x-shared.input.field>
    {{-- input harga end --}}

    {{-- input deskripsi start --}}
    <x-shared.input.field
      name="description"
      label="DESKRIPSI"
      :required="true"
    >
      <x-shared.input.textarea
        name="description"
        placeholder="Contoh: Cetak resolusi tinggi termasuk bingkai kayu minimalis..."
        rows="3"
        maxlength="255"
        x-model="state.form.description"
        x-on:blur="validateField('description')"
        x-on:input="validateField('description')"
        required
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

    {{-- input tipe input start --}}
    <x-shared.input.field
      name="has_quantity"
      label="TIPE INPUT"
      :required="true"
    >
      <div class="grid grid-cols-2 gap-3">
        {{-- pilihan: checkbox single --}}
        <button
          type="button"
          x-on:click="state.form.has_quantity = false; validateField('has_quantity')"
          x-bind:class="!state.form.has_quantity ?
              'border-stone-700 bg-stone-800 text-stone-50' :
              'border-stone-300 bg-white text-stone-700 hover:border-stone-500'"
          class="flex cursor-pointer flex-col items-center gap-2 border-2 p-3 transition"
        >
          <i
            class="ri-checkbox-line text-2xl"
            aria-hidden="true"
          ></i>
          <div class="text-center">
            <p class="text-xs font-bold">Checkbox (Single)</p>
            <p class="mt-0.5 text-[11px] opacity-70">Pilih satu / ya-tidak</p>
          </div>
        </button>

        {{-- pilihan: counter multi --}}
        <button
          type="button"
          x-on:click="state.form.has_quantity = true; validateField('has_quantity')"
          x-bind:class="state.form.has_quantity ?
              'border-stone-700 bg-stone-800 text-stone-50' :
              'border-stone-300 bg-white text-stone-700 hover:border-stone-500'"
          class="flex cursor-pointer flex-col items-center gap-2 border-2 p-3 transition"
        >
          <i
            class="ri-add-circle-line text-2xl"
            aria-hidden="true"
          ></i>
          <div class="text-center">
            <p class="text-xs font-bold">Counter (Multi)</p>
            <p class="mt-0.5 text-[11px] opacity-70">Bisa lebih dari satu</p>
          </div>
        </button>
      </div>
    </x-shared.input.field>
    {{-- input tipe input end --}}

    {{-- separator start --}}
    <div class="h-px bg-stone-200"></div>
    {{-- separator end --}}

    {{-- toggle status aktif start --}}
    <x-shared.input.field
      name="is_active"
      label="STATUS ADD-ON AKTIF"
    >
      <div class="flex items-center justify-between gap-4">
        <span class="text-xs text-stone-500">
          Jika aktif, add-on ini bisa dipilih klien saat booking.
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
      <span x-text="state.isLoading ? 'Menyimpan...' : (state.isEdit ? 'Simpan Perubahan' : 'Simpan Add-On')"></span>
    </x-shared.button>
  </x-slot:footer>
  {{-- drawer footer end --}}
</x-shared.drawer>
