# Spec: Frontdoor — Booking History (Dasbor Jadwal Klien)

**Tanggal:** 2026-07-09
**Branch:** `feat/frontdoor-booking-history`
**Scope:** Backend (Migration, Controller, Service, Repository, DTO) + Frontend (Blade refactor, Alpine.js JS Modules) + Repay via Midtrans Snap
**Stack:** Laravel 13 · PHP 8.3 · Alpine.js · Tailwind CSS v4 · Axios · Midtrans Snap · `spatie/laravel-data`
**Design System:** ThoughtStream (flat, no rounded corners, stone palette, border separators)

---

## Daftar Isi

1. [Struktur Direktori File Baru & Modifikasi](#1-struktur-direktori-file-baru--modifikasi)
2. [Migration — Tambah Field ke Tabel `payments`](#2-migration--tambah-field-ke-tabel-payments)
3. [Model Payment — Update Casts](#3-model-payment--update-casts)
4. [DTOs](#4-dtos)
5. [Repository — BookingRepository (Modifikasi)](#5-repository--bookingrepositoryphp-modifikasi)
6. [Service — DashboardService (Baru)](#6-service--dashboardservice-baru)
7. [WebhookController — Update Simpan `snap_token_expiry`](#7-webhookcontroller--update-simpan-snap_token_expiry)
8. [Controller — DashboardController (Modifikasi)](#8-controller--dashboardcontroller-modifikasi)
9. [Route — Frontdoor](#9-route--frontdoor)
10. [Frontend — JS Modules](#10-frontend--js-modules)
11. [Frontend — Blade Views (Modifikasi)](#11-frontend--blade-views-modifikasi)
12. [Catatan Implementasi](#12-catatan-implementasi)

---

## 1. Struktur Direktori File Baru & Modifikasi

```
app/Domains/Payment/
└── Models/
    └── Payment.php                             ← MODIFIKASI (tambah cast snap_token_expiry)

app/Domains/Booking/
├── DTOs/
│   └── BookingHistoryData.php                  ← BARU (DTO untuk kartu list)
├── Repositories/
│   └── BookingRepository.php                  ← MODIFIKASI (tambah method getByUser*)
├── Services/
│   └── DashboardService.php                   ← BARU (logika repay / reuse token)
└── Http/Controllers/Frontdoor/
    └── WebhookController.php                  ← MODIFIKASI (simpan snap_token_expiry)

app/Domains/User/Http/Controllers/Frontdoor/
└── DashboardController.php                    ← MODIFIKASI (inject DashboardService, tambah repay())

database/migrations/
└── xxxx_add_snap_token_expiry_to_payments.php ← BARU

routes/frontdoor/
└── frontdoor.php                              ← MODIFIKASI (tambah route repay)

resources/js/features/
└── dashboard/                                 ← BARU (folder khusus dashboard)
    ├── Dashboard.js                           ← BARU (Alpine.data entry point)
    ├── useAppointments.js                     ← BARU (fetch list + tab filter)
    ├── useDetail.js                           ← BARU (tampil detail di panel kanan)
    └── usePayment.js                          ← BARU (repay via Midtrans Snap)

resources/views/frontdoor/dashboard/
├── jadwal.blade.php                           ← MODIFIKASI PENUH (master-detail layout)
└── script.blade.php                           ← BARU (co-located Alpine.js)
```

---

## 2. Migration — Tambah Field ke Tabel `payments`

**File:** `database/migrations/xxxx_add_snap_token_expiry_to_payments.php` ← BARU

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Simpan waktu expired Snap Token dari Midtrans (biasanya 1 jam setelah dibuat)
            // Digunakan untuk menentukan apakah snap_token masih valid atau perlu dibuat ulang
            $table->timestamp('snap_token_expiry')->nullable()->after('snap_token');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('snap_token_expiry');
        });
    }
};
```

---

## 3. Model Payment — Update Casts

**File:** `app/Domains/Payment/Models/Payment.php` ← MODIFIKASI

Tambahkan cast untuk field `snap_token_expiry` agar otomatis menjadi objek Carbon.

```php
<?php

namespace App\Domains\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Booking\Models\Booking;
use App\Domains\Payment\Enums\PaymentPurpose;
use App\Domains\Payment\Enums\PaymentStatus;

#[Guarded(['id'])]
class Payment extends Model
{
    protected function casts(): array
    {
        return [
            'payment_purpose'   => PaymentPurpose::class,
            'pay_date'          => 'datetime',
            'status'            => PaymentStatus::class,
            'snap_token_expiry' => 'datetime',  // ← TAMBAH INI
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
```

---

## 4. DTOs

### `BookingHistoryData.php` ← BARU

DTO khusus untuk menampilkan data kartu di halaman Dasbor (list view).
Lebih ringan dari `BookingViewData` yang dipakai untuk invoice PDF.

```php
<?php

namespace App\Domains\Booking\DTOs;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Support\Formatter;
use Spatie\LaravelData\Data;

class BookingHistoryData extends Data
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $booking_code,
        public readonly string  $package_name,
        public readonly string  $variant_name,
        public readonly string  $background_name,
        public readonly string  $formatted_date,
        public readonly string  $formatted_time,
        public readonly string  $status,
        public readonly string  $status_label,
        public readonly bool    $is_upcoming,           // true = tab Akan Datang, false = tab Selesai
        public readonly bool    $can_pay,               // true = tampilkan tombol "Bayar Sekarang"
        public readonly string  $formatted_total_price,
        public readonly string  $payment_scheme,
        public readonly ?string $gdrive_link,
    ) {}

    public static function fromModel(Booking $booking): self
    {
        $upcomingStatuses = [
            BookingStatus::PENDING->value,
            BookingStatus::DP_PAID->value,
            BookingStatus::SUCCESS->value,
        ];

        $isUpcoming = in_array($booking->status->value, $upcomingStatuses);

        // Tombol "Bayar Sekarang" hanya muncul jika status masih "Menunggu"
        $canPay = $booking->status === BookingStatus::PENDING;

        return new self(
            id:                    $booking->id,
            booking_code:          $booking->booking_code,
            package_name:          $booking->packageVariant?->package?->name ?? '-',
            variant_name:          $booking->packageVariant?->name ?? '-',
            background_name:       $booking->background?->name ?? 'Tanpa Background',
            formatted_date:        Formatter::dateId($booking->booking_date, 'l, d F Y'),
            formatted_time:        Formatter::timeRange($booking->start_time, $booking->end_time),
            status:                $booking->status->value,
            status_label:          $booking->status->label(),
            is_upcoming:           $isUpcoming,
            can_pay:               $canPay,
            formatted_total_price: Formatter::rupiah($booking->total_price),
            payment_scheme:        $booking->payment_scheme->value,
            gdrive_link:           $booking->gdrive_link,
        );
    }
}
```

---

## 5. Repository — `BookingRepository.php` (Modifikasi)

**File:** `app/Domains/Booking/Repositories/BookingRepository.php` ← MODIFIKASI

Tambahkan 2 method baru di bawah method-method yang sudah ada.

```php
/**
 * Ambil semua booking milik user tertentu (untuk Dasbor)
 * Eager load yang dibutuhkan untuk BookingHistoryData::fromModel()
 *
 * @param int $userId
 */
public function getByUser(int $userId): Collection
{
    return Booking::with(['packageVariant.package', 'background'])
        ->where('user_id', $userId)
        ->orderBy('booking_date', 'desc')
        ->orderBy('start_time', 'desc')
        ->get();
}

/**
 * Cari booking berdasarkan booking_code milik user tertentu
 * Eager load lengkap untuk keperluan repay (butuh data payments)
 *
 * @param string $bookingCode
 * @param int    $userId
 */
public function findByCodeAndUser(string $bookingCode, int $userId): ?Booking
{
    return Booking::with(['packageVariant.package', 'background', 'payments'])
        ->where('booking_code', $bookingCode)
        ->where('user_id', $userId)
        ->first();
}
```

---

## 6. Service — `DashboardService.php` (Baru)

**File:** `app/Domains/Booking/Services/DashboardService.php` ← BARU

Service ini bertanggung jawab atas logika **Reuse atau Buat Ulang Snap Token** untuk fitur "Bayar Sekarang" di Dasbor.

```php
<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\DTOs\BookingHistoryData;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Repositories\BookingRepository;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\User\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;

class DashboardService
{
    public function __construct(
        private readonly BookingRepository $repository,
        private readonly MidtransService   $midtrans,
    ) {}

    /**
     * Ambil semua booking milik user dan transform ke DTO
     *
     * @param int $userId
     */
    public function getBookingHistory(int $userId): Collection
    {
        return $this->repository->getByUser($userId)
            ->map(fn(Booking $b) => BookingHistoryData::fromModel($b));
    }

    /**
     * Logika utama: Reuse atau buat ulang Snap Token untuk booking yang sudah ada.
     * Hanya bisa dilakukan jika status booking masih PENDING.
     *
     * Algoritma:
     * 1. Cek apakah booking_code valid dan milik user yang login
     * 2. Pastikan status booking masih PENDING (boleh bayar)
     * 3. Cek payment record dengan status pending
     * 4. Jika snap_token ada dan belum expired → kembalikan token lama (hemat API call)
     * 5. Jika token expired atau tidak ada → request token baru ke Midtrans, update record
     *
     * @param string $bookingCode
     * @param User   $user
     * @return string snap_token
     */
    public function getOrCreateSnapToken(string $bookingCode, User $user): string
    {
        $booking = $this->repository->findByCodeAndUser($bookingCode, $user->id);

        if (! $booking) {
            throw new RuntimeException('Booking tidak ditemukan.');
        }

        if ($booking->status !== BookingStatus::PENDING) {
            throw new RuntimeException('Booking ini tidak dapat dibayar (status bukan Menunggu).');
        }

        // Ambil payment record dengan status pending yang belum terbayar
        $payment = $booking->payments
            ->where('status', PaymentStatus::PENDING)
            ->first();

        if (! $payment) {
            throw new RuntimeException('Tidak ada tagihan yang perlu dibayar.');
        }

        // Jika snap_token masih ada dan BELUM expired → reuse token (hemat API call)
        if ($payment->snap_token && $payment->snap_token_expiry?->isFuture()) {
            return $payment->snap_token;
        }

        // Token expired atau belum ada → buat token baru ke Midtrans
        $snapToken = $this->midtrans->getSnapToken(
            orderId:     $payment->order_id,
            grossAmount: $payment->amount,
            user:        $user,
            booking:     $booking,
        );

        // Update snap_token dan expiry di database
        // Midtrans set expiry 1 jam dari sekarang (sesuai konfigurasi di Midtrans Dashboard)
        $payment->update([
            'snap_token'        => $snapToken,
            'snap_token_expiry' => Carbon::now()->addHour(),
        ]);

        return $snapToken;
    }
}
```

---

## 7. WebhookController — Update Simpan `snap_token_expiry`

**File:** `app/Domains/Booking/Http/Controllers/Frontdoor/WebhookController.php` ← MODIFIKASI

Saat Midtrans mengirim webhook, field `expiry_time` dari payload perlu disimpan ke kolom `snap_token_expiry` agar data expiry selalu sinkron dengan data dari Midtrans.

Cari bagian di mana `Payment` di-update setelah webhook diterima, lalu tambahkan:

```php
// Di dalam handler webhook, saat update Payment record:
$payment->update([
    'status'            => $newStatus,
    'payment_type'      => $notification['payment_type'] ?? null,
    'pay_date'          => $settlementTime,
    'snap_token_expiry' => isset($notification['expiry_time'])   // ← TAMBAH INI
                           ? Carbon::parse($notification['expiry_time'])
                           : null,
]);
```

> **Catatan:** Jika `expiry_time` tidak ada di payload (misal saat status `settlement`), nilainya `null` — yang artinya token dianggap tidak berlaku lagi dan tidak perlu di-reuse karena booking sudah terbayar.

---

## 8. Controller — `DashboardController.php` (Modifikasi)

**File:** `app/Domains/User/Http/Controllers/Frontdoor/DashboardController.php` ← MODIFIKASI

```php
<?php

namespace App\Domains\User\Http\Controllers\Frontdoor;

use App\Domains\Booking\Services\DashboardService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

#[Middleware('auth')]
class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    /**
     * Tampilkan halaman Dasbor Jadwal (Booking History)
     */
    public function index(): View
    {
        return view('frontdoor.dashboard.jadwal');
    }

    /**
     * Ambil semua data booking history milik user yang sedang login (API endpoint)
     * Dipanggil oleh Alpine.js via Axios saat halaman dimuat
     */
    public function appointments(): JsonResponse
    {
        $history = $this->dashboardService->getBookingHistory(Auth::id());

        return response()->json([
            'data' => $history,
        ]);
    }

    /**
     * Endpoint "Bayar Sekarang" — Reuse atau buat Snap Token baru
     * Dipanggil oleh usePayment.js saat user klik tombol "Bayar Sekarang"
     */
    public function repay(Request $request): JsonResponse
    {
        $request->validate([
            'booking_code' => ['required', 'string'],
        ]);

        $snapToken = $this->dashboardService->getOrCreateSnapToken(
            bookingCode: $request->booking_code,
            user:        Auth::user(),
        );

        return response()->json([
            'success'    => true,
            'snap_token' => $snapToken,
        ]);
    }

    /**
     * Tampilkan halaman Profil
     */
    public function profil(): View
    {
        return view('frontdoor.dashboard.profil');
    }
}
```

---

## 9. Route — Frontdoor

**File:** `routes/frontdoor/frontdoor.php` ← MODIFIKASI

Tambahkan 2 route baru di dalam group `middleware('auth')`:

```php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('frontdoor.dashboard.index');

    // API: Ambil daftar booking history (dipanggil Alpine.js)
    Route::get('/dashboard/appointments', [DashboardController::class, 'appointments'])
        ->name('frontdoor.dashboard.appointments');

    // API: Repay — reuse atau buat snap token baru
    Route::post('/dashboard/repay', [DashboardController::class, 'repay'])
        ->name('frontdoor.dashboard.repay');

    Route::get('/dashboard/profil', [DashboardController::class, 'profil'])
        ->name('frontdoor.dashboard.profil');
});
```

---

## 10. Frontend — JS Modules

### Struktur folder baru

```
resources/js/features/frontdoor/dashboard/
├── Dashboard.js          ← Entry point, Alpine.data registration
├── useState.js           ← Centralized Alpine.reactive() state
├── useAppointments.js    ← Fetch + filter data tab
├── useDetail.js          ← Kontrol tampilan panel detail (master-detail)
└── usePayment.js         ← Logika repay via Midtrans Snap
```

---

### `useState.js` — Centralized State

**File:** `resources/js/features/frontdoor/dashboard/useState.js` ← BARU

```js
export default function useState(Alpine) {
  return Alpine.reactive({
    appointments: [],
    activeTab: 'upcoming',
    isLoading: false,
    selectedAppointment: null,
    isProcessingPayment: false,
  });
}
```

---

### `Dashboard.js` — Entry Point Alpine.data

**File:** `resources/js/features/frontdoor/dashboard/Dashboard.js` ← BARU

> **Konvensi:** Nama function = nama file = nama komponen Alpine (`x-data="Dashboard"`).
> Dynamic loader di `app.js` akan otomatis mendaftarkannya — tidak perlu registrasi manual.

```js
import useState from './useState.js';
import useAppointments from './useAppointments.js';
import useDetail from './useDetail.js';
import usePayment from './usePayment.js';

export default function Dashboard(Alpine) {
  const state = useState(Alpine);

  const { fetchAppointments, filteredAppointments, switchTab } = useAppointments({ state });
  const { showDetail, clearDetail, hasDetail } = useDetail({ state });
  const { triggerRepay } = usePayment({ state, fetchAppointments });

  // Lifecycle: dipanggil otomatis Alpine saat komponen dimuat
  const init = () => {
    fetchAppointments();
  };

  return {
    state,
    init,
    // Appointments
    fetchAppointments,
    filteredAppointments,
    switchTab,
    // Detail
    showDetail,
    clearDetail,
    hasDetail,
    // Payment
    triggerRepay,
  };
}
```

---

### `useAppointments.js` — Fetch & Filter Data Tab

**File:** `resources/js/features/frontdoor/dashboard/useAppointments.js` ← BARU

```js
import route from '@/lib/route';

export default function useAppointments({ state }) {
  // Method: ambil data booking dari backend
  const fetchAppointments = async () => {
    state.isLoading = true;
    try {
      const res = await axios.get(route('frontdoor.dashboard.appointments'));
      state.appointments = res.data.data;
    } catch (err) {
      console.error('Gagal memuat riwayat booking:', err);
    } finally {
      state.isLoading = false;
    }
  };

  // Computed: filter list berdasarkan tab aktif
  const filteredAppointments = () => {
    return state.appointments.filter((a) =>
      state.activeTab === 'upcoming' ? a.is_upcoming : !a.is_upcoming
    );
  };

  // Method: ganti tab aktif & reset panel detail
  const switchTab = (tab) => {
    state.activeTab = tab;
    state.selectedAppointment = null;
  };

  return {
    fetchAppointments,
    filteredAppointments,
    switchTab,
  };
}
```

---

### `useDetail.js` — Kontrol Panel Detail (Master-Detail Pattern)

**File:** `resources/js/features/frontdoor/dashboard/useDetail.js` ← BARU

```js
export default function useDetail({ state }) {
  // Method: tampilkan detail booking yang diklik
  // URL diperbarui diam-diam tanpa refresh halaman (HTML5 History API)
  const showDetail = (appointment) => {
    state.selectedAppointment = appointment;

    const newUrl = window.location.pathname + '?booking=' + appointment.booking_code;
    window.history.replaceState({}, '', newUrl);
  };

  // Method: tutup / reset panel detail & kembalikan URL ke kondisi bersih
  const clearDetail = () => {
    state.selectedAppointment = null;
    window.history.replaceState({}, '', window.location.pathname);
  };

  // Computed: apakah ada detail yang sedang ditampilkan?
  const hasDetail = () => state.selectedAppointment !== null;

  return {
    showDetail,
    clearDetail,
    hasDetail,
  };
}
```

---

### `usePayment.js` — Repay via Midtrans Snap

**File:** `resources/js/features/frontdoor/dashboard/usePayment.js` ← BARU

```js
import route from '@/lib/route';
import { Toast } from '@/lib/sweetalert';

export default function usePayment({ state, fetchAppointments }) {
  // Method: Trigger "Bayar Sekarang" dari dashboard
  const triggerRepay = async (bookingCode) => {
    state.isProcessingPayment = true;

    try {
      const res = await axios.post(route('frontdoor.dashboard.repay'), {
        booking_code: bookingCode,
      });

      if (!res.data.success) {
        throw new Error(res.data.message || 'Gagal mendapatkan token pembayaran.');
      }

      const snapToken = res.data.snap_token;

      window.snap.pay(snapToken, {
        onSuccess: () => {
          Toast.fire({ icon: 'success', title: 'Pembayaran berhasil!' });
          state.selectedAppointment = null;
          fetchAppointments();
        },
        onPending: () => {
          Toast.fire({ icon: 'info', title: 'Menunggu pembayaran diselesaikan.' });
          state.isProcessingPayment = false;
          state.selectedAppointment = null;
          fetchAppointments();
        },
        onError: () => {
          Toast.fire({ icon: 'error', title: 'Pembayaran gagal. Silakan coba lagi.' });
          state.isProcessingPayment = false;
        },
        onClose: () => {
          Toast.fire({ icon: 'warning', title: 'Pembayaran dibatalkan. Tagihan masih tersimpan.' });
          state.isProcessingPayment = false;
        },
      });
    } catch (err) {
      const msg = err?.response?.data?.message || err.message || 'Terjadi kesalahan.';
      Toast.fire({ icon: 'error', title: msg });
      state.isProcessingPayment = false;
    }
  };

  return {
    triggerRepay,
  };
}
```

---

## 11. Frontend — Blade Views (Modifikasi)

### `jadwal.blade.php` — Master-Detail Layout

**File:** `resources/views/frontdoor/dashboard/jadwal.blade.php` ← MODIFIKASI PENUH

Pola: `jsModule="frontdoor/dashboard/Dashboard"` di layout, `x-data="Dashboard"` di wrapper utama.
Panel kiri = list kartu. Panel kanan = detail booking yang diklik.
Jika tidak ada yang diklik, panel kanan tidak ditampilkan.

```blade
<x-layouts.frontdoor
  title="Jadwal Saya - Dashboard"
  jsModule="frontdoor/dashboard/Dashboard">
  <x-slot:content>
    <div class="w-full min-h-[calc(100vh-80px)] bg-stone-50 py-12 px-4 sm:px-6"
         x-data="Dashboard">

      <div class="max-w-6xl mx-auto flex flex-col md:flex-row gap-8">

        {{-- Sidebar Navigasi --}}
        <x-frontdoor.dashboard.sidebar />

        {{-- Panel Utama --}}
        <main class="flex-1 flex gap-6">

          {{-- KIRI: Daftar Kartu Booking --}}
          <section class="flex-1 bg-white border border-stone-200 p-6 sm:p-8">
            <h1 class="text-xl font-bold text-stone-900 mb-6">Jadwal Sesi Foto Anda</h1>

            {{-- Tab Filter --}}
            <div class="flex gap-3 mb-6">
              <button
                x-on:click="switchTab('upcoming')"
                x-bind:class="activeTab === 'upcoming'
                  ? 'bg-stone-900 text-white'
                  : 'border border-stone-300 text-stone-600 hover:bg-stone-50'"
                class="px-5 py-2 text-sm font-medium transition-colors">
                Akan Datang
              </button>
              <button
                x-on:click="switchTab('past')"
                x-bind:class="activeTab === 'past'
                  ? 'bg-stone-900 text-white'
                  : 'border border-stone-300 text-stone-600 hover:bg-stone-50'"
                class="px-5 py-2 text-sm font-medium transition-colors">
                Selesai
              </button>
            </div>

            {{-- Loading State --}}
            <div x-show="isLoading" class="space-y-4">
              <template x-for="i in 3">
                <div class="border border-stone-200 p-6 animate-pulse">
                  <div class="h-4 bg-stone-200 w-1/3 mb-3"></div>
                  <div class="h-3 bg-stone-100 w-1/2"></div>
                </div>
              </template>
            </div>

            {{-- Empty State --}}
            <div x-show="!isLoading && filteredAppointments.length === 0"
                 class="py-16 text-center text-stone-400">
              <p class="text-sm">Belum ada jadwal di kategori ini.</p>
            </div>

            {{-- Daftar Kartu --}}
            <div x-show="!isLoading" class="space-y-3">
              <template x-for="appointment in filteredAppointments" :key="appointment.booking_code">
                <div
                  class="border border-stone-200 p-5 flex flex-col sm:flex-row gap-4 hover:border-stone-400 transition-colors cursor-pointer"
                  x-bind:class="selectedAppointment?.booking_code === appointment.booking_code
                    ? 'border-stone-900 bg-stone-50'
                    : 'bg-white'"
                  x-on:click="showDetail(appointment)">

                  {{-- Kolom Waktu --}}
                  <div class="sm:w-2/5">
                    <p class="font-semibold text-stone-900 text-sm" x-text="appointment.formatted_date"></p>
                    <p class="text-stone-500 text-sm mt-1" x-text="appointment.formatted_time + ' WITA'"></p>
                  </div>

                  {{-- Kolom Rincian --}}
                  <div class="flex-1 sm:border-l sm:border-stone-200 sm:pl-5">
                    <p class="font-medium text-stone-900 text-sm" x-text="appointment.variant_name"></p>
                    <p class="text-stone-500 text-xs mt-1 flex flex-wrap gap-x-3 gap-y-1 items-center">
                      <span x-text="appointment.status_label"></span>
                      <span class="w-1 h-1 bg-stone-300 inline-block"></span>
                      <span x-text="appointment.background_name"></span>
                    </p>
                  </div>

                  {{-- Tombol Bayar Sekarang (hanya jika can_pay = true) --}}
                  <div class="flex items-center" x-show="appointment.can_pay" x-on:click.stop>
                    <button
                      x-on:click="triggerRepay(appointment.booking_code)"
                      x-bind:disabled="isProcessingPayment"
                      class="px-4 py-2 text-xs font-semibold bg-stone-900 text-white hover:bg-stone-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                      <span x-show="!isProcessingPayment">Bayar Sekarang</span>
                      <span x-show="isProcessingPayment">Memproses...</span>
                    </button>
                  </div>
                </div>
              </template>
            </div>

            {{-- Footer Policy --}}
            <div class="mt-8 pt-6 border-t border-stone-100 text-sm text-stone-400">
              Ingin mengubah pesanan, batal, atau ada kendala teknis?
              <a href="https://wa.me/{{ config('studio.whatsapp_number') }}"
                 target="_blank" rel="noopener noreferrer"
                 class="text-stone-600 underline hover:text-stone-900 ml-1">
                Hubungi Kami
              </a>
            </div>
          </section>

          {{-- KANAN: Panel Detail (Master-Detail) --}}
          <aside
            class="w-80 flex-shrink-0 bg-white border border-stone-200 p-6 self-start sticky top-6"
            x-show="hasDetail"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-x-2"
            x-transition:enter-end="opacity-100 translate-x-0">

            {{-- Header Detail --}}
            <div class="flex items-center justify-between mb-6">
              <h2 class="font-bold text-stone-900">Detail Pesanan</h2>
              <button x-on:click="clearDetail()"
                      class="text-stone-400 hover:text-stone-700 text-sm">
                ✕ Tutup
              </button>
            </div>

            <template x-if="selectedAppointment">
              <div class="space-y-4 text-sm">
                {{-- Kode Booking --}}
                <div>
                  <p class="text-stone-400 text-xs uppercase tracking-wide mb-1">Kode Booking</p>
                  <p class="font-mono font-semibold text-stone-900"
                     x-text="selectedAppointment.booking_code"></p>
                </div>

                {{-- Paket --}}
                <div>
                  <p class="text-stone-400 text-xs uppercase tracking-wide mb-1">Paket</p>
                  <p class="font-medium text-stone-900" x-text="selectedAppointment.package_name"></p>
                  <p class="text-stone-500" x-text="selectedAppointment.variant_name"></p>
                </div>

                {{-- Jadwal --}}
                <div>
                  <p class="text-stone-400 text-xs uppercase tracking-wide mb-1">Jadwal</p>
                  <p class="text-stone-700" x-text="selectedAppointment.formatted_date"></p>
                  <p class="text-stone-500" x-text="selectedAppointment.formatted_time + ' WITA'"></p>
                </div>

                {{-- Background --}}
                <div>
                  <p class="text-stone-400 text-xs uppercase tracking-wide mb-1">Latar Studio</p>
                  <p class="text-stone-700" x-text="selectedAppointment.background_name"></p>
                </div>

                {{-- Status & Harga --}}
                <div class="border-t border-stone-100 pt-4">
                  <div class="flex justify-between items-center mb-2">
                    <span class="text-stone-400">Status</span>
                    <span class="font-semibold text-stone-900"
                          x-text="selectedAppointment.status_label"></span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-stone-400">Total</span>
                    <span class="font-bold text-stone-900"
                          x-text="selectedAppointment.formatted_total_price"></span>
                  </div>
                </div>

                {{-- Tombol Aksi di Panel Detail --}}
                <div class="border-t border-stone-100 pt-4 space-y-2">

                  {{-- Bayar Sekarang (hanya untuk status Menunggu) --}}
                  <button
                    x-show="selectedAppointment.can_pay"
                    x-on:click="triggerRepay(selectedAppointment.booking_code)"
                    x-bind:disabled="isProcessingPayment"
                    class="w-full py-2.5 font-semibold text-sm bg-stone-900 text-white hover:bg-stone-700 transition-colors disabled:opacity-50">
                    <span x-show="!isProcessingPayment">Bayar Sekarang</span>
                    <span x-show="isProcessingPayment">Memproses...</span>
                  </button>

                  {{-- Unduh Hasil Foto (hanya jika ada gdrive_link dan status Selesai) --}}
                  <a
                    x-show="selectedAppointment.gdrive_link && selectedAppointment.status === 'Selesai'"
                    x-bind:href="selectedAppointment.gdrive_link"
                    target="_blank" rel="noopener noreferrer"
                    class="block w-full py-2.5 font-semibold text-sm border border-stone-900 text-stone-900 hover:bg-stone-50 text-center transition-colors">
                    Unduh Hasil Foto
                  </a>

                  {{-- Hubungi Admin --}}
                  <a href="https://wa.me/{{ config('studio.whatsapp_number') }}"
                     target="_blank" rel="noopener noreferrer"
                     class="block w-full py-2.5 text-sm text-center text-stone-500 hover:text-stone-900 transition-colors">
                    Hubungi Admin
                  </a>
                </div>
              </div>
            </template>
          </aside>

        </main>
      </div>
    </div>
  </x-slot:content>

</x-layouts.frontdoor>
```

> **Catatan:** File `script.blade.php` dan pendaftaran manual di `app.js` **tidak diperlukan**.
> Dynamic loader di `app.js` secara otomatis mendaftarkan `Dashboard` ke `Alpine.data()` berdasarkan `jsModule="frontdoor/dashboard/Dashboard"` yang ada di layout. Cukup buat file JS-nya dan pastikan nama file PascalCase sesuai yang ingin Anda gunakan di `x-data`.

---

> **Tidak ada file `script.blade.php` atau modifikasi `app.js` yang diperlukan.**
> Pendaftaran komponen Alpine dilakukan secara otomatis oleh dynamic loader bawaan proyek ini.

---

## 12. Catatan Implementasi

### A. Update TODO di `useCheckout.js` (Booking Flow Lama)

Setelah halaman Dasbor selesai dibuat, selesaikan semua `TODO` di `useCheckout.js`:

```js
// onPending, onError, onClose → arahkan ke halaman dasbor
window.location.href = route('frontdoor.dashboard.index');
```

### B. Tambahkan `BookingStatus::DONE` di Enum

Di `BookingStatus.php` saat ini tidak ada case `Selesai`. Perlu ditambahkan:

```php
case DONE = 'Selesai';
```

Dan di `label()`:
```php
self::DONE => 'Selesai',
```

### C. Nomor WhatsApp Admin

Pastikan ada config `studio.whatsapp_number` di `config/studio.php` (atau sesuaikan dengan config yang sudah ada di proyek).

### D. Snap SDK Midtrans di Layout

Pastikan script Midtrans Snap di-load di layout `frontdoor.blade.php`:

```blade
<script src="https://app.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>
```

### E. Order Implementasi yang Disarankan

1. Jalankan migration baru (`snap_token_expiry`)
2. Update `Payment` model (tambah cast `snap_token_expiry`)
3. Tambahkan `BookingStatus::DONE` di Enum
4. Buat `BookingHistoryData` DTO
5. Update `BookingRepository` (tambah 2 method baru)
6. Buat `DashboardService`
7. Update `WebhookController` (simpan `snap_token_expiry` dari webhook payload)
8. Update `DashboardController` (inject `DashboardService`, tambah `appointments()` dan `repay()`)
9. Update `routes/frontdoor/frontdoor.php`
10. Buat JS modules (`useState.js`, `Dashboard.js`, `useAppointments.js`, `useDetail.js`, `usePayment.js`) di `resources/js/features/frontdoor/dashboard/`
11. Update `jadwal.blade.php` (tambah `jsModule="frontdoor/dashboard/Dashboard"` dan `x-data="Dashboard"`)
12. Selesaikan TODO di `useCheckout.js` (redirect ke dasbor)
