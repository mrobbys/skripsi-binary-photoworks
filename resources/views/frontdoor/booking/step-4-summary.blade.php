<div class="mx-auto max-w-5xl">
  {{-- Header --}}
  <div class="mb-12 text-center">
    <h2 class="font-heading mb-2 text-3xl font-bold text-stone-900">Ringkasan Pesanan</h2>
    <p class="text-sm font-medium text-stone-500 md:text-base">Periksa kembali detail pesanan Anda sebelum membayar</p>
  </div>

  <div class="space-y-6 border border-stone-300 bg-stone-100 p-5 sm:p-8">

    {{-- section 1 booking details start --}}
    <div class="space-y-4">
      <div class="flex flex-col gap-1 text-sm sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <span class="text-stone-500">Paket</span>
        <span
          class="font-bold text-stone-900 sm:text-right"
          x-text="state.packageName + ' - ' + state.selectedVariant?.name"
        ></span>
      </div>
      <template x-if="state.selectedBackgroundId">
        <div class="flex flex-col gap-1 text-sm sm:flex-row sm:items-center sm:justify-between sm:gap-4">
          <span class="text-stone-500">Background</span>
          <span
            class="font-bold text-stone-900 sm:text-right"
            x-text="state.allBackgrounds?.find(b => b.id === state.selectedBackgroundId)?.name ?? '-'"
          ></span>
        </div>
      </template>
      <div class="flex flex-col gap-1 text-sm sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <span class="text-stone-500">Tanggal</span>
        <span
          class="font-bold text-stone-900 sm:text-right"
          x-text="state.formattedDate"
        ></span>
      </div>
      <div class="flex flex-col gap-1 text-sm sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <span class="text-stone-500">Waktu</span>
        <span
          class="font-bold text-stone-900 sm:text-right"
          x-text="state.selectedSlot ? (state.selectedSlot.start_time + ' – ' + state.selectedSlot.end_time + ' WITA') : '-'"
        ></span>
      </div>
    </div>
    {{-- section 1 booking details start --}}

    <hr class="border-stone-200" />

    {{-- section 2 user details start --}}
    <div class="space-y-4">
      <div class="flex flex-col gap-1 text-sm sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <span class="text-stone-500">Pelanggan</span>
        <span class="font-bold text-stone-900 sm:text-right">{{ Auth::user()->name }}</span>
      </div>
      <div class="flex flex-col gap-1 text-sm sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <span class="text-stone-500">WhatsApp</span>
        <span class="font-bold text-stone-900 sm:text-right">{{ Auth::user()->phone }}</span>
      </div>
      <div class="flex flex-col gap-1 text-sm sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <span class="text-stone-500">Email</span>
        <span class="font-bold text-stone-900 sm:text-right">{{ Auth::user()->email }}</span>
      </div>
    </div>
    {{-- section 2 user details start --}}

    <hr class="border-stone-200" />

    {{-- section 3 prices start --}}
    <div class="space-y-4">
      {{-- harga paket variant --}}
      <div class="flex flex-col gap-1 text-sm sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <span
          class="text-stone-600"
          x-text="state.packageName + ' - ' + state.selectedVariant?.name"
        ></span>
        <span
          class="font-bold text-stone-900 sm:text-right"
          x-text="formatRupiah(state.selectedVariant?.price ?? 0)"
        ></span>
      </div>

      {{-- Addons List --}}
      <template x-if="Object.keys(state.selectedAddons).length > 0">
        <div class="space-y-3 pt-2">
          <span class="block text-xs font-bold uppercase tracking-wider text-stone-900">ADD-ONS</span>
          <template
            x-for="[addonId, qty] in Object.entries(state.selectedAddons)"
            :key="addonId"
          >
            <div class="mt-2 flex flex-col gap-1 text-sm sm:flex-row sm:items-center sm:justify-between sm:gap-4">
              <span
                class="text-stone-500"
                x-text="(state.allAddons.find(a => a.id === parseInt(addonId))?.name ?? '') + (qty > 1 ? ' ×' + qty : '')"
              ></span>
              <span
                class="font-bold text-stone-900 sm:text-right"
                x-text="formatRupiah((state.allAddons.find(a => a.id === parseInt(addonId))?.price ?? 0) * qty)"
              ></span>
            </div>
          </template>
        </div>
      </template>
    </div>
    {{-- section 3 prices end --}}

    <hr class="border-stone-200" />

    {{-- section 4 payment skema start --}}
    <div class="flex flex-col items-start justify-between gap-6 text-sm md:flex-row md:items-center">
      <span class="text-lg text-stone-900">Metode Pembayaran</span>
      <div class="flex flex-col items-start gap-6 sm:flex-row sm:items-center">
        <label class="flex cursor-pointer select-none items-center gap-2">
          <input
            type="radio"
            x-model="state.paymentScheme"
            value="lunas"
            class="h-4 w-4 accent-stone-700"
          >
          <div>
            <span class="block font-bold text-stone-900">Lunas Penuh</span>
            <span
              class="text-xs text-stone-500"
              x-text="formatRupiah(totalPrice())"
            ></span>
          </div>
        </label>
        <label class="flex cursor-pointer select-none items-center gap-2">
          <input
            type="radio"
            x-model="state.paymentScheme"
            value="dp"
            class="mt-0.5 h-4 w-4 accent-stone-700"
          >
          <div>
            <span class="block font-bold text-stone-900">DP 60%</span>
            <span class="text-xs text-stone-500">
              Bayar <span
                class="font-semibold"
                x-text="formatRupiah(dpAmount())"
              ></span> sekarang, <br> sisa <span
                class="font-semibold"
                x-text="formatRupiah(dpRemaining())"
              ></span> di kasir.
            </span>
          </div>
        </label>
      </div>
    </div>
    {{-- section 4 payment skema end --}}

    <hr class="border-stone-200" />

    {{-- section 5 keterangan start --}}
    <div class="space-y-3">
      <label
        for="notes"
        class="block text-xs font-bold uppercase tracking-wider text-stone-600"
      >
        Catatan Tambahan (Opsional)
      </label>
      <textarea
        id="notes"
        x-model="state.notes"
        rows="3"
        placeholder="Tuliskan keterangan / catatan Anda di sini"
        class="w-full border border-stone-300 bg-stone-50 p-3 text-sm text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none"
      ></textarea>
    </div>
    {{-- section 5 keterangan start --}}

    <hr class="border-stone-200" />

    {{-- section 6 total start --}}
    <div class="flex items-baseline justify-between font-bold">
      <span class="text-lg text-stone-900">Total</span>
      <span
        class="font-heading text-2xl text-stone-950"
        x-text="formatRupiah(grossAmount())"
      ></span>
    </div>
    {{-- section 6 total start --}}

  </div>

  {{-- actions start --}}
  <div class="mt-12 flex flex-col gap-4 sm:flex-row sm:gap-6">
    {{-- button kembali --}}
    <x-shared.button
      variant="outline"
      size="lg"
      value="Kembali"
      x-on:click="prevStep()"
      class="w-full sm:flex-1"
    >
      <x-slot:iconLeft>
        <i
          class="ri-arrow-left-line"
          aria-hidden="true"
        ></i>
      </x-slot:iconLeft>
    </x-shared.button>

    {{-- button checkout --}}
    <x-shared.button
      variant="dark"
      size="lg"
      value="Bayar Sekarang"
      xLoading="state.isProcessing"
      loadingText="Mengunci Slot Jadwal..."
      x-bind:disabled="state.isProcessing"
      x-on:click="triggerCheckout()"
      class="w-full sm:flex-1"
    >
      <x-slot:iconRight>
        <i
          class="ri-bank-card-line"
          aria-hidden="true"
        ></i>
      </x-slot:iconRight>
    </x-shared.button>
  </div>
  {{-- actions end --}}
</div>
