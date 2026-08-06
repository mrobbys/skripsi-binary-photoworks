<x-layouts.frontdoor.index
  title="Jadwal Saya - Dashboard"
  jsModule="frontdoor/dashboard/Dashboard">

  <x-slot:heads>
    <style>
      /* Paksa wadah utama dan internal kalender untuk merespons 100% */
      .flatpickr-calendar,
      .flatpickr-innerContainer,
      .flatpickr-rContainer,
      .flatpickr-days,
      .dayContainer {
          width: 100% !important;
          max-width: 100% !important;
          min-width: unset !important;
      }
      /* Pastikan setiap kotak tanggal terbagi rata 1/7 bagian */
      .flatpickr-day {
          max-width: 14.28% !important;
          width: auto !important;
      }
    </style>
    
    {{-- midtrans --}}
    <script type="text/javascript" src="{{ config('midtrans.snap_js_url') }}"
      data-client-key="{{ config('midtrans.client_key') }}"></script>
  </x-slot:heads>

  <x-slot:content>
    <div class="w-full"
      x-data="Dashboard"
      x-init="state.activeDays = {{ Js::from($activeDays) }}"
      x-cloak>

      <div class="mx-auto max-w-7xl w-full flex flex-col md:flex-row gap-8">

        {{-- sidebar nav start --}}
        <x-frontdoor.dashboard.sidebar />
        {{-- sidebar nav end --}}

        {{-- main content start --}}
        <div class="w-full">
          <section class="border border-stone-300 py-6 px-6 space-y-6">
            <h1 class="text-xl font-bold text-stone-900">Jadwal Sesi Foto Anda</h1>

            {{-- tab filter start --}}
            <x-frontdoor.dashboard.jadwal.tabs />
            {{-- tab filter end --}}

            {{-- loading skeleton start --}}
            <div x-show="state.isLoading" class="space-y-4">
              <template x-for="i in 5">
                <x-skeleton.history-booking-card />
              </template>
            </div>
            {{-- loading skeleton end --}}

            {{-- empty element start --}}
            <x-frontdoor.dashboard.jadwal.empty-element />
            {{-- empty element end --}}

            {{-- card container start --}}
            <div x-show="!state.isLoading" class="space-y-4">
              <template x-for="appointment in state.appointments" :key="appointment.booking_code">

                {{-- card item start --}}
                <x-frontdoor.dashboard.jadwal.booking-card />
                {{-- card item end --}}

              </template>
            </div>
            {{-- card container end --}}

            <div x-show="!state.isLoading && state.total > 5" class="pt-6 border-t border-stone-200">
              <x-frontdoor.shared.pagination />
            </div>
          </section>

          {{-- footer start --}}
          <x-frontdoor.dashboard.jadwal.help-footer />
          {{-- footer end --}}

        </div>
        {{-- main content end --}}
      </div>

      {{-- drawer form --}}
      <x-frontdoor.dashboard.jadwal.reschedule-drawer />
    </div>
  </x-slot:content>

</x-layouts.frontdoor.index>
