<div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
  {{-- Invoice Table --}}
  <div class="lg:col-span-2 border border-stone-200">
    <div class="border-b border-stone-200 p-5">
      <h3 class="text-xl font-bold text-stone-900">Ringkasan Pesanan</h3>
    </div>
    <div class="p-5 space-y-3 text-sm">
      <div class="flex justify-between">
        <span class="text-stone-600">Paket</span>
        <span class="font-medium" x-text="state.selectedVariant?.name"></span>
      </div>
      <template x-if="state.selectedBackgroundId">
        <div class="flex justify-between">
          <span class="text-stone-600">Background</span>
          <span class="font-medium"
            x-text="state.allBackgrounds?.find(b => b.id === state.selectedBackgroundId)?.name ?? '-'"></span>
        </div>
      </template>
      <div class="flex justify-between">
        <span class="text-stone-600">Tanggal Sesi</span>
        <span class="font-medium" x-text="state.selectedDate"></span>
      </div>
      <div class="flex justify-between">
        <span class="text-stone-600">Waktu Sesi</span>
        <span class="font-medium"
          x-text="state.selectedSlot ? state.selectedSlot.start_time + ' – ' + state.selectedSlot.end_time + ' WITA' : '-'"></span>
      </div>
      <div class="border-t border-stone-200 pt-3 space-y-2">
        <div class="flex justify-between">
          <span class="text-stone-600">Harga Varian</span>
          <span x-text="formatRupiah(state.selectedVariant?.price ?? 0)"></span>
        </div>
        <template x-for="[addonId, qty] in Object.entries(state.selectedAddons)" :key="addonId">
          <div class="flex justify-between">
            <span class="text-stone-600"
              x-text="(state.allAddons.find(a => a.id === parseInt(addonId))?.name ?? '') + ' ×' + qty"></span>
            <span
              x-text="formatRupiah((state.allAddons.find(a => a.id === parseInt(addonId))?.price ?? 0) * qty)"></span>
          </div>
        </template>
      </div>
      <div class="border-t border-stone-200 pt-3 flex justify-between font-bold text-base">
        <span class="text-stone-900">Total</span>
        <span class="text-stone-900" x-text="formatRupiah(totalPrice())"></span>
      </div>
    </div>
  </div>

  {{-- Payment Scheme + CTA --}}
  <div class="space-y-6">
    <div class="border border-stone-200 p-5">
      <h3 class="font-semibold text-stone-900 text-sm uppercase tracking-widest mb-5">Skema Pembayaran</h3>
      <div class="space-y-4">
        <label class="flex items-start gap-3 cursor-pointer">
          <input type="radio" x-model="state.paymentScheme" value="lunas" class="mt-0.5 accent-stone-500">
          <div>
            <span class="font-semibold text-stone-900 block">Lunas Penuh</span>
            <span class="text-xs text-stone-600" x-text="formatRupiah(totalPrice())"></span>
          </div>
        </label>
        <label class="flex items-start gap-3 cursor-pointer">
          <input type="radio" x-model="state.paymentScheme" value="dp" class="mt-0.5 accent-stone-500">
          <div>
            <span class="font-semibold text-stone-900 block">DP 60%</span>
            <span class="text-xs text-stone-600">
              Bayar <span x-text="formatRupiah(grossAmount())"></span> sekarang,
              sisa <span x-text="formatRupiah(remainingAmount())"></span> di kasir.
            </span>
          </div>
        </label>
      </div>
    </div>

    <div class="flex flex-col gap-3">
      <x-shared.button variant="ghost" x-on:click="prevStep()">
        <i class="ri-arrow-left-line mr-1"></i> Kembali
      </x-shared.button>
      <x-shared.button variant="primary" class="w-full"
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
</div>
