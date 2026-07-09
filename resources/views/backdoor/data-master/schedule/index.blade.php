@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard')],
      ['label' => 'Data Master', 'url' => '#'],
      ['label' => 'Jadwal Operasional', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Jadwal Operasional Studio"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/master-data/schedule/Schedule">

  <x-slot:content>
    <div
      x-data="Schedule"
      class="w-full space-y-6">

      {{-- Page Header --}}
      <x-backdoor.shared.page-header title="Jadwal Operasional Studio" />

      {{-- Table Card --}}
      <div class="bg-stone-50 border border-stone-200 p-6">

        {{-- Table Container --}}
        <x-backdoor.table.container headers="Hari,Jam Buka,Jam Tutup,Status">
          <template
            x-for="(item, index) in table.data"
            x-bind:key="item.id">
            <tr
              class="hover:bg-stone-100 border-b border-stone-200 transition"
              x-bind:class="state.savingIds.has(item.id) ? 'opacity-60' : ''"
              x-show="!table.isLoading"
              x-cloak>

              {{-- Hari --}}
              <x-backdoor.table.cell class="font-semibold text-stone-900" x-text="item.day_label" />

              {{-- Jam Buka (Flatpickr) --}}
              <x-backdoor.table.cell>
                <input
                  type="text"
                  x-bind:id="'start-time-' + item.id"
                  x-bind:value="item.start_time"
                  x-bind:disabled="state.savingIds.has(item.id)"
                  x-init="window.flatpickr($el, {
                      enableTime: true,
                      noCalendar: true,
                      dateFormat: 'H:i',
                      time_24hr: true,
                      defaultDate: item.start_time,
                      static: true,
                      onClose(selectedDates, dateStr) {
                          if (dateStr && dateStr !== item.start_time) {
                              saveScheduleTime(item.id, dateStr, item.end_time);
                          }
                      }
                  });"
                  class="w-24 border border-stone-300 bg-white px-3 py-1.5 text-stone-900 text-sm focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 disabled:opacity-50 disabled:cursor-not-allowed" />
              </x-backdoor.table.cell>

              {{-- Jam Tutup (Flatpickr) --}}
              <x-backdoor.table.cell>
                <input
                  type="text"
                  x-bind:id="'end-time-' + item.id"
                  x-bind:value="item.end_time"
                  x-bind:disabled="state.savingIds.has(item.id)"
                  x-init="window.flatpickr($el, {
                      enableTime: true,
                      noCalendar: true,
                      dateFormat: 'H:i',
                      time_24hr: true,
                      defaultDate: item.end_time,
                      static: true,
                      onClose(selectedDates, dateStr) {
                          if (dateStr && dateStr !== item.end_time) {
                              saveScheduleTime(item.id, item.start_time, dateStr);
                          }
                      }
                  });"
                  class="w-24 border border-stone-300 bg-white px-3 py-1.5 text-stone-900 text-sm focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 disabled:opacity-50 disabled:cursor-not-allowed" />
              </x-backdoor.table.cell>

              {{-- Status Toggle --}}
              <x-backdoor.table.cell>
                <x-backdoor.shared.toggle
                  x-bind:checked="item.is_active"
                  x-bind:disabled="state.savingIds.has(item.id)"
                  x-on:change="toggleScheduleStatus(item.id, item.is_active)" />
              </x-backdoor.table.cell>

            </tr>
          </template>
        </x-backdoor.table.container>

      </div>

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
