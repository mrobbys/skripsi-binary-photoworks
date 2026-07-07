<div class="max-w-5xl mx-auto px-6">
  {{-- Header --}}
  <div class="text-center mb-12">
    <h2 class="text-3xl font-bold font-heading text-stone-900 mb-2">Ringkasan Pesanan</h2>
    <p class="text-stone-500 text-sm md:text-base font-medium">Periksa kembali detail pesanan Anda sebelum membayar</p>
  </div>

  {{-- Card wrapper (no rounded corners, no shadows) --}}
  <div class="border border-stone-200 bg-white p-8 md:p-12 space-y-6">

    {{-- Section 1: Booking Details --}}
    <div class="space-y-4">
      <div class="flex justify-between items-center text-sm">
        <span class="text-stone-500">Paket</span>
        <span class="font-bold text-stone-900 text-right"
          x-text="state.packageName + ' - ' + state.selectedVariant?.name"></span>
      </div>
      <template x-if="state.selectedBackgroundId">
        <div class="flex justify-between items-center text-sm">
          <span class="text-stone-500">Background</span>
          <span class="font-bold text-stone-900 text-right"
            x-text="state.allBackgrounds?.find(b => b.id === state.selectedBackgroundId)?.name ?? '-'"></span>
        </div>
      </template>
      <div class="flex justify-between items-center text-sm">
        <span class="text-stone-500">Tanggal</span>
        <span class="font-bold text-stone-900 text-right" x-text="state.formattedDate"></span>
      </div>
      <div class="flex justify-between items-center text-sm">
        <span class="text-stone-500">Waktu</span>
        <span class="font-bold text-stone-900 text-right"
          x-text="state.selectedSlot ? (state.selectedSlot.start_time + ' – ' + state.selectedSlot.end_time + ' WITA') : '-'"></span>
      </div>
    </div>

    <hr class="border-stone-200" />

    {{-- Section 2: User Details --}}
    <div class="space-y-4">
      <div class="flex justify-between items-center text-sm">
        <span class="text-stone-500">Pelanggan</span>
        <span class="font-bold text-stone-900 text-right">{{ Auth::user()->name }}</span>
      </div>
      <div class="flex justify-between items-center text-sm">
        <span class="text-stone-500">WhatsApp</span>
        <span class="font-bold text-stone-900 text-right">{{ Auth::user()->phone }}</span>
      </div>
      <div class="flex justify-between items-center text-sm">
        <span class="text-stone-500">Email</span>
        <span class="font-bold text-stone-900 text-right">{{ Auth::user()->email }}</span>
      </div>
    </div>

    <hr class="border-stone-200" />

    {{-- Section 3: Prices --}}
    <div class="space-y-4">
      <div class="flex justify-between items-center text-sm">
        <span class="text-stone-600" x-text="state.packageName + ' - ' + state.selectedVariant?.name"></span>
        <span class="font-bold text-stone-900" x-text="formatRupiah(state.selectedVariant?.price ?? 0)"></span>
      </div>

      {{-- Addons List --}}
      <template x-if="Object.keys(state.selectedAddons).length > 0">
        <div class="space-y-3 pt-2">
          <span class="text-xs font-bold text-stone-900 tracking-wider block uppercase">ADD-ONS</span>
          <template x-for="[addonId, qty] in Object.entries(state.selectedAddons)" :key="addonId">
            <div class="flex justify-between items-center text-sm">
              <span class="text-stone-500"
                x-text="(state.allAddons.find(a => a.id === parseInt(addonId))?.name ?? '') + (qty > 1 ? ' ×' + qty : '')"></span>
              <span class="font-bold text-stone-900"
                x-text="formatRupiah((state.allAddons.find(a => a.id === parseInt(addonId))?.price ?? 0) * qty)"></span>
            </div>
          </template>
        </div>
      </template>
    </div>

    <hr class="border-stone-200" />

    {{-- Section 4: Payment Scheme --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 text-sm">
      <span class="text-stone-500">Metode Pembayaran</span>
      <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
        <label class="flex items-center gap-2 cursor-pointer select-none">
          <input type="radio" x-model="state.paymentScheme" value="lunas" class="accent-stone-700 w-4 h-4">
          <div>
            <span class="font-bold text-stone-900 block">Lunas Penuh</span>
            <span class="text-xs text-stone-500" x-text="formatRupiah(totalPrice())"></span>
          </div>
        </label>
        <label class="flex items-center gap-2 cursor-pointer select-none">
          <input type="radio" x-model="state.paymentScheme" value="dp" class="accent-stone-700 w-4 h-4 mt-0.5">
          <div>
            <span class="font-bold text-stone-900 block">DP 60%</span>
            <span class="text-xs text-stone-500">
              Bayar <span class="font-semibold" x-text="formatRupiah(dpAmount())"></span> sekarang, <br> sisa <span
                class="font-semibold" x-text="formatRupiah(dpRemaining())"></span> di kasir.
            </span>
          </div>
        </label>
      </div>
    </div>

    <hr class="border-stone-200" />

    {{-- Section 5: Keterangan (Catatan Tambahan) --}}
    <div class="space-y-3">
      <label for="keterangan" class="block text-xs font-bold tracking-wider text-stone-600 uppercase">
        Catatan Tambahan (Opsional)
      </label>
      <textarea
        id="keterangan"
        x-model="state.keterangan"
        rows="3"
        placeholder="Tuliskan keterangan / catatan Anda di sini (contoh: membawa properti sendiri, dll)..."
        class="w-full bg-stone-50 border border-stone-300 text-stone-900 placeholder-stone-400 text-sm p-4 focus:outline-none focus:border-stone-500 focus:ring-2 focus:ring-stone-500 focus:ring-offset-2 focus:ring-offset-stone-50 rounded-none resize-none"></textarea>
    </div>

    <hr class="border-stone-200" />

    {{-- Section 5: Total --}}
    <div class="flex justify-between items-baseline font-bold">
      <span class="text-stone-900 text-lg">Total</span>
      <span class="text-stone-950 text-2xl font-heading"
        x-text="state.paymentScheme === 'dp' ? formatRupiah(grossAmount()) : formatRupiah(totalPrice())"></span>
    </div>

  </div>

  {{-- Actions --}}
  <div class="mt-12 flex flex-col sm:flex-row gap-12">
    <x-shared.button variant="outline" size="lg" value="Kembali" x-on:click="prevStep()">
      <x-slot:iconLeft>
        <i class="ri-arrow-left-line"></i>
      </x-slot:iconLeft>
    </x-shared.button>
    <x-shared.button variant="primary" size="lg"
      x-on:click="triggerCheckout()" x-bind:disabled="state.isProcessing">
      <template x-if="!state.isProcessing">
        <span>Bayar Sekarang <i class="ri-secure-payment-line ml-1"></i></span>
      </template>
      <template x-if="state.isProcessing">
        <span><i class="ri-loader-4-line animate-spin mr-1"></i> Mengunci Slot Jadwal...</span>
      </template>
    </x-shared.button>
  </div>
</div>
