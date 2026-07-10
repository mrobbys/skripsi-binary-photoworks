<div
  class="border transition-all overflow-hidden bg-white"
  x-bind:class="state.selectedAppointment?.booking_code === appointment.booking_code ?
      'border-stone-900 ring-1 ring-stone-900 shadow-sm' :
      'border-stone-300 hover:border-stone-700'">

  {{-- card header start --}}
  <div
    class="p-5 flex flex-col sm:flex-row gap-4 cursor-pointer items-start sm:items-center"
    x-on:click="state.selectedAppointment?.booking_code === appointment.booking_code ? clearDetail() : showDetail(appointment)">

    {{-- waktu & tanggal start --}}
    <div class="sm:w-1/3">
      <p class="font-semibold text-stone-900 text-sm" x-text="appointment.formatted_date"></p>
      <p class="text-stone-500 text-sm mt-1 flex items-center gap-2">
        <i class="ri-time-line"></i>
        <span x-text="appointment.formatted_time"></span>
      </p>
    </div>
    {{-- waktu & tanggal start --}}

    {{-- info start --}}
    <div class="flex-1 sm:border-l sm:border-stone-300 text-sm sm:pl-4">
      <p class="text-stone-500 flex gap-x-2 gap-y-1 items-center">
        <span x-text="appointment.variant_name"></span>
        <span class="size-1.5 bg-stone-400 shrink-0"></span>
        <span x-text="appointment.package_name"></span>
      </p>
      <p x-text="appointment.status_label" class="font-medium text-stone-700"></p>
    </div>
    {{-- info end --}}

    {{-- action start --}}
    <div
      class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end border-t border-stone-100 sm:border-none pt-4 sm:pt-0 mt-2 sm:mt-0">

      {{-- Tombol Bayar (Hanya di Header jika belum bayar) --}}
      {{-- 
        Tombol Bayar start
        Kondisi jika belum bayar
      --}}
      <x-shared.button
        x-show="appointment.can_pay"
        x-on:click.stop="triggerRepay(appointment.booking_code)"
        x-bind:disabled="state.isProcessingPayment"
        class="w-auto shrink-0 text-sm font-semibold border border-stone-300 bg-white text-stone-600 hover:bg-stone-50 hover:text-stone-800">
        <span x-show="!state.isProcessingPayment">Bayar Sekarang</span>
        <span x-show="state.isProcessingPayment">Memproses...</span>
      </x-shared.button>
      {{-- Tombol Bayar end --}}

      {{-- icon expand start --}}
      <div
        class="w-8 h-8 flex items-center justify-center rounded-full bg-stone-50 text-stone-500 group-hover:bg-stone-100 transition-colors">
        <i class="ri-arrow-down-s-line text-lg transition-transform duration-300"
          x-bind:class="state.selectedAppointment?.booking_code === appointment.booking_code ? 'rotate-180' : ''"></i>
      </div>
      {{-- icon expand end --}}
    </div>
    {{-- action end --}}

  </div>
  {{-- card header end --}}

  {{-- expanded detail card start --}}
  <div
    x-show="state.selectedAppointment?.booking_code === appointment.booking_code"
    x-collapse>

    <div class="border-t border-stone-100 px-5 py-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">

        {{-- booking info start --}}
        <div>
          <div class="mb-4">
            <p class="text-stone-400 text-xs uppercase tracking-wide mb-1">Kode Booking</p>
            <p class="font-mono font-semibold text-stone-900" x-text="appointment.booking_code"></p>
          </div>
          <div>
            <p class="text-stone-400 text-xs uppercase tracking-wide mb-1">Jadwal Sesi</p>
            <p class="font-medium text-stone-900" x-text="appointment.formatted_date"></p>
            <p class="text-stone-600 mt-0.5" x-text="appointment.formatted_time"></p>
          </div>
        </div>
        {{-- booking info end --}}

        {{-- paket info start --}}
        <div>
          <div class="mb-4">
            <p class="text-stone-400 text-xs uppercase tracking-wide mb-1">Pilihan Paket</p>
            <p class="font-medium text-stone-900" x-text="appointment.package_name"></p>
            <p class="text-stone-600 mt-0.5" x-text="appointment.variant_name"></p>
          </div>
          <div>
            <p class="text-stone-400 text-xs uppercase tracking-wide mb-1">Background</p>
            <p class="font-medium text-stone-900" x-text="appointment.background_name"></p>
          </div>
        </div>
        {{-- paket info end --}}

        {{-- biaya & aksi start --}}
        <div class="sm:col-span-2 lg:col-span-1 flex flex-col">

          <div class="p-3 border border-stone-300 mb-4">
            <div class="flex justify-between items-center mb-2">
              <span class="text-stone-500">Status</span>
              <span class="font-semibold text-stone-900" x-text="appointment.status_label"></span>
            </div>
            <div class="flex justify-between items-center pt-2 border-t border-stone-100">
              <span class="text-stone-500">Total Biaya</span>
              <span class="font-bold text-stone-900" x-text="appointment.formatted_total_price"></span>
            </div>
          </div>
          {{-- biaya & aksi end --}}

          <div class="space-y-2 mt-auto">
            {{-- btn bayar start --}}
            <x-shared.button
              x-show="appointment.can_pay"
              x-on:click="triggerRepay(appointment.booking_code)"
              x-bind:disabled="state.isProcessingPayment"
              class="w-full py-2.5 text-sm font-semibold bg-stone-700 text-stone-50 hover:bg-stone-800">
              <span x-show="!state.isProcessingPayment">Lanjutkan Pembayaran</span>
              <span x-show="state.isProcessingPayment">Memproses...</span>
            </x-shared.button>
            {{-- btn bayar end --}}

            {{-- link hasil foto start --}}
            <x-shared.button
              as="a"
              x-show="appointment.gdrive_link && appointment.status === 'Selesai'"
              x-bind:href="appointment.gdrive_link"
              target="_blank" rel="noopener noreferrer"
              class="w-full py-2.5 text-sm font-semibold border border-stone-900 text-stone-900 bg-white hover:bg-stone-50">
              Link Hasil Foto
            </x-shared.button>
            {{-- link hasil foto end --}}
          </div>

        </div>
      </div>
    </div>
  </div>
  {{-- expanded detail card end --}}

</div>
