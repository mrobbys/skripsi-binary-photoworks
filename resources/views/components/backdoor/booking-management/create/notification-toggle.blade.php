{{-- notification toggle card start --}}
<div class="border border-stone-300 p-5">
  <label class="flex cursor-pointer items-start gap-3 select-none">
    <input
      type="checkbox"
      x-model="state.sendWaNotification"
      class="mt-0.5 h-4 w-4 cursor-pointer border-stone-300 text-stone-800 focus:ring-stone-500"
    >
    <div class="flex flex-col">
      <span class="text-sm font-semibold text-stone-900">Kirim Notifikasi WhatsApp</span>
      <span class="text-xs text-stone-500">Kirim rincian pesanan ke nomor WhatsApp klien</span>
    </div>
  </label>
</div>
{{-- notification toggle card end --}}
