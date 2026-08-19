{{-- schedule selection card start --}}
<div class="border border-stone-300">
  <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
    <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Jadwal Sesi</h2>
  </div>
  <div class="grid grid-cols-1 gap-4 p-5 xl:grid-cols-2">

    {{-- tanggal sesi start --}}
    <div>
      <label
        for="booking_date"
        class="mb-2 block text-xs font-semibold uppercase tracking-wide text-stone-700"
      >
        Tanggal Sesi <span class="text-red-500">*</span>
      </label>
      <input
        type="text"
        id="booking_date"
        name="booking_date"
        x-model="state.bookingDate"
        x-init="initDatePicker($el)"
        placeholder="--- Pilih Tanggal ---"
        readonly
        class="w-full cursor-pointer border border-stone-300 bg-white px-3 py-2.5 text-sm text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500"
      >
      <template x-if="state.errors['booking_date']">
        <p
          class="mt-1.5 text-xs text-red-600"
          x-text="Array.isArray(state.errors['booking_date']) ? state.errors['booking_date'][0] : state.errors['booking_date']"
        ></p>
      </template>
    </div>
    {{-- tanggal sesi end --}}

    {{-- slot waktu start --}}
    <div>
      <label
        for="start_time"
        class="mb-2 block text-xs font-semibold uppercase tracking-wide text-stone-700"
      >
        Slot Waktu <span class="text-red-500">*</span>
      </label>
      <select
        id="start_time"
        name="start_time"
        x-data="choices({ placeholder: true, placeholderValue: '--- Pilih Tanggal Terlebih Dahulu ---' })"
        x-init="initTimeSlotChoices($el, $watch)"
        x-modelable="value"
        x-model="state.startTime"
      >
        <option value="">--- Pilih Tanggal Terlebih Dahulu ---</option>
      </select>
      <template x-if="state.errors['start_time']">
        <p
          class="mt-1.5 text-xs text-red-600"
          x-text="Array.isArray(state.errors['start_time']) ? state.errors['start_time'][0] : state.errors['start_time']"
        ></p>
      </template>
    </div>
    {{-- slot waktu end --}}

  </div>
</div>
{{-- schedule selection card end --}}
