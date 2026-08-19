{{-- status selection card start --}}
<div class="border border-stone-300">
  <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
    <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Status Awal Booking</h2>
  </div>
  <div class="p-5">
    <select
      x-data="choices({ placeholder: true, placeholderValue: '--- Pilih Status ---' })"
      x-modelable="value"
      x-model="state.bookingStatus"
    >
      <option value="DP Terbayar">DP Terbayar - Sudah Bayar 60%</option>
      <option value="Lunas">Lunas - Bayar Penuh</option>
    </select>
    <template x-if="state.errors['status']">
      <p
        class="mt-1.5 text-xs text-red-600"
        x-text="Array.isArray(state.errors['status']) ? state.errors['status'][0] : state.errors['status']"
      ></p>
    </template>
  </div>
</div>
{{-- status selection card end --}}
