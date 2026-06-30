<div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
  {{-- Kiri: Kalender Inline --}}
  <div>
    <h3 class="text-xl font-bold text-stone-900 mb-4">Pilih Tanggal</h3>
    <input type="text" x-ref="calendarInput" class="hidden"
      x-init="$nextTick(() => initCalendar($el))">
    {{-- Flatpickr inline renders here --}}
  </div>

  {{-- Kanan: Slot Waktu --}}
  <div>
    <h3 class="text-xl font-bold text-stone-900 mb-4">Pilih Jam Sesi</h3>

    <template x-if="!state.selectedDate">
      <p class="text-sm text-stone-400">Pilih tanggal terlebih dahulu.</p>
    </template>

    <template x-if="state.isFetchingSlots">
      <div class="flex items-center gap-2 text-stone-500 text-sm">
        <i class="ri-loader-4-line animate-spin"></i>
        <span>Memuat slot tersedia...</span>
      </div>
    </template>

    <template x-if="state.selectedDate && !state.isFetchingSlots">
      <div>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
          <template x-for="slot in state.availableSlots" :key="slot.start_time">
            <x-frontdoor.booking.time-slot-button />
          </template>
        </div>
        <template x-if="state.availableSlots.length === 0">
          <p class="mt-4 text-sm text-stone-400">Tidak ada slot tersedia pada tanggal ini.</p>
        </template>
      </div>
    </template>

    <div class="mt-8 flex gap-3">
      <x-shared.button variant="ghost" x-on:click="prevStep()">
        <i class="ri-arrow-left-line mr-1"></i> Kembali
      </x-shared.button>
      <x-shared.button variant="primary" class="flex-1"
        x-bind:disabled="!state.selectedSlot" x-on:click="nextStep()">
        Lanjutkan ke Layanan Tambahan
        <i class="ri-arrow-right-line ml-2"></i>
      </x-shared.button>
    </div>
  </div>
</div>
