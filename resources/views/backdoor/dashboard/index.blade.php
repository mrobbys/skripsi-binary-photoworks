@php
  use App\Domains\Booking\Enums\BookingStatus;
  
  $breadcrumbs = [['label' => 'Dashboard', 'url' => '']];

  $getBadgeVariant = fn(BookingStatus $status) => match ($status) {
      BookingStatus::PENDING => 'warning',
      BookingStatus::DP_PAID => 'info',
      BookingStatus::SUCCESS => 'success',
      BookingStatus::CANCELLED => 'danger',
      BookingStatus::DONE => 'primary',
  };
@endphp

<x-layouts.backdoor
  title="Dashboard"
  :breadcrumbs="$breadcrumbs"
  js-module="backdoor/dashboard/Dashboard"
>
  <x-slot:content>
    <div
      x-data="Dashboard"
      x-init="fetchChartData()"
      x-cloak
      class="space-y-8"
    >

      {{-- stats overview section start --}}
      <x-backdoor.dashboard.stats-overview :stats="$stats" />
      {{-- stats overview section end --}}

      {{-- analytics charts section start --}}
      <section>
        <div class="mb-6 flex items-center justify-between border-b border-stone-200 pb-4">
          <div>
            <h2 class="text-xs font-semibold uppercase tracking-wider text-stone-500">Analitik Performa</h2>
          </div>
          <div class="flex items-center gap-2">
            <label for="yearPicker" class="text-xs font-semibold uppercase tracking-wider text-stone-500">Tahun</label>
            <div class="w-24">
              <select
                id="yearPicker"
                x-data="choices({ searchEnabled: false, itemSelectText: '', shouldSort: false })"
                x-model="state.selectedYear"
                x-on:change="debouncedFetchCharts"
              >
                @foreach ($availableYears as $y)
                  <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
          {{-- chart 1: tren booking start --}}
          <x-backdoor.dashboard.booking-trend-chart />
          {{-- chart 1: tren booking end --}}

          {{-- chart 2: tren revenue start --}}
          <x-backdoor.dashboard.revenue-trend-chart />
          {{-- chart 2: tren revenue end --}}

          {{-- chart 3: proporsi paket start --}}
          <x-backdoor.dashboard.package-proportion-chart />
          {{-- chart 3: proporsi paket end --}}

          {{-- chart 4: proporsi addon start --}}
          <x-backdoor.dashboard.addon-proportion-chart />
          {{-- chart 4: proporsi addon end --}}
        </div>
      </section>
      {{-- analytics charts section end --}}

      {{-- tables section start --}}
      <section>
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
          {{-- today schedule table start --}}
          <x-backdoor.dashboard.today-schedule-table :today="$today" :get-badge-variant="$getBadgeVariant" />
          {{-- today schedule table end --}}

          {{-- recent reservations table start --}}
          <x-backdoor.dashboard.recent-reservations-table :recent="$recent" :get-badge-variant="$getBadgeVariant" />
          {{-- recent reservations table end --}}
        </div>
      </section>
      {{-- tables section end --}}

    </div>
  </x-slot:content>
</x-layouts.backdoor>
