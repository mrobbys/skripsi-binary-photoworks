{{-- booking trend chart start --}}
<div class="border border-stone-200 bg-stone-50 p-4">
  <div class="mb-4 border-b border-stone-200 pb-3">
    <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-600">Tren Volume Reservasi</h3>
  </div>

  {{-- skeleton loading start --}}
  <div x-show="state.isLoadingCharts" x-cloak>
    <x-skeleton.dashboard-chart type="bar" />
  </div>
  {{-- skeleton loading end --}}

  {{-- empty state start --}}
  <div
    x-show="!state.isLoadingCharts && !hasChartData(state.chartData.bookingTrend)"
    x-cloak
    class="flex h-64 items-center justify-center text-sm text-stone-400 sm:h-72"
  >
    Belum ada data reservasi pada tahun ini
  </div>
  {{-- empty state end --}}

  {{-- chart canvas start --}}
  <div
    x-show="!state.isLoadingCharts && hasChartData(state.chartData.bookingTrend)"
    class="relative h-64 w-full sm:h-72"
  >
    <canvas id="bookingTrendChart"></canvas>
  </div>
  {{-- chart canvas end --}}
</div>
{{-- booking trend chart end --}}
