{{-- package proportion chart start --}}
<div class="border border-stone-200 bg-stone-50 p-4">
  <div class="mb-4 border-b border-stone-200 pb-3">
    <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-600">Proporsi Paket Terlaris</h3>
  </div>

  {{-- skeleton loading start --}}
  <div x-show="state.isLoadingCharts" x-cloak>
    <x-skeleton.dashboard-chart type="pie" />
  </div>
  {{-- skeleton loading end --}}

  {{-- empty state start --}}
  <div
    x-show="!state.isLoadingCharts && !hasChartData(state.chartData.package)"
    x-cloak
    class="flex h-64 items-center justify-center text-sm text-stone-400 sm:h-72"
  >
    Belum ada data paket pada tahun ini
  </div>
  {{-- empty state end --}}

  {{-- chart canvas start --}}
  <div
    x-show="!state.isLoadingCharts && hasChartData(state.chartData.package)"
    class="relative h-64 w-full sm:h-72"
  >
    <canvas id="packageChart"></canvas>
  </div>
  {{-- chart canvas end --}}
</div>
{{-- package proportion chart end --}}
