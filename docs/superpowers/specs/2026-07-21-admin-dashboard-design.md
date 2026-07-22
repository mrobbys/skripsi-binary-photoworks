# Spesifikasi Teknis: Admin Dashboard
**Tanggal:** 2026-07-21
**Branch:** `feat/admin-dashboard`
**Status:** Ready for Implementation

---

## Daftar Isi

1. [Ikhtisar & Tujuan](#1-ikhtisar--tujuan)
2. [Struktur Layout UI (3 Seksi)](#2-struktur-layout-ui-3-seksi)
3. [Seksi 1: Ringkasan Bulan Ini (Stats Cards)](#3-seksi-1-ringkasan-bulan-ini-stats-cards)
4. [Seksi 2: Analitik Tahunan (Charts + Filter Tahun)](#4-seksi-2-analitik-tahunan-charts--filter-tahun)
5. [Seksi 3: Pantauan Real-Time (Tables)](#5-seksi-3-pantauan-real-time-tables)
6. [Arsitektur Backend Laravel](#6-arsitektur-backend-laravel)
7. [Arsitektur Frontend (Alpine.js + Dynamic Loader)](#7-arsitektur-frontend-alpinejs--dynamic-loader)
8. [Struktur File & Direktori](#8-struktur-file--direktori)
9. [Aturan Desain (ThoughtStream Design System)](#9-aturan-desain-thoughtstream-design-system)

---

## 1. Ikhtisar & Tujuan

Halaman Dashboard Admin adalah pusat komando (*command center*) utama aplikasi Binary Photoworks. Halaman ini menyajikan ringkasan performa studio secara sekilas, visualisasi tren analitik tahunan, dan pantauan operasional harian secara *real-time*.

**Fitur utama yang diimplementasikan:**
- 4 Kartu statistik ringkas data bulan berjalan
- Filter tahun menggunakan Flatpickr *Year Select Plugin*
- 4 Grafik Chart.js (Bar, Line, Donut, Pie) yang diambil via **1 endpoint AJAX**
- 2 Tabel pantauan harian (Jadwal & Reservasi Terbaru) dengan limit 5 baris

---

## 2. Struktur Layout UI (3 Seksi)

Halaman dashboard dibagi menjadi 3 seksi visual yang terpisah secara hierarki. Filter tahun TIDAK diletakkan di *header* global halaman, melainkan hanya di *header* Seksi Analitik untuk menghindari ambiguitas UX.

```
+----------------------------------------------------------+
| HEADER HALAMAN                                           |
| "Dashboard"                                              |
+----------------------------------------------------------+
| SEKSI 1: RINGKASAN BULAN INI                             |
| [Card: Total Reservasi] [Card: Pendapatan]               |
| [Card: Total Klien]     [Card: Sesi Menunggu]            |
+----------------------------------------------------------+
| SEKSI 2: ANALITIK PERFORMA          [Filter: 2026 [icon]]|
| [Bar Chart: Tren Volume]  [Line Chart: Tren Pendapatan]  |
| [Donut: Paket Terlaris]   [Pie: Add-ons Terlaris]        |
+----------------------------------------------------------+
| SEKSI 3: PANTAUAN REAL-TIME                              |
| [Tabel: Jadwal Hari Ini]  [Tabel: Reservasi Terbaru]     |
+----------------------------------------------------------+
```

---

## 3. Seksi 1: Ringkasan Bulan Ini (Stats Cards)

### Komponen
Menggunakan komponen `x-backdoor.shared.stats-card` yang sudah ada.

### Data (4 Kartu)

| # | Label | Formula Backend | Keterangan |
|---|-------|-----------------|------------|
| 1 | **Total Reservasi** | `COUNT(bookings.id)` WHERE `MONTH(created_at) = bulan_ini` AND `status != 'Batal'` | Semua booking masuk bulan berjalan |
| 2 | **Total Pendapatan** | `SUM(total_price)` WHERE `MONTH(booking_date) = bulan_ini` AND `status IN (DP_PAID, SUCCESS, DONE)` | Hanya booking yang sudah terbayar |
| 3 | **Total Klien Terdaftar** | `COUNT(users.id)` WHERE `role = 'klien'` | Kumulatif sepanjang masa |
| 4 | **Sesi Menunggu Hari Ini** | `COUNT(bookings.id)` WHERE `booking_date = CURRENT_DATE` AND `status = 'Menunggu'` | Sesi belum konfirmasi hari ini |

### Aturan

- Data stats-card dimuat satu kali saat halaman pertama kali dibuka via variabel Blade dari Controller (`$stats`)
- Data ini TIDAK terpengaruh filter tahun
- Format `totalPendapatan`: `Rp 1.500.000` (gunakan `number_format`)

---

## 4. Seksi 2: Analitik Tahunan (Charts + Filter Tahun)

### 4.1 Filter Tahun (Flatpickr Year Select Plugin)

**Plugin:** `bearholmes/flatpickr-year-select-plugin`
**GitHub:** https://github.com/bearholmes/flatpickr-year-select-plugin

Filter tahun ditempatkan di pojok kanan atas Seksi 2.
Perubahan nilai akan men-trigger *debounced* (400ms) Axios AJAX call.

**Aturan:**
- Diinisialisasi via `x-init="initFlatpickr($refs.yearPickerInput)"` pada elemen `<input>`
- TIDAK ada `onchange` inline di HTML
- Default value: tahun berjalan (`new Date().getFullYear()`)

### 4.2 API Endpoint (1 Request Gabungan)

**Strategi:** Semua data keempat grafik diambil dalam **satu endpoint tunggal** untuk meminimalkan HTTP overhead.

**Route:** `GET /backdoor/dashboard/analytics?year=2026`
**Route Name:** `backdoor.dashboard.analytics`

**Response JSON Structure:**
```json
{
  "year": 2026,
  "booking_trend": {
    "labels": ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"],
    "data": [12, 8, 15, 0, 20, 18, 25, 0, 14, 19, 22, 10]
  },
  "revenue_trend": {
    "labels": ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"],
    "data": [4500000, 3200000, 6000000, 0, 8000000, 7200000, 10000000, 0, 5600000, 7600000, 8800000, 4000000]
  },
  "package_proportion": {
    "labels": ["Wisuda", "Family", "Personal"],
    "data": [65, 20, 15]
  },
  "addon_proportion": {
    "labels": ["Cetak Foto", "Frame Kayu", "Soft File HD", "Extra Waktu"],
    "data": [45, 20, 25, 10]
  }
}
```

> **ATURAN KRITIS (Edge Case):** Untuk `booking_trend` dan `revenue_trend`, bulan yang tidak memiliki transaksi WAJIB menginjeksikan angka `0` secara eksplisit (bukan dihapus dari array). Ini menjaga urutan sumbu X tidak bergeser.

### 4.3 Detail Masing-Masing Grafik

#### Grafik 1: Tren Volume Reservasi (Bar Chart)
- **Tipe:** Chart.js Bar Chart
- **Sumbu X:** Bulan (Jan - Des)
- **Sumbu Y:** Jumlah Booking (integer)
- **Query Backend:**
  ```php
  Booking::whereYear('booking_date', $year)
    ->where('status', '!=', BookingStatus::CANCELLED)
    ->selectRaw('EXTRACT(MONTH FROM booking_date)::int as month, COUNT(id) as total')
    ->groupBy('month')
    ->orderBy('month')
    ->pluck('total', 'month')
    ->toArray();
  // Inject 0 untuk bulan yang tidak ada di result (loop 1-12)
  ```

#### Grafik 2: Tren Pendapatan Per Bulan (Line Chart)
- **Tipe:** Chart.js Line Chart
- **Sumbu X:** Bulan (Jan - Des)
- **Sumbu Y:** Pendapatan (format Y-axis: `15000000` → `15jt`)
- **Query Backend:**
  ```php
  Booking::whereYear('booking_date', $year)
    ->whereIn('status', [BookingStatus::DP_PAID, BookingStatus::SUCCESS, BookingStatus::DONE])
    ->selectRaw('EXTRACT(MONTH FROM booking_date)::int as month, SUM(total_price) as total')
    ->groupBy('month')
    ->orderBy('month')
    ->pluck('total', 'month')
    ->toArray();
  ```

#### Grafik 3: Proporsi Paket Terlaris (Donut Chart)
- **Tipe:** Chart.js Doughnut Chart
- **Urutan:** Clockwise dari persentase terbesar ke terkecil (`orderBy('total', 'desc')`)
- **Query Backend (Multi-level Join):**
  ```php
  Booking::whereYear('bookings.booking_date', $year)
    ->where('bookings.status', '!=', BookingStatus::CANCELLED)
    ->join('package_variants', 'bookings.package_variant_id', '=', 'package_variants.id')
    ->join('packages', 'package_variants.package_id', '=', 'packages.id')
    ->join('categories', 'packages.category_id', '=', 'categories.id')
    ->selectRaw('categories.name as label, COUNT(bookings.id) as total')
    ->groupBy('categories.name')
    ->orderBy('total', 'desc')
    ->get();
  ```

#### Grafik 4: Proporsi Add-ons Terlaris (Pie Chart)
- **Tipe:** Chart.js Pie Chart
- **Query Backend (Join Pivot):**
  ```php
  DB::table('addon_booking')
    ->whereYear('addon_booking.created_at', $year)
    ->join('addons', 'addon_booking.addon_id', '=', 'addons.id')
    ->selectRaw('addons.name as label, COUNT(*) as total')
    ->groupBy('addons.name')
    ->orderBy('total', 'desc')
    ->get();
  ```

---

## 5. Seksi 3: Pantauan Real-Time (Tables)

### Aturan Umum
- Dibuat manual dengan HTML/Tailwind (tanpa komponen `x-table` yang kompleks)
- Limit **5 baris terbaru**
- Tanpa pagination
- Setiap tabel memiliki link "Lihat Semua" menuju halaman index masing-masing
- Tidak ada foto/avatar — data hanya teks
- Data dimuat bersamaan dengan stats card via variabel Blade dari Controller (`$today`, `$recent`)

### Tabel 1: Jadwal Pemotretan Hari Ini

**Query:**
```php
Booking::whereDate('booking_date', Carbon::today())
  ->where('status', '!=', BookingStatus::CANCELLED)
  ->with(['user', 'packageVariant.package'])
  ->orderBy('start_time', 'asc')
  ->limit(5)
  ->get();
```

**Kolom:** Waktu Sesi | Nama Klien | Paket | Status

**Format Waktu:** `$booking->start_time->format('H:i') . ' - ' . $booking->end_time->format('H:i')`

**Link "Lihat Semua":** `route('backdoor.session-schedule.list')`

### Tabel 2: Reservasi Terbaru

**Query:**
```php
Booking::with(['user'])
  ->orderBy('created_at', 'desc')
  ->limit(5)
  ->get();
```

**Kolom:** Kode Booking | Tanggal | Total | Status Bayar

**Format Tanggal:** `$booking->created_at->translatedFormat('d M Y')`
**Format Total:** `'Rp ' . number_format($booking->total_price, 0, ',', '.')`

**Link "Lihat Semua":** `route('backdoor.bookings.index')`

### Badge Status (Partial View)

Dibuat sebagai partial `partials/status-badge.blade.php`:

```blade
@php
  $config = match($status) {
    \App\Domains\Booking\Enums\BookingStatus::PENDING    => ['bg-amber-50 text-amber-700 border border-amber-200',     'Menunggu'],
    \App\Domains\Booking\Enums\BookingStatus::DP_PAID    => ['bg-blue-50 text-blue-700 border border-blue-200',        'DP Terbayar'],
    \App\Domains\Booking\Enums\BookingStatus::SUCCESS    => ['bg-green-50 text-green-700 border border-green-200',     'Lunas'],
    \App\Domains\Booking\Enums\BookingStatus::CANCELLED  => ['bg-red-50 text-red-700 border border-red-200',           'Batal'],
    \App\Domains\Booking\Enums\BookingStatus::DONE       => ['bg-stone-100 text-stone-600 border border-stone-200',    'Selesai'],
    default                                              => ['bg-stone-100 text-stone-500 border border-stone-200',    $status->value],
  };
@endphp
<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold uppercase tracking-wider {{ $config[0] }}">
  {{ $config[1] }}
</span>
```

---

## 6. Arsitektur Backend Laravel

### 6.1 Struktur Direktori Backend

```
app/Domains/AdminDashboard/
├── Http/
│   └── Controllers/
│       └── DashboardController.php   [MODIFY] — Tambah method analytics() & inject Service
└── Services/
    └── DashboardService.php          [NEW] — Semua logika query terpisah di sini
```

### 6.2 DashboardController.php

```php
<?php

namespace App\Domains\AdminDashboard\Http\Controllers;

use App\Domains\AdminDashboard\Services\DashboardService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $service,
    ) {}

    public function index(): View
    {
        $stats  = $this->service->getStats();
        $today  = $this->service->getTodaySchedule();
        $recent = $this->service->getRecentBookings();

        return view('backdoor.dashboard.index', compact('stats', 'today', 'recent'));
    }

    public function analytics(Request $request): JsonResponse
    {
        $year = $request->integer('year', (int) date('Y'));
        $data = $this->service->getAnalytics($year);

        return response()->json($data);
    }
}
```

### 6.3 DashboardService.php

```php
<?php

namespace App\Domains\AdminDashboard\Services;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\User\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getStats(): array
    {
        $now = Carbon::now();

        return [
            'totalReservasi'  => Booking::whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->where('status', '!=', BookingStatus::CANCELLED)
                ->count(),

            'totalPendapatan' => 'Rp ' . number_format(
                Booking::whereMonth('booking_date', $now->month)
                    ->whereYear('booking_date', $now->year)
                    ->whereIn('status', [BookingStatus::DP_PAID, BookingStatus::SUCCESS, BookingStatus::DONE])
                    ->sum('total_price'),
                0, ',', '.'
            ),

            'totalKlien'      => User::role('klien')->count(),

            'sesiMenunggu'    => Booking::whereDate('booking_date', $now->toDateString())
                ->where('status', BookingStatus::PENDING)
                ->count(),
        ];
    }

    public function getAnalytics(int $year): array
    {
        return [
            'year'               => $year,
            'booking_trend'      => $this->getBookingTrend($year),
            'revenue_trend'      => $this->getRevenueTrend($year),
            'package_proportion' => $this->getPackageProportion($year),
            'addon_proportion'   => $this->getAddonProportion($year),
        ];
    }

    public function getTodaySchedule(): Collection
    {
        return Booking::whereDate('booking_date', Carbon::today())
            ->where('status', '!=', BookingStatus::CANCELLED)
            ->with(['user', 'packageVariant.package'])
            ->orderBy('start_time', 'asc')
            ->limit(5)
            ->get();
    }

    public function getRecentBookings(): Collection
    {
        return Booking::with(['user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    // -------------------------------------------------------------------------
    // Private chart query methods
    // -------------------------------------------------------------------------

    private function getBookingTrend(int $year): array
    {
        $raw = Booking::whereYear('booking_date', $year)
            ->where('status', '!=', BookingStatus::CANCELLED)
            ->selectRaw('EXTRACT(MONTH FROM booking_date)::int as month, COUNT(id) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $data   = array_map(fn($m) => (int) ($raw[$m] ?? 0), range(1, 12));

        return ['labels' => $months, 'data' => $data];
    }

    private function getRevenueTrend(int $year): array
    {
        $raw = Booking::whereYear('booking_date', $year)
            ->whereIn('status', [BookingStatus::DP_PAID, BookingStatus::SUCCESS, BookingStatus::DONE])
            ->selectRaw('EXTRACT(MONTH FROM booking_date)::int as month, SUM(total_price) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $data   = array_map(fn($m) => (int) ($raw[$m] ?? 0), range(1, 12));

        return ['labels' => $months, 'data' => $data];
    }

    private function getPackageProportion(int $year): array
    {
        $results = Booking::whereYear('bookings.booking_date', $year)
            ->where('bookings.status', '!=', BookingStatus::CANCELLED)
            ->join('package_variants', 'bookings.package_variant_id', '=', 'package_variants.id')
            ->join('packages', 'package_variants.package_id', '=', 'packages.id')
            ->join('categories', 'packages.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as label, COUNT(bookings.id) as total')
            ->groupBy('categories.name')
            ->orderBy('total', 'desc')
            ->get();

        return [
            'labels' => $results->pluck('label')->toArray(),
            'data'   => $results->pluck('total')->map(fn($v) => (int) $v)->toArray(),
        ];
    }

    private function getAddonProportion(int $year): array
    {
        $results = DB::table('addon_booking')
            ->whereYear('addon_booking.created_at', $year)
            ->join('addons', 'addon_booking.addon_id', '=', 'addons.id')
            ->selectRaw('addons.name as label, COUNT(*) as total')
            ->groupBy('addons.name')
            ->orderBy('total', 'desc')
            ->get();

        return [
            'labels' => $results->pluck('label')->toArray(),
            'data'   => $results->pluck('total')->map(fn($v) => (int) $v)->toArray(),
        ];
    }
}
```

### 6.4 Route (Modifikasi `routes/backdoor/dashboard.php`)

```php
<?php

use App\Domains\AdminDashboard\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('backdoor/dashboard')
    ->name('backdoor.dashboard.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');
    });
```

---

## 7. Arsitektur Frontend (Alpine.js + Dynamic Loader)

### 7.1 Prinsip Wajib

- **Anti-Inline:** Semua logika JS (Chart.js, Flatpickr, Axios) DILARANG ditulis di atribut HTML
- **Co-location:** Script diletakkan di `dashboard-script.blade.php` dan di-include via `@include`
- **React-Style Alpine:** Menggunakan `Alpine.reactive()`, arrow functions, tanpa `this`
- **Dynamic Loader:** File JS menggunakan pola `export default function Dashboard(Alpine)` dan dimuat via `js-module="backdoor/dashboard/Dashboard"`

### 7.2 `Dashboard.js` (Alpine Component — React-Style)

```javascript
/**
 * Dashboard Admin — Alpine Component
 *
 * File: resources/js/features/backdoor/dashboard/Dashboard.js
 * Blade: <div x-data="Dashboard">
 *
 * @param {import('alpinejs').Alpine} Alpine
 */
import route from '../../../lib/route';

export default function Dashboard(Alpine) {
  // ---------------------------------------------------------------------------
  // State
  // ---------------------------------------------------------------------------
  const state = Alpine.reactive({
    selectedYear: new Date().getFullYear(),
    isLoadingCharts: false,
  });

  // Chart instances — tidak perlu reaktif
  let charts = { bookingTrend: null, revenueTrend: null, package: null, addon: null };
  let fetchDebounceTimer = null;

  // ---------------------------------------------------------------------------
  // Methods
  // ---------------------------------------------------------------------------

  /**
   * Inisialisasi Flatpickr Year Select Plugin.
   * Dipanggil via: x-init="initFlatpickr($refs.yearPickerInput)"
   */
  const initFlatpickr = (inputEl) => {
    flatpickr(inputEl, {
      plugins: [new yearSelectPlugin({ date: String(state.selectedYear) })],
      onChange: (selectedDates, dateStr) => {
        const year = parseInt(dateStr, 10);
        if (!isNaN(year) && year !== state.selectedYear) {
          state.selectedYear = year;
          debouncedFetchCharts();
        }
      },
    });
  };

  /**
   * Fetch data 4 chart dari 1 endpoint gabungan.
   */
  const fetchChartData = async () => {
    state.isLoadingCharts = true;

    try {
      const res = await axios.get(route('backdoor.dashboard.analytics'), {
        params: { year: state.selectedYear },
      });

      const data = res.data;
      renderBookingTrendChart(data.booking_trend);
      renderRevenueTrendChart(data.revenue_trend);
      renderPackageChart(data.package_proportion);
      renderAddonChart(data.addon_proportion);
    } catch (err) {
      console.error('Gagal memuat data analitik:', err);
    } finally {
      state.isLoadingCharts = false;
    }
  };

  const debouncedFetchCharts = () => {
    clearTimeout(fetchDebounceTimer);
    fetchDebounceTimer = setTimeout(() => fetchChartData(), 400);
  };

  // ---------------------------------------------------------------------------
  // Chart Renderers (destroy existing instance sebelum render ulang)
  // ---------------------------------------------------------------------------

  const renderBookingTrendChart = ({ labels, data }) => {
    charts.bookingTrend?.destroy();
    const ctx = document.getElementById('bookingTrendChart').getContext('2d');
    charts.bookingTrend = new Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [{ label: 'Jumlah Sesi', data, backgroundColor: '#78716C' }],
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
      },
    });
  };

  const renderRevenueTrendChart = ({ labels, data }) => {
    charts.revenueTrend?.destroy();
    const ctx = document.getElementById('revenueTrendChart').getContext('2d');
    charts.revenueTrend = new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Pendapatan',
          data,
          borderColor: '#78716C',
          backgroundColor: 'rgba(120,113,108,0.1)',
          fill: true,
          tension: 0.4,
        }],
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              // Format Y-axis: 15000000 -> "15jt"
              callback: (value) => {
                if (value >= 1_000_000) return `${value / 1_000_000}jt`;
                if (value >= 1_000) return `${value / 1_000}rb`;
                return value;
              },
            },
          },
        },
      },
    });
  };

  const renderPackageChart = ({ labels, data }) => {
    charts.package?.destroy();
    const ctx = document.getElementById('packageChart').getContext('2d');
    charts.package = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{ data, backgroundColor: ['#78716C', '#A8A29E', '#D6D3D1', '#E7E5E4'] }],
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
      },
    });
  };

  const renderAddonChart = ({ labels, data }) => {
    charts.addon?.destroy();
    const ctx = document.getElementById('addonChart').getContext('2d');
    charts.addon = new Chart(ctx, {
      type: 'pie',
      data: {
        labels,
        datasets: [{ data, backgroundColor: ['#78716C', '#A8A29E', '#D6D3D1', '#E7E5E4', '#57534E'] }],
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
      },
    });
  };

  // ---------------------------------------------------------------------------
  // Return — hanya expose yang dibutuhkan Blade
  // ---------------------------------------------------------------------------
  return {
    state,
    initFlatpickr,
    fetchChartData,
  };
}
```

### 7.3 `index.blade.php` (Struktur Utama)

```blade
<x-layouts.backdoor title="Dashboard" js-module="backdoor/dashboard/Dashboard">
  <x-slot:content>
    <div x-data="Dashboard" x-init="fetchChartData()">

      {{-- ============================================================ --}}
      {{-- SEKSI 1: RINGKASAN BULAN INI                                 --}}
      {{-- ============================================================ --}}
      <section class="mb-8">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-4">
          Ringkasan Bulan Ini
        </h2>
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
          <x-backdoor.shared.stats-card label="Total Reservasi"   value="{{ $stats['totalReservasi'] }}"  suffix="Sesi"   />
          <x-backdoor.shared.stats-card label="Total Pendapatan"  value="{{ $stats['totalPendapatan'] }}"                 />
          <x-backdoor.shared.stats-card label="Total Klien"       value="{{ $stats['totalKlien'] }}"      suffix="Klien"  />
          <x-backdoor.shared.stats-card label="Sesi Menunggu"     value="{{ $stats['sesiMenunggu'] }}"    suffix="Sesi"   />
        </div>
      </section>

      {{-- ============================================================ --}}
      {{-- SEKSI 2: ANALITIK PERFORMA                                   --}}
      {{-- ============================================================ --}}
      <section class="mb-8">
        <div class="flex items-center justify-between border-b border-stone-200 pb-4 mb-6">
          <h2 class="text-xs font-semibold uppercase tracking-wider text-stone-500">Analitik Performa</h2>
          <div class="flex items-center gap-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-stone-500">Tahun</span>
            <input
              type="text"
              id="yearPicker"
              x-ref="yearPickerInput"
              x-init="initFlatpickr($refs.yearPickerInput)"
              class="w-20 border border-stone-300 px-2 py-1 text-sm text-center text-stone-800 cursor-pointer bg-white"
              readonly
            />
          </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
          <div class="border border-stone-200 p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-4">Tren Volume Reservasi</p>
            <template x-if="state.isLoadingCharts">
              <div class="flex items-center justify-center h-48 text-stone-400 text-sm">Memuat data...</div>
            </template>
            <canvas id="bookingTrendChart" x-show="!state.isLoadingCharts" class="max-h-56"></canvas>
          </div>

          <div class="border border-stone-200 p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-4">Tren Pendapatan Per Bulan</p>
            <template x-if="state.isLoadingCharts">
              <div class="flex items-center justify-center h-48 text-stone-400 text-sm">Memuat data...</div>
            </template>
            <canvas id="revenueTrendChart" x-show="!state.isLoadingCharts" class="max-h-56"></canvas>
          </div>

          <div class="border border-stone-200 p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-4">Proporsi Paket Terlaris</p>
            <template x-if="state.isLoadingCharts">
              <div class="flex items-center justify-center h-48 text-stone-400 text-sm">Memuat data...</div>
            </template>
            <canvas id="packageChart" x-show="!state.isLoadingCharts" class="max-h-56"></canvas>
          </div>

          <div class="border border-stone-200 p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-4">Proporsi Add-ons Terlaris</p>
            <template x-if="state.isLoadingCharts">
              <div class="flex items-center justify-center h-48 text-stone-400 text-sm">Memuat data...</div>
            </template>
            <canvas id="addonChart" x-show="!state.isLoadingCharts" class="max-h-56"></canvas>
          </div>
        </div>
      </section>

      {{-- ============================================================ --}}
      {{-- SEKSI 3: PANTAUAN REAL-TIME                                  --}}
      {{-- ============================================================ --}}
      <section>
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

          {{-- Tabel 1: Jadwal Hari Ini --}}
          <div class="border border-stone-200">
            <div class="flex items-center justify-between border-b border-stone-200 px-4 py-3">
              <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-500">Jadwal Pemotretan Hari Ini</h3>
              <a href="{{ route('backdoor.session-schedule.list') }}" class="text-xs text-stone-500 hover:text-stone-800 underline underline-offset-2">
                Lihat Semua
              </a>
            </div>
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-stone-200 bg-stone-50">
                  <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Waktu</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Klien</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Paket</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($today as $booking)
                  <tr class="border-b border-stone-100 hover:bg-stone-50">
                    <td class="px-4 py-3 text-stone-700 text-xs tabular-nums whitespace-nowrap">
                      {{ $booking->start_time->format('H:i') }} - {{ $booking->end_time->format('H:i') }}
                    </td>
                    <td class="px-4 py-3 text-stone-800 font-medium">{{ $booking->user->name }}</td>
                    <td class="px-4 py-3 text-stone-600">{{ $booking->packageVariant->package->name }}</td>
                    <td class="px-4 py-3">
                      @include('backdoor.dashboard.partials.status-badge', ['status' => $booking->status])
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-stone-400 text-sm">Tidak ada jadwal hari ini.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          {{-- Tabel 2: Reservasi Terbaru --}}
          <div class="border border-stone-200">
            <div class="flex items-center justify-between border-b border-stone-200 px-4 py-3">
              <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-500">Reservasi Terbaru</h3>
              <a href="{{ route('backdoor.bookings.index') }}" class="text-xs text-stone-500 hover:text-stone-800 underline underline-offset-2">
                Lihat Semua
              </a>
            </div>
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-stone-200 bg-stone-50">
                  <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Kode</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Tanggal</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Total</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($recent as $booking)
                  <tr class="border-b border-stone-100 hover:bg-stone-50">
                    <td class="px-4 py-3 font-mono text-xs text-stone-600">{{ $booking->booking_code }}</td>
                    <td class="px-4 py-3 text-stone-600 text-xs whitespace-nowrap">{{ $booking->created_at->translatedFormat('d M Y') }}</td>
                    <td class="px-4 py-3 text-stone-800 font-medium whitespace-nowrap">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                    <td class="px-4 py-3">
                      @include('backdoor.dashboard.partials.status-badge', ['status' => $booking->status])
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-stone-400 text-sm">Belum ada reservasi.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

        </div>
      </section>

    </div>
  </x-slot:content>

  @include('backdoor.dashboard.dashboard-script')
</x-layouts.backdoor>
```

### 7.4 `dashboard-script.blade.php`

```blade
{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>

{{-- Flatpickr CSS + JS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

{{-- Flatpickr Year Select Plugin --}}
{{-- Source: https://github.com/bearholmes/flatpickr-year-select-plugin --}}
<script src="https://cdn.jsdelivr.net/gh/bearholmes/flatpickr-year-select-plugin@latest/dist/yearSelectPlugin.js"></script>
```

---

## 8. Struktur File & Direktori (Ringkasan)

```
[NEW]  app/Domains/AdminDashboard/Services/DashboardService.php
[MOD]  app/Domains/AdminDashboard/Http/Controllers/DashboardController.php
[MOD]  routes/backdoor/dashboard.php

[NEW]  resources/views/backdoor/dashboard/index.blade.php
[NEW]  resources/views/backdoor/dashboard/dashboard-script.blade.php
[NEW]  resources/views/backdoor/dashboard/partials/status-badge.blade.php

[NEW]  resources/js/features/backdoor/dashboard/Dashboard.js
```

---

## 9. Aturan Desain (ThoughtStream Design System)

Seluruh implementasi wajib mematuhi aturan ThoughtStream (`docs/thoughtstream-DESIGN.md`):

| Aturan | Implementasi |
|--------|-------------|
| **Tanpa Rounded Corner** | Semua elemen tanpa `rounded-*`, hanya `border` biasa |
| **Tanpa Shadow** | Pemisah menggunakan `border-stone-200`, bukan shadow |
| **Flat Background** | Stats card: `bg-stone-100`, chart container: `bg-white` |
| **Palette Stone** | Semua warna anchor: `stone-*` Tailwind |
| **Tanpa Gradient** | Tidak ada `bg-gradient-*` |
| **Badge Status** | Menggunakan Status Chip: Warning (amber), Success (green), Info (blue), dll |
| **Tipografi Label** | `text-xs font-semibold uppercase tracking-wider text-stone-500` |
| **Warna Chart.js** | Menggunakan palette stone dari ThoughtStream: `#78716C`, `#A8A29E`, `#D6D3D1`, `#E7E5E4` |
