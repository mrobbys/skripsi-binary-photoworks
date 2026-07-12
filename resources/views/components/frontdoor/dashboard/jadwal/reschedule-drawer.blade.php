{{--
  * COMPONENT: RESCHEDULE DRAWER
  * Slide-over drawer untuk fitur Ubah Jadwal.
  *
  * Menggunakan "x-shared.drawer" sebagai wrapper.
  * Terhubung ke Alpine state dari Dashboard.js (via useReschedule.js).
  *
  * State:
  *   - state.isRescheduleOpen
  *   - state.rescheduleTarget
  *   - state.selectedRescheduleDate
  *   - state.selectedRescheduleSlot
  *   - state.rescheduleSlots
  *   - state.isFetchingRescheduleSlots
  *   - state.isRescheduling
  *
  * Methods:
  *   - closeRescheduleDrawer()
  *   - initRescheduleCalendar($el)
  *   - selectRescheduleSlot(slot)
  *   - submitReschedule()
--}}

<x-shared.drawer
  openState="state.isRescheduleOpen"
  closeAction="closeRescheduleDrawer()"
  title="Ubah Jadwal"
  maxWidth="max-w-lg"
  ariaLabelledBy="reschedule-drawer-title">

  {{-- Info Jadwal Lama (Versi Compact) --}}
  <div
    class="mb-6 py-2.5 px-3 bg-stone-100 border-l-2 border-stone-400 flex flex-col sm:flex-row sm:justify-between sm:items-center text-sm">
    <span class="text-stone-500 font-medium text-xs uppercase tracking-wide">Jadwal Saat Ini:</span>
    <span class="font-semibold text-stone-900"
      x-text="state.rescheduleTarget?.formatted_date + ' ■ ' + state.rescheduleTarget?.formatted_time"></span>
  </div>

  {{-- calendar flatpick start --}}
  <div class="mb-6">
    <p class="text-sm font-semibold text-stone-900 mb-4">Pilih Tanggal Baru</p>
    <div class="flex justify-center">
      <input
        type="text"
        class="hidden"
        x-ref="rescheduleCalendarInput"
        x-init="$watch('state.isRescheduleOpen', (val) => {
            if (val) {
                $nextTick(() => initRescheduleCalendar($refs.rescheduleCalendarInput));
            }
        })">
    </div>
  </div>
  {{-- calendar flatpick end --}}

  <div class="h-px bg-stone-200 mb-6"></div>

  {{-- slot waktu start --}}
  <div id="slot-waktu-area">
    <p class="text-sm font-semibold text-stone-900 mb-4">
      <template x-if="state.selectedRescheduleDate">
        <span x-text="'Pilih Waktu — ' + state.formattedDate"></span>
      </template>
      <template x-if="!state.selectedRescheduleDate">
        <span class="text-stone-500 font-normal text-sm flex items-center gap-2">
          <i class="ri-calendar-event-line text-stone-400"></i>
          Pilih tanggal terlebih dahulu.
        </span>
      </template>
    </p>

    {{-- lading slot start --}}
    <template x-if="state.isFetchingRescheduleSlots">
      <div class="flex items-center gap-2 text-stone-500 text-sm py-4">
        <i class="ri-loader-4-line animate-spin"></i>
        <span>Memuat slot tersedia...</span>
      </div>
    </template>
    {{-- lading slot end --}}

    {{-- grid slot waktu start --}}
    <template x-if="state.selectedRescheduleDate && !state.isFetchingRescheduleSlots">
      <div>
        <template x-if="state.rescheduleSlots.length === 0">
          <p class="text-sm text-stone-400 py-4">Tidak ada slot tersedia pada tanggal ini.</p>
        </template>

        <div class="grid grid-cols-2 gap-3">
          <template x-for="slot in state.rescheduleSlots" :key="slot.start_time">
            <x-shared.button
              type="button"
              size="custom"
              x-on:click="selectRescheduleSlot(slot)"
              x-bind:class="{
                  'border-stone-500 bg-stone-100 text-stone-900': state.selectedRescheduleSlot?.start_time === slot
                      .start_time,
                  'border-stone-200 bg-stone-50 text-stone-600 hover:border-stone-300': state.selectedRescheduleSlot
                      ?.start_time !== slot.start_time,
              }"
              class="w-full border py-3 text-sm">
              <span x-text="slot.start_time"></span>
            </x-shared.button>
          </template>
        </div>
      </div>
    </template>
    {{-- grid slot waktu end --}}
  </div>
  {{-- slot waktu end --}}

  {{-- footer start --}}
  <x-slot:footer>
    <x-shared.button
      type="button"
      x-on:click="closeRescheduleDrawer()"
      x-bind:disabled="state.isRescheduling"
      class="text-stone-600 hover:text-stone-900 transition font-semibold text-sm cursor-pointer disabled:opacity-50"
      value="Batal" />
    <x-shared.button
      type="button"
      x-on:click="submitReschedule()"
      x-bind:disabled="!state.selectedRescheduleSlot || state.isRescheduling"
      class="bg-stone-700 text-stone-50 px-4 py-2 border border-stone-700 hover:bg-stone-800 font-semibold text-sm tracking-wide disabled:opacity-50 disabled:pointer-events-none">
      <span x-text="state.isRescheduling ? 'Menyimpan...' : 'Konfirmasi Ubah Jadwal'"></span>
    </x-shared.button>
  </x-slot:footer>
  {{-- footer end --}}

</x-shared.drawer>
