{{-- financial summary card start --}}
<div class="border border-stone-300">
  <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
    <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Ringkasan Pembayaran</h2>
  </div>
  <div class="space-y-3 p-5">
    <div class="flex justify-between text-sm">
      <span class="text-stone-500">Total Tagihan</span>
      <span
        class="font-semibold text-stone-900 whitespace-nowrap"
        x-text="formatRupiah(state.booking?.total_price || 0)"
      ></span>
    </div>
    <div class="flex justify-between text-sm">
      <span class="text-stone-500">Total Terbayar</span>
      <span
        class="font-semibold text-emerald-700 whitespace-nowrap"
        x-text="formatRupiah(state.summary?.net_paid || 0)"
      ></span>
    </div>
    <div class="flex justify-between border-t border-stone-300 pt-3 text-sm">
      <span class="font-semibold text-stone-700">Sisa Tagihan</span>
      <span
        class="font-bold whitespace-nowrap"
        x-bind:class="(state.summary?.net_paid || 0) >= (state.booking?.total_price || 0) ? 'text-stone-500' : 'text-red-600'"
        x-text="formatRupiah(Math.max(0, (state.booking?.total_price || 0) - (state.summary?.net_paid || 0)))"
      ></span>
    </div>

    {{-- indikator kelebihan bayar start --}}
    <template x-if="state.summary?.overpayment > 0">
      <div class="flex justify-between text-sm font-semibold text-amber-800">
        <span>Kelebihan Bayar</span>
        <span
          class="whitespace-nowrap"
          x-text="formatRupiah(state.summary.overpayment)"
        ></span>
      </div>
    </template>
    {{-- indikator kelebihan bayar end --}}

    {{-- tombol catat refund start --}}
    <template x-if="state.summary?.has_overpayment && (state.summary?.total_refunded || 0) === 0">
      <x-shared.button
        type="button"
        x-on:click="refund()"
        variant="outline"
        size="md"
        class="w-full border-amber-400 bg-amber-50 text-amber-800 hover:bg-amber-100"
        value="Catat Refund"
      >
        <x-slot:iconLeft>
          <i class="ri-refund-line text-lg leading-none" aria-hidden="true"></i>
        </x-slot:iconLeft>
      </x-shared.button>
    </template>
    {{-- tombol catat refund end --}}

    {{-- indikator sudah direfund start --}}
    <template x-if="state.summary?.has_overpayment && (state.summary?.total_refunded || 0) > 0">
      <div class="flex justify-between text-sm text-amber-700">
        <span>Sudah Dikembalikan</span>
        <span
          class="font-semibold whitespace-nowrap"
          x-text="formatRupiah(state.summary.total_refunded)"
        ></span>
      </div>
    </template>
    {{-- indikator sudah direfund end --}}

    {{-- tombol lihat kuitansi start --}}
    <div
      class="mt-4 border-t border-stone-300 pt-4"
      x-show="state.bookingCode"
    >
      <x-shared.button
        as="a"
        x-bind:href="`/payments/${state.bookingCode}/receipt`"
        target="_blank"
        variant="outline"
        size="md"
        class="w-full"
        value="Cetak / Lihat Kuitansi"
      >
        <x-slot:iconLeft>
          <i class="ri-printer-line text-lg leading-none" aria-hidden="true"></i>
        </x-slot:iconLeft>
      </x-shared.button>
    </div>
    {{-- tombol lihat kuitansi end --}}
  </div>
</div>
{{-- financial summary card end --}}
