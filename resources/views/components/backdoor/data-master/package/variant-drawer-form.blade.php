<x-shared.drawer
  openState="state.isVariantDrawerOpen"
  closeAction="closeVariantDrawer()"
  titleExpression="state.isVariantEdit ? 'Edit Varian' : 'Tambah Varian Baru'"
  ariaLabelledBy="variant-drawer-title"
  formAction="submitVariant()"
  maxWidth="max-w-md"
>
  {{-- form body start --}}
  <div class="space-y-6">

    {{-- input nama varian start --}}
    <x-shared.input.field
      name="name"
      label="NAMA VARIAN"
      :required="true"
    >
      <x-shared.input.text
        type="text"
        name="name"
        placeholder="Contoh: Paket 4, Paket Premium"
        x-model="state.variantForm.name"
        x-on:blur="validateVariantField('name')"
        x-on:input="validateVariantField('name')"
        maxlength="100"
        required
      />
    </x-shared.input.field>
    {{-- input nama varian end --}}

    {{-- input harga varian start --}}
    <x-shared.input.field
      name="price"
      label="HARGA VARIAN (RP)"
      :required="true"
    >
      <x-shared.input.text
        type="text"
        name="price"
        inputmode="numeric"
        placeholder="Contoh: 500.000"
        x-bind:value="state.variantForm.price ? new Intl.NumberFormat('id-ID').format(state.variantForm.price) : ''"
        x-on:input="onPriceInput($event)"
        x-on:blur="validateVariantField('price')"
        required
      />
    </x-shared.input.field>
    {{-- input harga varian end --}}

    {{-- input durasi sesi start --}}
    <x-shared.input.field
      name="duration"
      label="DURASI SESI (MENIT)"
      :required="true"
    >
      <x-shared.input.text
        type="number"
        name="duration"
        placeholder="Contoh: 60"
        min="1"
        max="1000"
        x-model="state.variantForm.duration"
        x-on:blur="validateVariantField('duration')"
        x-on:input="validateVariantField('duration')"
        required
      />
    </x-shared.input.field>
    {{-- input durasi sesi end --}}

    {{-- separator start --}}
    <div class="h-px bg-stone-200"></div>
    {{-- separator end --}}

    {{-- toggle whatsapp only start --}}
    <x-shared.input.field
      name="is_whatsapp_only"
      label="WHATSAPP ONLY (ALUR MANUAL)"
    >
      <div class="flex items-center justify-between gap-4">
        <span class="text-xs text-stone-500">
          Aktifkan jika varian ini harus dinegosiasikan via WhatsApp (ex: sesi outdoor)
        </span>
        <x-backdoor.shared.toggle
          class="shrink-0"
          x-model="state.variantForm.is_whatsapp_only"
        />
      </div>
    </x-shared.input.field>
    {{-- toggle whatsapp only end --}}

    {{-- toggle status aktif start --}}
    <x-shared.input.field
      name="is_active"
      label="STATUS VARIAN AKTIF"
    >
      <div class="flex items-center justify-between gap-4">
        <span class="text-xs text-stone-500">
          Jika mati, varian ini tidak bisa dipilih klien saat booking
        </span>
        <x-backdoor.shared.toggle
          class="shrink-0"
          x-model="state.variantForm.is_active"
        />
      </div>
    </x-shared.input.field>
    {{-- toggle status aktif end --}}

    {{-- separator start --}}
    <div class="h-px bg-stone-200"></div>
    {{-- separator end --}}

    {{-- input fasilitas varian start --}}
    <x-shared.input.field
      name="features"
      label="FASILITAS SPESIFIK VARIAN"
    >
      <div class="space-y-2">
        <template
          x-for="(feature, index) in state.variantForm.features"
          x-bind:key="index"
        >
          <div class="flex items-center gap-2">
            <x-shared.input.text
              type="text"
              name="variant_feature_item"
              x-model="state.variantForm.features[index]"
              placeholder="Contoh: 15 Foto Edit"
            />
            <button
              type="button"
              x-on:click="removeVariantFeature(index)"
              class="flex items-center justify-center p-2 text-stone-400 transition hover:text-red-600 cursor-pointer"
              aria-label="Hapus baris fasilitas"
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
        x-on:click="addVariantFeature()"
        class="mt-2 inline-flex items-center text-xs font-semibold text-stone-600 transition hover:text-stone-900 cursor-pointer"
      >
        <i
          class="ri-add-line mr-1 text-sm"
          aria-hidden="true"
        ></i>
        Tambah Baris Fasilitas
      </button>
    </x-shared.input.field>
    {{-- input fasilitas varian end --}}

  </div>
  {{-- form body end --}}

  {{-- drawer footer start --}}
  <x-slot:footer>
    <x-shared.button
      type="button"
      variant="secondary"
      x-on:click="closeVariantDrawer()"
      x-bind:disabled="state.isLoading"
      value="Batal"
    />
    <x-shared.button
      type="submit"
      variant="charcoal"
      x-bind:disabled="state.isLoading || !state.isVariantFormValid"
    >
      <span x-text="state.isLoading ? 'Menyimpan...' : (state.isVariantEdit ? 'Simpan Perubahan' : 'Simpan Varian')"></span>
    </x-shared.button>
  </x-slot:footer>
  {{-- drawer footer end --}}
</x-shared.drawer>
