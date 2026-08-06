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
  ariaLabelledBy="reschedule-drawer-title"
  formAction="submitReschedule()"
>

  {{-- info jadwal lama start --}}
  <div
    class="mb-6 flex flex-col border-l-2 border-stone-400 bg-stone-100 px-3 py-2.5 text-sm sm:flex-row sm:items-center sm:justify-between"
  >
    <span class="text-xs font-medium uppercase tracking-wide text-stone-500">Jadwal Saat Ini:</span>
    <span
      class="font-semibold text-stone-900"
      x-text="state.rescheduleTarget?.formatted_date + ' ■ ' + state.rescheduleTarget?.formatted_time"
    ></span>
  </div>
  {{-- info jadwal lama end --}}

  {{-- calendar flatpick start --}}
  <div class="mb-6">
    <h3 class="mb-4 text-sm font-semibold text-stone-900">Pilih Tanggal Baru</h3>
    <div class="flex w-full max-w-xs justify-center">
      <div class="w-full">
        <input
          type="text"
          class="hidden"
          x-ref="rescheduleCalendarInput"
          x-init="$watch('state.isRescheduleOpen', (val) => {
              if (val) {
                  $nextTick(() => initRescheduleCalendar($refs.rescheduleCalendarInput));
              }
          })"
        >
      </div>
    </div>
  </div>
  {{-- calendar flatpick end --}}

  <div class="mb-6 h-px bg-stone-200"></div>

  {{-- slot waktu start --}}
  <div id="slot-waktu-area">
    <h3 class="mb-4 text-sm font-semibold text-stone-900">
      <template x-if="state.selectedRescheduleDate">
        <span x-text="'Pilih Waktu — ' + state.formattedDate"></span>
      </template>
      <template x-if="!state.selectedRescheduleDate">
        <span class="flex items-center gap-2 text-sm font-normal text-stone-500">
          <i
            class="ri-calendar-event-line text-stone-400"
            aria-hidden="true"
          ></i>
          Pilih tanggal terlebih dahulu.
        </span>
      </template>
    </h3>

    {{-- lading slot start --}}
    <template x-if="state.isFetchingRescheduleSlots">
      <div class="flex items-center gap-2 py-4 text-sm text-stone-500">
        <i
          class="ri-loader-4-line animate-spin"
          aria-hidden="true"
        ></i>
        <span>Memuat slot tersedia...</span>
      </div>
    </template>
    {{-- lading slot end --}}

    {{-- grid slot waktu start --}}
    <template x-if="state.selectedRescheduleDate && !state.isFetchingRescheduleSlots">
      <div>
        <template x-if="state.rescheduleSlots.length === 0">
          <p class="py-4 text-sm text-stone-400">Tidak ada slot tersedia pada tanggal ini.</p>
        </template>

        <div class="grid grid-cols-2 gap-3">
          <template
            x-for="slot in state.rescheduleSlots"
            :key="slot.start_time"
          >
            <x-shared.button
              type="button"
              variant="custom"
              x-on:click="selectRescheduleSlot(slot)"
              x-bind:class="state.selectedRescheduleSlot?.start_time === slot.start_time ?
                  'border-stone-800 bg-stone-100 font-semibold text-stone-900' :
                  'border-stone-200 bg-stone-50 text-stone-600 hover:border-stone-300'"
              class="w-full border py-3 text-sm"
            >
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
      variant="ghost"
      value="Batal"
      x-on:click="closeRescheduleDrawer()"
      x-bind:disabled="state.isRescheduling"
    />
    <x-shared.button
      type="submit"
      variant="dark"
      value="Konfirmasi Ubah Jadwal"
      xLoading="state.isRescheduling"
      loadingText="Menyimpan..."
      x-bind:disabled="!state.selectedRescheduleSlot || state.isRescheduling"
    />
  </x-slot:footer>
  {{-- footer end --}}

</x-shared.drawer>
