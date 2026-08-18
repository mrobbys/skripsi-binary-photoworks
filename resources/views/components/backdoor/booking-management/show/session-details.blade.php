{{-- session details card start --}}
<div class="border border-stone-300">
  <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
    <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Detail Sesi Pemotretan</h2>
  </div>
  <div class="grid grid-cols-1 gap-6 p-5 text-sm md:grid-cols-2">
    <div>
      <p class="mb-1 text-xs text-stone-500">Pilihan Paket</p>
      <p
        class="font-medium text-stone-900"
        x-text="(state.booking?.package_variant?.package?.name || '') + ' - ' + (state.booking?.package_variant?.name || '')"
      ></p>
    </div>
    <div>
      <p class="mb-1 text-xs text-stone-500">Background</p>
      <p
        class="font-medium text-stone-900"
        x-text="state.booking?.background?.name || '-'"
      ></p>
    </div>
    <div>
      <p class="mb-1 text-xs text-stone-500">Jadwal Sesi</p>
      <p
        class="font-medium text-stone-900"
        x-text="state.booking?.booking_date ? new Date(state.booking?.booking_date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) : '-'"
      ></p>
    </div>
    <div>
      <p class="mb-1 text-xs text-stone-500">Waktu</p>
      <p class="font-medium text-stone-900">
        <span x-text="state.booking?.start_time"></span> - <span x-text="state.booking?.end_time"></span> WITA
      </p>
    </div>
    <div>
      <p class="mb-1 text-xs text-stone-500">Sumber Booking</p>
      <p
        class="font-medium capitalize text-stone-900"
        x-text="state.booking?.source || '-'"
      ></p>
    </div>
    <div>
      <p class="mb-1 text-xs text-stone-500">Jumlah Reschedule</p>
      <p class="font-medium text-stone-900">
        <span x-text="state.booking?.reschedule_count || 0"></span> Kali
      </p>
    </div>
    <div class="mt-2 border-t border-stone-300 pt-4 md:col-span-2">
      <p class="mb-1 text-xs text-stone-500">Catatan Tambahan</p>
      <p
        class="italic text-stone-700"
        x-text="state.booking?.notes || 'Tidak ada catatan'"
      ></p>
    </div>
  </div>
</div>
{{-- session details card end --}}
