<div class="max-w-5xl mx-auto px-6">
  {{-- Header --}}
  <div class="text-center mb-12">
    <h2 class="text-3xl font-bold font-heading text-stone-900 mb-2">Pilih Tanggal & Waktu</h2>
    <p class="text-stone-500 text-sm md:text-base font-medium">Pilih jadwal sesi yang Anda inginkan</p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-start">
    {{-- section kiri start --}}
    <div class="flex justify-center">
      <input type="text" x-ref="calendarInput" class="hidden"
        x-init="$nextTick(() => initCalendar($el))">
    </div>
    {{-- section kiri end --}}

    {{-- section kanan start (Pilih Jam) --}}
    <div class="w-full mx-auto md:mx-0">
      <h3 class="text-xl font-bold text-stone-900 mb-6 min-h-[28px]">
        <template x-if="state.selectedDate">
          <span x-text="state.formattedDate"></span>
        </template>
        <template x-if="!state.selectedDate">
          <span class="text-stone-500 font-normal text-sm flex items-center gap-2">
            <i class="ri-calendar-event-line text-stone-400"></i>
            Pilih tanggal terlebih dahulu.
          </span>
        </template>
      </h3>

      <div class="min-h-[220px]">
        <template x-if="state.isFetchingSlots">
          <div class="flex items-center gap-2 text-stone-500 text-sm py-4">
            <i class="ri-loader-4-line animate-spin"></i>
            <span>Memuat slot tersedia...</span>
          </div>
        </template>

        <template x-if="state.selectedDate && !state.isFetchingSlots">
          <div>
            <div class="grid grid-cols-2 gap-3">
              <template x-for="slot in state.availableSlots" :key="slot.start_time">
                <x-frontdoor.booking.time-slot-button />
              </template>
            </div>
            <template x-if="state.availableSlots.length === 0">
              <p class="text-sm text-stone-400 py-4">Tidak ada slot tersedia pada tanggal ini.</p>
            </template>
          </div>
        </template>
      </div>
    </div>
    {{-- section kanan end --}}
  </div>

  <div class="my-8 flex flex-col sm:flex-row gap-12">
    <x-shared.button size="lg" value="Kembali" x-on:click="prevStep()"
      class="w-full border border-stone-300 bg-transparent text-stone-800 hover:bg-stone-50">
      <x-slot:iconLeft>
        <i class="ri-arrow-left-line"></i>
      </x-slot:iconLeft>
    </x-shared.button>
    <x-shared.button size="lg" value="Lanjutkan ke Layanan Tambahan"
      class="w-full bg-stone-800 text-white hover:bg-stone-700"
      x-bind:disabled="!state.selectedSlot" x-on:click="nextStep()">
      <x-slot:iconRight>
        <i class="ri-arrow-right-line"></i>
      </x-slot:iconRight>
    </x-shared.button>
  </div>
</div>
