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
    >
      <section class="mb-8">
        <h2 class="mb-4 text-xs font-semibold uppercase tracking-wider text-stone-500">
          Kartu Statistik
        </h2>
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
          <x-backdoor.shared.stats-card
            label="Total Reservasi (Bulan Ini)"
            value="{{ $stats['totalReservasi'] }}"
            suffix="Sesi"
          />
          <x-backdoor.shared.stats-card
            label="Total Pendapatan (Bulan Ini)"
            value="{{ $stats['totalPendapatan'] }}"
          />
          <x-backdoor.shared.stats-card
            label="Total Klien Terdaftar"
            value="{{ $stats['totalKlien'] }}"
            suffix="Klien"
          />
          <x-backdoor.shared.stats-card
            label="Sesi Menunggu (Hari Ini)"
            value="{{ $stats['sesiMenunggu'] }}"
            suffix="Sesi"
          />
        </div>
      </section>

      <section class="mb-8">
        <div class="mb-6 flex items-center justify-between border-b border-stone-200 pb-4">
          <div class="w-full">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-stone-500">Analitik Performa</h2>
          </div>
          <div class="flex w-full items-center justify-end gap-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-stone-500">Tahun</span>
            <select
              id="yearPicker"
              x-data="choices({ searchEnabled: false, itemSelectText: '', shouldSort: false })"
              x-model="state.selectedYear"
              @change="debouncedFetchCharts"
            >
              @foreach ($availableYears as $y)
                <option value="{{ $y }}">{{ $y }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
          <div class="border border-stone-200 p-6">
            <p class="mb-4 text-xs font-semibold uppercase tracking-wider text-stone-500">Tren Volume Reservasi</p>
            <template x-if="state.isLoadingCharts">
              <div class="flex h-48 items-center justify-center text-sm text-stone-400">Memuat data...</div>
            </template>
            <template x-if="!state.isLoadingCharts && !hasChartData(state.chartData.bookingTrend)">
              <div class="flex h-48 items-center justify-center text-sm text-stone-400">Belum ada data reservasi pada tahun ini.</div>
            </template>
            <canvas
              id="bookingTrendChart"
              x-show="!state.isLoadingCharts && hasChartData(state.chartData.bookingTrend)"
              class="max-h-72"
            ></canvas>
          </div>

          <div class="border border-stone-200 p-6">
            <p class="mb-4 text-xs font-semibold uppercase tracking-wider text-stone-500">Tren Pendapatan Per Bulan</p>
            <template x-if="state.isLoadingCharts">
              <div class="flex h-48 items-center justify-center text-sm text-stone-400">Memuat data...</div>
            </template>
            <template x-if="!state.isLoadingCharts && !hasChartData(state.chartData.revenueTrend)">
              <div class="flex h-48 items-center justify-center text-sm text-stone-400">Belum ada data pendapatan pada tahun ini.</div>
            </template>
            <canvas
              id="revenueTrendChart"
              x-show="!state.isLoadingCharts && hasChartData(state.chartData.revenueTrend)"
              class="max-h-72"
            ></canvas>
          </div>

          <div class="border border-stone-200 p-6">
            <p class="mb-4 text-xs font-semibold uppercase tracking-wider text-stone-500">Proporsi Paket Terlaris</p>
            <template x-if="state.isLoadingCharts">
              <div class="flex h-48 items-center justify-center text-sm text-stone-400">Memuat data...</div>
            </template>
            <template x-if="!state.isLoadingCharts && !hasChartData(state.chartData.package)">
              <div class="flex h-48 items-center justify-center text-sm text-stone-400">Belum ada data paket pada tahun ini.</div>
            </template>
            <canvas
              id="packageChart"
              x-show="!state.isLoadingCharts && hasChartData(state.chartData.package)"
              class="max-h-72"
            ></canvas>
          </div>

          <div class="border border-stone-200 p-6">
            <p class="mb-4 text-xs font-semibold uppercase tracking-wider text-stone-500">Proporsi Add-ons Terlaris</p>
            <template x-if="state.isLoadingCharts">
              <div class="flex h-48 items-center justify-center text-sm text-stone-400">Memuat data...</div>
            </template>
            <template x-if="!state.isLoadingCharts && !hasChartData(state.chartData.addon)">
              <div class="flex h-48 items-center justify-center text-sm text-stone-400">Belum ada data add-on pada tahun ini.</div>
            </template>
            <canvas
              id="addonChart"
              x-show="!state.isLoadingCharts && hasChartData(state.chartData.addon)"
              class="max-h-72"
            ></canvas>
          </div>
        </div>
      </section>

      <section>
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

          {{-- Tabel 1: Jadwal Hari Ini --}}
          <div class="border border-stone-200">
            <div class="flex items-center justify-between border-b border-stone-200 px-4 py-3">
              <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-500">Jadwal Pemotretan Hari Ini</h3>
              <a
                href="{{ route('backdoor.session-schedule.list') }}"
                class="text-xs text-stone-500 underline underline-offset-2 hover:text-stone-800"
              >
                Lihat Semua
              </a>
            </div>
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-stone-200 bg-stone-50">
                  <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Waktu
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Klien
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Paket
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Status
                  </th>
                </tr>
              </thead>
              <tbody>
                @forelse ($today as $booking)
                  <tr class="border-b border-stone-100 hover:bg-stone-50">
                    <td class="whitespace-nowrap px-4 py-3 text-xs tabular-nums text-stone-700">
                      {{ $booking->start_time->format('H:i') }} - {{ $booking->end_time->format('H:i') }}
                    </td>
                    <td class="px-4 py-3 font-medium text-stone-800">{{ $booking->user->name }}</td>
                    <td class="px-4 py-3 text-stone-600">{{ $booking->packageVariant->package->name }}</td>
                    <td class="px-4 py-3">
                      <x-shared.badge
                        :value="$booking->status->label()"
                        :variant="$getBadgeVariant($booking->status)"
                      />
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td
                      colspan="4"
                      class="px-4 py-6 text-center text-sm text-stone-400"
                    >Tidak ada jadwal hari ini.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          {{-- Tabel 2: Reservasi Terbaru --}}
          <div class="border border-stone-200">
            <div class="flex items-center justify-between border-b border-stone-200 px-4 py-3">
              <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-500">Reservasi Terbaru</h3>
              <a
                href="{{ route('backdoor.booking-management.index') }}"
                class="text-xs text-stone-500 underline underline-offset-2 hover:text-stone-800"
              >
                Lihat Semua
              </a>
            </div>
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-stone-200 bg-stone-50">
                  <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Kode
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Tanggal
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Total
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Status
                  </th>
                </tr>
              </thead>
              <tbody>
                @forelse ($recent as $booking)
                  <tr class="border-b border-stone-100 hover:bg-stone-50">
                    <td class="px-4 py-3 font-mono text-xs text-stone-600">{{ $booking->booking_code }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-xs text-stone-600">
                      {{ $booking->created_at->translatedFormat('d M Y') }}</td>
                    <td class="whitespace-nowrap px-4 py-3 font-medium text-stone-800">Rp
                      {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                    <td class="px-4 py-3">
                      <x-shared.badge
                        :value="$booking->status->label()"
                        :variant="$getBadgeVariant($booking->status)"
                      />
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td
                      colspan="4"
                      class="px-4 py-6 text-center text-sm text-stone-400"
                    >Belum ada reservasi.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

        </div>
      </section>

    </div>
  </x-slot:content>
</x-layouts.backdoor>
