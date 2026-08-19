{{-- payment history card start --}}
<div class="border border-stone-300">
  <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
    <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Riwayat Transaksi</h2>
  </div>
  <div class="divide-y divide-stone-300">
    <template x-if="!state.booking?.payments?.length">
      <p class="p-5 text-xs italic text-stone-400">Belum ada transaksi</p>
    </template>
    <template
      x-for="payment in state.booking?.payments"
      :key="payment.id"
    >
      <div class="flex items-start justify-between p-5 text-sm">
        <div class="space-y-1">
          <p
            class="text-xs font-bold uppercase tracking-wider text-stone-800"
            x-text="payment.payment_purpose === 'refund' ? 'Pengembalian Dana' : payment.payment_purpose"
          ></p>
          <p
            class="font-mono text-xs text-stone-600"
            x-text="'#' + payment.order_id"
          ></p>
          <p
            class="text-xs capitalize text-stone-500"
            x-text="(payment.payment_type || 'Unknown') + (payment.payment_type === 'manual' ? ' (Admin)' : ' (Midtrans)')"
          ></p>
          <template x-if="payment.pay_date">
            <p
              class="text-xs text-stone-500"
              x-text="'Dibayar: ' + new Date(payment.pay_date).toLocaleString('id-ID', {day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'}).replace(/\./g, ':') + ' WITA'"
            ></p>
          </template>
        </div>
        <div class="text-right">
          <p
            class="font-bold text-stone-900 whitespace-nowrap"
            :class="payment.payment_purpose === 'refund' ? 'text-amber-700' : (payment.status === 'Settlement' ? 'text-emerald-700' : '')"
            x-text="payment.payment_purpose === 'refund' ? '- ' + formatRupiah(payment.amount) : formatRupiah(payment.amount)"
          ></p>
          <div class="mt-1">
            <template x-if="payment.payment_purpose === 'refund'">
              <x-shared.badge
                size="xs"
                variant="warning"
                x-text="payment.status"
              />
            </template>
            <template x-if="payment.payment_purpose !== 'refund'">
              <x-shared.badge
                alpine="payment.status === 'Settlement'"
                size="xs"
                variant="success"
                x-text="payment.status"
              />
            </template>
            <x-shared.badge
              x-show="payment.status !== 'Settlement' && payment.payment_purpose !== 'refund'"
              size="xs"
              variant="secondary"
              x-text="payment.status"
            />
          </div>
        </div>
      </div>
    </template>
  </div>
</div>
{{-- payment history card end --}}
