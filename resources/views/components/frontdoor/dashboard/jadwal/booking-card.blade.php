<div
  class="border transition-all overflow-hidden"
  x-bind:class="state.selectedAppointment?.booking_code === appointment.booking_code ?
      'border-stone-900 ring-1 ring-stone-900' :
      'border-stone-200 hover:border-stone-400'">

  {{-- card header start --}}
  <div
    class="flex flex-col sm:flex-row gap-4 cursor-pointer items-start sm:items-center p-5"
    x-on:click="state.selectedAppointment?.booking_code === appointment.booking_code ? clearDetail() : showDetail(appointment)">

    {{-- waktu & tanggal start --}}
    <div class="sm:w-1/4">
      <p class="font-medium text-stone-900 text-sm" x-text="appointment.formatted_date"></p>
      <p class="text-stone-400 text-xs mt-1 flex items-center gap-1">
        <i class="ri-time-line"></i>
        <span x-text="appointment.formatted_time"></span>
      </p>
    </div>
    {{-- waktu & tanggal end --}}

    {{-- info start --}}
    <div class="flex-1 sm:border-l sm:border-stone-200 text-sm sm:pl-5">
      <p class="text-stone-600 flex gap-x-2 gap-y-1 items-center font-medium">
        <span x-text="appointment.variant_name" class="text-stone-800"></span>
        <span class="w-1 h-1 bg-stone-300 shrink-0"></span>
        <span x-text="appointment.package_name" class="text-stone-400 font-normal"></span>
      </p>
      <div class="flex flex-wrap items-center gap-2 mt-1.5">
        <span x-text="appointment.status_label" class="text-[11px] px-2 py-0.5 bg-stone-100 border border-stone-200 text-stone-700 font-medium tracking-wide"></span>
        
        <template x-if="appointment.can_pay && appointment.payment_expiry_time">
          <span class="text-[11px] px-2 py-0.5 bg-red-50 border border-red-100 text-red-700 font-medium flex items-center gap-1">
            <i class="ri-error-warning-line text-xs"></i>
            <span>Bayar sebelum <span class="font-bold" x-text="appointment.payment_expiry_time"></span> WITA</span>
          </span>
        </template>
      </div>
    </div>
    {{-- info end --}}

    {{-- action start --}}
    <div class="flex items-center gap-4 ml-auto w-full sm:w-auto justify-between sm:justify-end">
      
      {{-- btn bayar start --}}
      <x-shared.button
        variant="dark"
        value="Bayar Sekarang"
        xLoading="state.isProcessingPayment === appointment.booking_code"
        loadingText="Memproses..."
        x-show="appointment.can_pay"
        x-on:click.stop="triggerRepay(appointment.booking_code)"
        x-bind:disabled="state.isProcessingPayment === appointment.booking_code"
        class="w-auto shrink-0"
      />
      {{-- btn bayar end --}}

      {{-- icon expand start --}}
      <i class="ri-arrow-down-s-line text-xl text-stone-400 transition-transform duration-300"
        x-bind:class="state.selectedAppointment?.booking_code === appointment.booking_code ? 'rotate-180 text-stone-900' : ''"></i>
      {{-- icon expand end --}}

    </div>
    {{-- action end --}}

  </div>
  {{-- card header end --}}

  {{-- expanded detail card start --}}
  <div
    x-show="state.selectedAppointment?.booking_code === appointment.booking_code"
    x-collapse>

    <div class="p-5 flex flex-col">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-sm border-y border-stone-300 py-6">

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

        {{-- biaya start --}}
        <div class="sm:col-span-2 lg:col-span-1">
          <div class="p-3 border border-stone-300">
            <div class="flex justify-between items-center mb-2">
              <span class="text-stone-500">Status</span>
              <span class="font-semibold text-stone-900" x-text="appointment.status_label"></span>
            </div>
            <div class="flex justify-between items-center pt-2 border-t border-stone-100">
              <span class="text-stone-500">Total Biaya</span>
              <span class="font-bold text-stone-900" x-text="appointment.formatted_total_price"></span>
            </div>
          </div>
        </div>
        {{-- biaya end --}}

      </div>

      {{-- action footer start --}}
      <div class="flex flex-col-reverse items-center gap-3 w-full pt-5 lg:flex-row">

        {{-- section kiri start --}}
        <div class="w-full lg:mr-auto lg:w-auto">
          {{-- btn batal start --}}
          <x-shared.button
            variant="ghost"
  
            value="Batalkan Reservasi"
            xLoading="state.isCancelling === appointment.booking_code"
            loadingText="Membatalkan..."
            x-show="appointment.can_cancel"
            x-on:click="triggerCancel(appointment.booking_code)"
            x-bind:disabled="state.isCancelling === appointment.booking_code"
            class="w-full text-stone-500 hover:border-red-200 hover:bg-red-50 hover:text-red-600 lg:w-auto"
          />
          {{-- btn batal end --}}
        </div>
        {{-- section kiri end --}}

        {{-- section kanan start --}}
        <div class="flex w-full flex-col-reverse gap-3 lg:w-auto lg:flex-row">
          {{-- link cetak kuitansi start --}}
          <x-shared.button
            as="a"
            variant="outline"
  
            value="Unduh Kuitansi"
            x-show="appointment.receipt_url"
            x-bind:href="appointment.receipt_url"
            target="_blank"
            rel="noopener noreferrer"
            class="w-full text-center lg:w-auto"
          />
          {{-- link cetak kuitansi end --}}

          {{-- link hasil foto start --}}
          <x-shared.button
            as="a"
            variant="dark"
  
            value="Hasil Foto"
            x-show="appointment.gdrive_link && appointment.status === 'Selesai'"
            x-bind:href="appointment.gdrive_link"
            target="_blank"
            rel="noopener noreferrer"
            class="w-full text-center lg:w-auto"
          />
          {{-- link hasil foto end --}}

          {{-- btn reschedule start --}}
          <x-shared.button
            variant="outline"
  
            value="Ubah Jadwal"
            x-show="appointment.can_reschedule"
            x-on:click="openRescheduleDrawer(appointment)"
            class="w-full lg:w-auto"
          />
          {{-- btn reschedule end --}}
        </div>
        {{-- section kanan end --}}
      </div>
      {{-- action footer end --}}
    </div>
  </div>
  {{-- expanded detail card end --}}

</div>
