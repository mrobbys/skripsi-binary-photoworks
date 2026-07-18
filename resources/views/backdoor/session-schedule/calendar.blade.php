@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard')],
      ['label' => 'Jadwal Sesi', 'url' => ''],
      ['label' => 'Kalender Sesi', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Kalender Sesi"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/session-schedule/calendar/Calendar"
>

  <x-slot:content>
    <div
      x-data="Calendar"
      x-init="calendarSession($refs.calendarEl)"
      x-cloak
      class="w-full space-y-6"
    >

      {{-- page header --}}
      <x-backdoor.shared.page-header title="Kalender Sesi" />

      {{-- calendar card --}}
      <div class="border border-stone-200 bg-stone-50 p-6 max-w-6xl mx-auto">

        <div
          x-ref="calendarEl"
          id="fc-calendar"
        ></div>

        {{-- legend  start --}}
        <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2 border-t border-stone-300 pt-4">

          {{-- sesi dikonfirmasi start --}}
          <div class="flex items-center gap-2">
            <span class="inline-block h-3 w-3 bg-stone-700"></span>
            <span class="text-xs font-semibold uppercase tracking-wider text-stone-600">
              Sesi Dikonfirmasi
            </span>
          </div>
          {{-- sesi dikonfirmasi end --}}

          {{-- sesi selesai start --}}
          <div class="flex items-center gap-2">
            <span class="inline-block h-3 w-3 bg-stone-400"></span>
            <span class="text-xs font-semibold uppercase tracking-wider text-stone-600">
              Sesi Selesai
            </span>
          </div>
          {{-- sesi selesai end --}}

          {{-- tersedia start --}}
          <div class="flex items-center gap-2">
            <span class="inline-block h-3 w-3 border border-stone-400 bg-transparent"></span>
            <span class="text-xs font-semibold uppercase tracking-wider text-stone-600">
              Tersedia
            </span>
          </div>
          {{-- tersedia end --}}

          {{-- hari libur start --}}
          <div class="flex items-center gap-2">
            <span class="inline-block h-3 w-3 border border-pink-200 bg-pink-100"></span>
            <span class="text-xs font-semibold uppercase tracking-wider text-stone-600">
              Hari Libur
            </span>
          </div>
          {{-- hari libur end --}}

        </div>
        {{-- legend end --}}

      </div>
      {{-- calendar card end --}}

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
