@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Data Master', 'url' => '#'],
      ['label' => 'Jadwal Operasional', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Jadwal Operasional Studio"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/master-data/schedule/Schedule"
>

  <x-slot:content>
    <div
      x-data="Schedule"
      x-cloak
      class="w-full space-y-6"
    >

      {{-- page header start --}}
      <x-backdoor.shared.page-header title="Jadwal Operasional Studio" />
      {{-- page header end --}}

      {{-- table card start --}}
      <div class="relative overflow-visible border border-stone-200 bg-stone-50 p-6">

        {{-- table container start --}}
        <x-backdoor.table.container headers="Hari,Jam Buka,Jam Tutup,Status">
          <template
            x-for="(item, index) in table.data"
            x-bind:key="item.id"
          >
            <tr
              class="border-b border-stone-200 transition hover:bg-stone-100"
              x-bind:class="state.savingIds.has(item.id) ? 'opacity-60' : ''"
              x-show="!table.isLoading"
              x-cloak
            >

              {{-- hari start --}}
              <x-backdoor.table.cell
                class="font-semibold text-stone-900"
                x-text="item.day_label"
              />
              {{-- hari end --}}

              {{-- jam buka start --}}
              <x-backdoor.table.cell>
                <input
                  type="text"
                  x-bind:id="'start-time-' + item.id"
                  x-bind:value="item.start_time"
                  x-bind:disabled="state.savingIds.has(item.id)"
                  x-bind:aria-label="'Jam buka ' + item.day_label"
                  placeholder="00:00"
                  x-init="initTimePicker($el, item, 'start_time')"
                  class="w-24 border border-stone-300 bg-white px-3 py-1.5 text-sm text-stone-900 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 disabled:cursor-not-allowed disabled:opacity-50"
                />
              </x-backdoor.table.cell>
              {{-- jam buka end --}}

              {{-- jam tutup start --}}
              <x-backdoor.table.cell>
                <input
                  type="text"
                  x-bind:id="'end-time-' + item.id"
                  x-bind:value="item.end_time"
                  x-bind:disabled="state.savingIds.has(item.id)"
                  x-bind:aria-label="'Jam tutup ' + item.day_label"
                  placeholder="00:00"
                  x-init="initTimePicker($el, item, 'end_time')"
                  class="w-24 border border-stone-300 bg-white px-3 py-1.5 text-sm text-stone-900 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 disabled:cursor-not-allowed disabled:opacity-50"
                />
              </x-backdoor.table.cell>
              {{-- jam tutup end --}}

              {{-- status toggle start --}}
              <x-backdoor.table.cell>
                <x-backdoor.shared.toggle
                  x-bind:checked="item.is_active"
                  x-bind:disabled="state.savingIds.has(item.id)"
                  x-bind:aria-label="'Status aktif ' + item.day_label"
                  x-on:change="toggleScheduleStatus(item.id)"
                />
              </x-backdoor.table.cell>
              {{-- status toggle end --}}

            </tr>
          </template>
        </x-backdoor.table.container>
        {{-- table container end --}}

      </div>
      {{-- table card end --}}

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
