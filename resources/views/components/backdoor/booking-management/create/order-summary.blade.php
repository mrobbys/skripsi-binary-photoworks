{{-- order summary card start --}}
<div class="border border-stone-300">
  <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
    <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Ringkasan Pesanan</h2>
  </div>
  <div class="space-y-3 p-5">
    <div class="flex items-center justify-between pb-3 text-sm">
      <span class="font-semibold text-stone-600">Total Biaya Layanan</span>
      <span
        class="font-bold text-stone-900 whitespace-nowrap"
        x-text="'Rp ' + state.totalPrice.toLocaleString('id-ID')"
      ></span>
    </div>

    {{-- hitungan berdasarkan status dp terbayar --}}
    <template x-if="state.bookingStatus === 'DP Terbayar'">
      <div class="space-y-2 border-t border-stone-200 pt-3">
        <div class="flex items-center justify-between text-sm">
          <span class="text-stone-500">DP Terbayar (60%)</span>
          <span
            class="font-bold text-emerald-600 whitespace-nowrap"
            x-text="'- Rp ' + state.dpAmount.toLocaleString('id-ID')"
          ></span>
        </div>
        <div class="flex items-center justify-between text-sm">
          <span class="font-semibold text-stone-900">Sisa Pelunasan (40%)</span>
          <span
            class="font-bold text-rose-600 whitespace-nowrap"
            x-text="'Rp ' + state.remainingAmount.toLocaleString('id-ID')"
          ></span>
        </div>
      </div>
    </template>

    {{-- hitungan berdasarkan status lunas --}}
    <template x-if="state.bookingStatus === 'Lunas'">
      <div class="border-t border-stone-200 pt-3">
        <div class="flex items-center justify-between text-sm">
          <span class="font-semibold text-stone-900">Sisa Pelunasan</span>
          <span class="font-bold text-emerald-600 whitespace-nowrap">Rp 0 (Lunas)</span>
        </div>
      </div>
    </template>
  </div>
</div>
{{-- order summary card end --}}
