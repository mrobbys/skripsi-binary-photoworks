# Spec: Frontdoor — Batal & Reschedule Booking (Dasbor Klien)

**Tanggal:** 2026-07-10
**Branch:** `feat/frontdoor-booking-history`
**Scope:** Backend (Controller, Service, Repository, DTO) + Frontend (Shared Drawer Component, Alpine.js Modules, Blade)
**Stack:** Laravel 13 · PHP 8.3 · Alpine.js · Tailwind CSS v4 · Axios · Flatpickr · `spatie/laravel-data`
**Design System:** ThoughtStream (flat, no rounded corners, stone palette, border separators)

---

## Daftar Isi

1. [Aturan Bisnis & Logika Pembatasan](#1-aturan-bisnis--logika-pembatasan)
2. [Struktur Direktori File Baru & Modifikasi](#2-struktur-direktori-file-baru--modifikasi)
3. [DTO — BookingHistoryData (Modifikasi)](#3-dto--bookinghistorydata-modifikasi)
4. [Repository — BookingRepository (Modifikasi)](#4-repository--bookingrepositoryphp-modifikasi)
5. [Service — DashboardService (Modifikasi)](#5-service--dashboardservice-modifikasi)
6. [Controller — DashboardController (Modifikasi)](#6-controller--dashboardcontroller-modifikasi)
7. [Route — dashboard.php (Modifikasi)](#7-route--dashboardphp-modifikasi)
8. [Frontend — Shared Drawer Component (Baru)](#8-frontend--shared-drawer-component-baru)
9. [Frontend — JS Modules](#9-frontend--js-modules)
10. [Frontend — Blade Views](#10-frontend--blade-views)
11. [Catatan Implementasi](#11-catatan-implementasi)

---

## 1. Aturan Bisnis & Logika Pembatasan

### A. Fitur Batal Booking

| Kondisi | Nilai |
|---|---|
| Status yang bisa membatalkan | `PENDING` saja |
| Konfirmasi sebelum aksi | Ya — via `confirmModal(...)` SweetAlert2 |
| Efek ke tabel `bookings` | `status` → `CANCELLED` |
| Efek ke tabel `payments` | `status` → `CANCELLED` |
| Setelah batal | Card hilang dari tab "Akan Datang", muncul di tab "Selesai" |

### B. Fitur Reschedule (Ubah Jadwal)

| Kondisi | Nilai |
|---|---|
| Status yang bisa reschedule | `PENDING` dan `DP_PAID` |
| Batas waktu maksimal | H-1 — harus dilakukan **> 24 jam** sebelum `booking_date + start_time` |
| Contoh | Jadwal 5 Mei 10:00 → reschedule hanya bisa sebelum 4 Mei 10:00 |
| Validasi slot baru | Slot baru tidak boleh bertabrakan dengan booking lain |
| Hari yang bisa dipilih | Sama dengan booking flow — hanya hari yang `Schedule.is_active = true` |
| Konfirmasi sebelum aksi | Ya — via `confirmModal(...)` SweetAlert2 |
| Efek ke tabel `bookings` | Update `booking_date`, `start_time`, `end_time` |
| UI | Slide-over Drawer dari kanan, berisi Flatpickr kalender + grid slot waktu |

### C. Logika `can_cancel` dan `can_reschedule` di DTO

```
can_cancel     = (status === PENDING)
can_reschedule = (status === PENDING || status === DP_PAID)
                 && (booking_date + start_time) > now() + 24 jam
```

---

## 2. Struktur Direktori File Baru & Modifikasi

```
app/Domains/Booking/
├── DTOs/
│   └── BookingHistoryData.php          ← MODIFIKASI (tambah can_cancel, can_reschedule, variant_duration)
├── Repositories/
│   └── BookingRepository.php           ← MODIFIKASI (tambah isSlotOccupiedExcluding)
└── Services/
    └── DashboardService.php            ← MODIFIKASI (tambah cancelBooking, rescheduleBooking)

app/Domains/User/Http/Controllers/Frontdoor/
└── DashboardController.php             ← MODIFIKASI (tambah cancel(), reschedule())

routes/frontdoor/
└── dashboard.php                       ← MODIFIKASI (tambah 2 route baru)

resources/views/components/shared/
└── drawer.blade.php                    ← BARU (reusable slide-over drawer wrapper)

resources/js/features/frontdoor/dashboard/
├── useState.js                         ← MODIFIKASI (tambah state batal & reschedule)
├── useCancel.js                        ← BARU (logika konfirmasi & submit batal)
├── useReschedule.js                    ← BARU (logika drawer, flatpickr, fetch slot, submit)
└── Dashboard.js                        ← MODIFIKASI (inject useCancel & useReschedule)

resources/views/components/frontdoor/dashboard/jadwal/
├── booking-card.blade.php              ← MODIFIKASI (tambah tombol Batal & Ubah Jadwal)
└── reschedule-drawer.blade.php         ← BARU (konten drawer menggunakan x-shared.drawer)

resources/views/frontdoor/dashboard/
└── jadwal.blade.php                    ← MODIFIKASI (inject activeDays via window.pageConfig, include drawer)
```

---

## 3. DTO — `BookingHistoryData` (Modifikasi)

**File:** `app/Domains/Booking/DTOs/BookingHistoryData.php`

Tambahkan 3 field baru: `can_cancel`, `can_reschedule`, `variant_duration`.
`variant_duration` dibutuhkan oleh JS untuk mengirim parameter `duration` ke endpoint `getAvailableSlots`.

```php
<?php

namespace App\Domains\Booking\DTOs;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Support\Formatter;
use Carbon\Carbon;
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
        public readonly bool    $is_upcoming,
        public readonly bool    $can_pay,
        public readonly bool    $can_cancel,        // TAMBAH
        public readonly bool    $can_reschedule,    // TAMBAH
        public readonly int     $variant_duration,  // TAMBAH (menit)
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

        $isUpcoming  = in_array($booking->status->value, $upcomingStatuses);
        $canPay      = $booking->status === BookingStatus::PENDING;
        $canCancel   = $booking->status === BookingStatus::PENDING;

        // Reschedule: hanya jika status PENDING atau DP_PAID
        // DAN waktu booking masih > 24 jam ke depan (aturan H-1)
        $bookingDateTime = Carbon::parse($booking->booking_date . ' ' . $booking->start_time);
        $canReschedule   = in_array($booking->status, [BookingStatus::PENDING, BookingStatus::DP_PAID])
            && $bookingDateTime->isAfter(Carbon::now()->addHours(24));

        return new self(
            id:                    $booking->id,
            booking_code:          $booking->booking_code,
            package_name:          $booking->packageVariant?->package?->name ?? '-',
            variant_name:          $booking->packageVariant?->name ?? '-',
            background_name:       $booking->background?->name ?? '-',
            formatted_date:        Formatter::dateId($booking->booking_date, 'l, d F Y'),
            formatted_time:        Formatter::timeRange($booking->start_time, $booking->end_time),
            status:                $booking->status->value,
            status_label:          $booking->status->label(),
            is_upcoming:           $isUpcoming,
            can_pay:               $canPay,
            can_cancel:            $canCancel,
            can_reschedule:        $canReschedule,
            variant_duration:      $booking->packageVariant?->duration ?? 30,
            formatted_total_price: Formatter::rupiah($booking->total_price),
            payment_scheme:        $booking->payment_scheme->value,
            gdrive_link:           $booking->gdrive_link,
        );
    }
}
```

---

## 4. Repository — `BookingRepository` (Modifikasi)

**File:** `app/Domains/Booking/Repositories/BookingRepository.php`

Tambahkan 1 method baru: `isSlotOccupiedExcluding` — versi `isSlotOccupied` yang mengecualikan
booking milik user sendiri agar tidak dianggap bentrok saat reschedule ke tanggal berbeda
dengan slot yang masih sama.

```php
/**
 * Cek apakah slot sudah terisi, KECUALI booking milik booking_id tertentu.
 * Digunakan saat reschedule agar slot lama milik user sendiri tidak dianggap bentrok.
 *
 * @param string $date
 * @param string $startTime
 * @param string $endTime
 * @param int    $excludeBookingId  ID booking yang sedang di-reschedule
 */
public function isSlotOccupiedExcluding(
    string $date,
    string $startTime,
    string $endTime,
    int $excludeBookingId
): bool {
    return Booking::where('booking_date', $date)
        ->where('id', '!=', $excludeBookingId)
        ->where('status', '!=', BookingStatus::CANCELLED)
        ->where(function ($query) use ($startTime, $endTime) {
            $query->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
        })
        ->exists();
}
```

---

## 5. Service — `DashboardService` (Modifikasi)

**File:** `app/Domains/Booking/Services/DashboardService.php`

Tambahkan 2 method baru di bawah `getOrCreateSnapToken()`.

### Method `cancelBooking()`

```php
/**
 * Batalkan booking milik user.
 * Hanya booking dengan status PENDING yang bisa dibatalkan.
 *
 * @param string $bookingCode
 * @param int    $userId
 */
public function cancelBooking(string $bookingCode, int $userId): void
{
    $booking = $this->repository->findByCodeAndUser($bookingCode, $userId);

    if (! $booking) {
        throw new RuntimeException('Booking tidak ditemukan.');
    }

    if ($booking->status !== BookingStatus::PENDING) {
        throw new RuntimeException('Booking ini tidak dapat dibatalkan.');
    }

    // Update status booking ke CANCELLED
    $booking->update(['status' => BookingStatus::CANCELLED]);

    // Update semua payment terkait yang masih PENDING ke CANCELLED
    $booking->payments()
        ->where('status', PaymentStatus::PENDING)
        ->update(['status' => PaymentStatus::CANCELLED]);
}
```

### Method `rescheduleBooking()`

```php
/**
 * Ubah jadwal booking milik user.
 *
 * Aturan:
 * - Status harus PENDING atau DP_PAID
 * - Booking harus masih > 24 jam ke depan (H-1)
 * - Slot baru tidak boleh bentrok dengan booking lain
 *
 * @param string $bookingCode
 * @param int    $userId
 * @param string $newDate       Format: Y-m-d
 * @param string $newStartTime  Format: H:i
 */
public function rescheduleBooking(
    string $bookingCode,
    int $userId,
    string $newDate,
    string $newStartTime
): void {
    $booking = $this->repository->findByCodeAndUser($bookingCode, $userId);

    if (! $booking) {
        throw new RuntimeException('Booking tidak ditemukan.');
    }

    $allowedStatuses = [BookingStatus::PENDING, BookingStatus::DP_PAID];
    if (! in_array($booking->status, $allowedStatuses)) {
        throw new RuntimeException('Booking ini tidak dapat diubah jadwalnya.');
    }

    // Validasi H-1: harus > 24 jam sebelum jadwal awal
    $originalDateTime = Carbon::parse($booking->booking_date . ' ' . $booking->start_time);
    if (! $originalDateTime->isAfter(Carbon::now()->addHours(24))) {
        throw new RuntimeException('Jadwal sudah terlalu dekat untuk diubah (batas H-1).');
    }

    // Hitung end_time baru berdasarkan durasi variant
    $duration   = $booking->packageVariant?->duration ?? 30;
    $newEndTime = Carbon::parse($newStartTime)->addMinutes($duration)->format('H:i');

    // Validasi slot baru tidak bentrok (kecuali dengan booking sendiri)
    if ($this->repository->isSlotOccupiedExcluding($newDate, $newStartTime, $newEndTime, $booking->id)) {
        throw new RuntimeException('Slot waktu yang dipilih sudah terisi. Silakan pilih waktu lain.');
    }

    // Update jadwal
    $booking->update([
        'booking_date' => $newDate,
        'start_time'   => $newStartTime,
        'end_time'     => $newEndTime,
    ]);
}
```

> **Import yang perlu ditambahkan di DashboardService.php:**
> ```php
> use App\Domains\Payment\Enums\PaymentStatus;
> use Carbon\Carbon;
> ```

---

## 6. Controller — `DashboardController` (Modifikasi)

**File:** `app/Domains/User/Http/Controllers/Frontdoor/DashboardController.php`

Tambahkan 2 method baru di bawah method `repay()`.

```php
/**
 * Batalkan booking milik user yang sedang login.
 */
public function cancel(Request $request): JsonResponse
{
    $request->validate([
        'booking_code' => ['required', 'string'],
    ]);

    try {
        $this->dashboardService->cancelBooking(
            bookingCode: $request->booking_code,
            userId: Auth::id(),
        );

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil dibatalkan.',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 400);
    }
}

/**
 * Ubah jadwal booking milik user yang sedang login.
 */
public function reschedule(Request $request): JsonResponse
{
    $request->validate([
        'booking_code' => ['required', 'string'],
        'new_date'     => ['required', 'date', 'after:today'],
        'new_time'     => ['required', 'date_format:H:i'],
    ]);

    try {
        $this->dashboardService->rescheduleBooking(
            bookingCode:  $request->booking_code,
            userId:       Auth::id(),
            newDate:      $request->new_date,
            newStartTime: $request->new_time,
        );

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil diubah.',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 400);
    }
}
```

---

## 7. Route — `dashboard.php` (Modifikasi)

**File:** `routes/frontdoor/dashboard.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Domains\User\Http\Controllers\Frontdoor\DashboardController;

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('frontdoor.dashboard.index');

    Route::get('/dashboard/appointments', [DashboardController::class, 'appointments'])
        ->name('frontdoor.dashboard.appointments');

    Route::post('/dashboard/repay', [DashboardController::class, 'repay'])
        ->name('frontdoor.dashboard.repay');

    // TAMBAH: Batal Booking
    Route::post('/dashboard/cancel', [DashboardController::class, 'cancel'])
        ->name('frontdoor.dashboard.cancel');

    // TAMBAH: Reschedule Booking
    Route::post('/dashboard/reschedule', [DashboardController::class, 'reschedule'])
        ->name('frontdoor.dashboard.reschedule');

    Route::get('/dashboard/profil', [DashboardController::class, 'profil'])
        ->name('frontdoor.dashboard.profil');
});
```

> **Catatan:** Jalankan `php artisan ziggy:generate` setelah menambahkan route baru agar `resources/js/ziggy.js` diperbarui.

---

## 8. Frontend — Shared Drawer Component (Baru)

**File:** `resources/views/components/shared/drawer.blade.php`

Komponen wrapper drawer yang reusable di seluruh aplikasi (frontdoor & backdoor).
Berdasarkan pola dari `addon-drawer-form.blade.php` dan `category-drawer-form.blade.php`,
diubah menjadi generik via Blade props dan named slots.

### Props

| Prop | Default | Keterangan |
|---|---|---|
| `openState` | `'false'` | Alpine expression kondisi buka/tutup. Contoh: `"state.isRescheduleOpen"` |
| `closeAction` | `''` | Alpine expression fungsi tutup. Contoh: `"closeRescheduleDrawer()"` |
| `title` | `'Drawer'` | Teks judul di header |
| `maxWidth` | `'max-w-lg'` | Tailwind class lebar panel |
| `ariaLabelledBy` | `'drawer-title'` | ID untuk `aria-labelledby` |

### Slots

| Slot | Keterangan |
|---|---|
| `default` | Isi body drawer (scrollable) |
| `footer` | Tombol-tombol aksi di sticky footer |

### Kode Lengkap

```blade
{{--
  * COMPONENT: SHARED DRAWER
  * Slide-over panel generik dari kanan layar.
  *
  * Props:
  *   - openState      : Alpine expression — kondisi buka/tutup
  *   - closeAction    : Alpine expression — fungsi menutup drawer
  *   - title          : string — judul header
  *   - maxWidth       : string — Tailwind class lebar max panel (default: max-w-lg)
  *   - ariaLabelledBy : string — ID untuk aria-labelledby
  *
  * Slots:
  *   - default  : isi body (scrollable)
  *   - footer   : tombol aksi (di sticky footer)
--}}

@props([
    'openState'      => 'false',
    'closeAction'    => '',
    'title'          => 'Drawer',
    'maxWidth'       => 'max-w-lg',
    'ariaLabelledBy' => 'drawer-title',
])

<div
  x-show="{{ $openState }}"
  x-on:keydown.escape.window="{{ $closeAction }}"
  class="relative z-50"
  x-cloak>

  {{-- Backdrop --}}
  <div
    x-show="{{ $openState }}"
    x-transition.opacity.duration.600ms
    x-on:click="{{ $closeAction }}"
    class="fixed inset-0 bg-stone-900/50"
    aria-hidden="true"></div>

  <div class="overflow-hidden fixed inset-0 pointer-events-none">
    <div class="overflow-hidden absolute inset-0">
      <div class="flex fixed inset-y-0 right-0 pl-10 max-w-full">

        {{-- Sliding Panel --}}
        <div
          x-show="{{ $openState }}"
          x-on:click.away="{{ $closeAction }}"
          role="dialog"
          aria-modal="true"
          aria-labelledby="{{ $ariaLabelledBy }}"
          x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
          x-transition:enter-start="translate-x-full"
          x-transition:enter-end="translate-x-0"
          x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
          x-transition:leave-start="translate-x-0"
          x-transition:leave-end="translate-x-full"
          class="w-screen {{ $maxWidth }} pointer-events-auto">

          <div class="flex flex-col h-full bg-stone-50 border-l border-stone-200 overflow-hidden">

            {{-- Header --}}
            <div class="p-4 border-b-2 border-stone-300 bg-stone-200 flex justify-between items-center shrink-0">
              <h2
                class="text-xl font-bold text-stone-900"
                id="{{ $ariaLabelledBy }}">
                {{ $title }}
              </h2>
              <button
                x-on:click="{{ $closeAction }}"
                type="button"
                aria-label="Tutup drawer"
                class="flex items-center px-3 py-1.5 text-stone-600 transition active:scale-[0.97] cursor-pointer hover:text-stone-900">
                <i class="ri-close-line text-2xl" aria-hidden="true"></i>
              </button>
            </div>

            {{-- Body (scrollable) --}}
            <div class="flex-1 overflow-y-auto pt-6 pb-20 px-6">
              {{ $slot }}
            </div>

            {{-- Footer --}}
            @if (isset($footer))
              <div class="p-4 border-t border-stone-200 bg-stone-100 flex justify-end items-center gap-6 shrink-0">
                {{ $footer }}
              </div>
            @endif

          </div>
        </div>

      </div>
    </div>
  </div>
</div>
```

---

## 9. Frontend — JS Modules

### A. `useState.js` (Modifikasi)

```js
export default function useState(Alpine) {
  return Alpine.reactive({
    // semua data booking dari api
    appointments: [],
    // 'upcoming' | 'past'
    activeTab: 'upcoming',
    // loading state list
    isLoading: false,
    // data booking yang sedang ditampilkan / dipilih
    selectedAppointment: null,
    // proses pembayaran midtrans
    isProcessingPayment: false,

    // BATAL ─────────────────────────────────────────────────
    // apakah sedang memproses pembatalan
    isCancelling: false,

    // RESCHEDULE ────────────────────────────────────────────
    // apakah drawer reschedule terbuka
    isRescheduleOpen: false,
    // booking yang sedang dalam proses reschedule
    rescheduleTarget: null,
    // tanggal baru yang dipilih user (format: 'Y-m-d')
    selectedRescheduleDate: null,
    // slot waktu baru yang dipilih user
    selectedRescheduleSlot: null,
    // daftar slot waktu tersedia untuk tanggal yang dipilih
    rescheduleSlots: [],
    // sedang fetch slot waktu ke server
    isFetchingRescheduleSlots: false,
    // sedang submit reschedule ke server
    isRescheduling: false,
  });
}
```

---

### B. `useCancel.js` (Baru)

**File:** `resources/js/features/frontdoor/dashboard/useCancel.js`

```js
import route from '@/lib/route';
import { Toast } from '@/lib/sweetalert';

export default function useCancel({ state, fetchAppointments }) {
  /**
   * Trigger pembatalan booking.
   * Menampilkan confirmModal sebelum eksekusi (pola sama dengan logout di sidebar).
   *
   * @param {string} bookingCode
   */
  const triggerCancel = async (bookingCode) => {
    const result = await confirmModal(
      'Batalkan Reservasi?',
      'Tindakan ini tidak dapat dibatalkan. Slot waktu Anda akan dilepas.',
      'warning',
      'Ya, Batalkan'
    );

    if (!result.isConfirmed) return;

    state.isCancelling = true;

    try {
      const res = await window.axios.post(route('frontdoor.dashboard.cancel'), {
        booking_code: bookingCode,
      });

      if (!res.data.success) {
        throw new Error(res.data.message || 'Gagal membatalkan booking.');
      }

      Toast.fire({ icon: 'success', title: 'Reservasi berhasil dibatalkan.' });

      // Tutup detail card jika booking yang dibatalkan sedang aktif
      if (state.selectedAppointment?.booking_code === bookingCode) {
        state.selectedAppointment = null;
      }

      await fetchAppointments();
    } catch (err) {
      const msg = err?.response?.data?.message || err.message;
      Toast.fire({ icon: 'error', title: msg || 'Terjadi kesalahan.' });
    } finally {
      state.isCancelling = false;
    }
  };

  return { triggerCancel };
}
```

---

### C. `useReschedule.js` (Baru)

**File:** `resources/js/features/frontdoor/dashboard/useReschedule.js`

```js
import route from '@/lib/route';
import { Toast } from '@/lib/sweetalert';

export default function useReschedule({ state, fetchAppointments }) {
  /** Buka drawer reschedule dan simpan appointment target. */
  const openRescheduleDrawer = (appointment) => {
    state.rescheduleTarget        = appointment;
    state.selectedRescheduleDate  = null;
    state.selectedRescheduleSlot  = null;
    state.rescheduleSlots         = [];
    state.isRescheduleOpen        = true;
  };

  /** Tutup drawer dan reset seluruh state reschedule. */
  const closeRescheduleDrawer = () => {
    state.isRescheduleOpen          = false;
    state.rescheduleTarget          = null;
    state.selectedRescheduleDate    = null;
    state.selectedRescheduleSlot    = null;
    state.rescheduleSlots           = [];
    state.isFetchingRescheduleSlots = false;
  };

  /**
   * Fetch slot waktu tersedia untuk tanggal yang dipilih.
   * Menggunakan endpoint existing: GET /booking/api/slots
   *
   * @param {string} date  format: 'Y-m-d'
   */
  const fetchRescheduleSlots = async (date) => {
    if (!date || !state.rescheduleTarget) return;

    state.selectedRescheduleDate    = date;
    state.selectedRescheduleSlot    = null;
    state.rescheduleSlots           = [];
    state.isFetchingRescheduleSlots = true;

    try {
      const res = await window.axios.get(route('frontdoor.booking.api.slots'), {
        params: {
          date:     date,
          duration: state.rescheduleTarget.variant_duration,
        },
      });

      state.rescheduleSlots = res.data.slots ?? [];
    } catch (err) {
      console.error('Gagal memuat slot reschedule:', err);
      Toast.fire({ icon: 'error', title: 'Gagal memuat slot waktu.' });
    } finally {
      state.isFetchingRescheduleSlots = false;
    }
  };

  /** Pilih slot waktu untuk reschedule. */
  const selectRescheduleSlot = (slot) => {
    state.selectedRescheduleSlot = slot;
  };

  /**
   * Inisialisasi Flatpickr pada elemen kalender reschedule.
   * Menonaktifkan hari-hari yang bukan jadwal aktif studio.
   *
   * activeDays dikirim dari Blade via window.pageConfig.
   * Format: array of ISO weekday (1=Senin ... 7=Minggu).
   * Flatpickr pakai 0=Minggu ... 6=Sabtu, konversi: isoDay % 7.
   *
   * @param {HTMLElement} el
   */
  const initRescheduleCalendar = (el) => {
    const activeDays      = window.pageConfig?.activeDays ?? [];
    const enabledWeekdays = activeDays.map((d) => d % 7);

    flatpickr(el, {
      inline:  true,
      minDate: 'today',
      locale:  'id',
      disable: [
        (date) => !enabledWeekdays.includes(date.getDay()),
      ],
      onChange: ([selectedDate]) => {
        if (!selectedDate) return;
        const formatted = selectedDate.toISOString().slice(0, 10);
        fetchRescheduleSlots(formatted);
      },
    });
  };

  /** Submit reschedule setelah konfirmasi user. */
  const submitReschedule = async () => {
    if (!state.selectedRescheduleDate || !state.selectedRescheduleSlot) return;

    const result = await confirmModal(
      'Konfirmasi Ubah Jadwal?',
      `Jadwal akan diubah ke ${state.selectedRescheduleDate} pukul ${state.selectedRescheduleSlot.start_time}.`,
      'question',
      'Ya, Ubah Jadwal'
    );

    if (!result.isConfirmed) return;

    state.isRescheduling = true;

    try {
      const res = await window.axios.post(route('frontdoor.dashboard.reschedule'), {
        booking_code: state.rescheduleTarget.booking_code,
        new_date:     state.selectedRescheduleDate,
        new_time:     state.selectedRescheduleSlot.start_time,
      });

      if (!res.data.success) {
        throw new Error(res.data.message || 'Gagal mengubah jadwal.');
      }

      Toast.fire({ icon: 'success', title: 'Jadwal berhasil diubah!' });

      // Tutup card detail jika booking yang di-reschedule sedang ditampilkan
      if (state.selectedAppointment?.booking_code === state.rescheduleTarget?.booking_code) {
        state.selectedAppointment = null;
      }

      closeRescheduleDrawer();
      await fetchAppointments();
    } catch (err) {
      const msg = err?.response?.data?.message || err.message;
      Toast.fire({ icon: 'error', title: msg || 'Terjadi kesalahan.' });
    } finally {
      state.isRescheduling = false;
    }
  };

  return {
    openRescheduleDrawer,
    closeRescheduleDrawer,
    fetchRescheduleSlots,
    selectRescheduleSlot,
    initRescheduleCalendar,
    submitReschedule,
  };
}
```

---

### D. `Dashboard.js` (Modifikasi)

```js
import useState from './useState.js';
import useAppointments from './useAppointments.js';
import useDetail from './useDetail.js';
import usePayment from './usePayment.js';
import usePagination from './usePagination.js';
import useCancel from './useCancel.js';         // TAMBAH
import useReschedule from './useReschedule.js'; // TAMBAH

export default function Dashboard(Alpine) {
  const state = useState(Alpine);

  const { fetchAppointments, filteredAppointments, switchTab } = useAppointments({ state });
  const { showDetail, clearDetail, hasDetail } = useDetail({ state });
  const { triggerRepay } = usePayment({ state, fetchAppointments });
  const paginationControls = usePagination({ state, fetchCallback: fetchAppointments });
  const { triggerCancel } = useCancel({ state, fetchAppointments });          // TAMBAH
  const rescheduleControls = useReschedule({ state, fetchAppointments });     // TAMBAH

  const init = () => {
    fetchAppointments();
  };

  return {
    state,
    init,
    ...paginationControls,

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

    // Cancel
    triggerCancel,

    // Reschedule
    ...rescheduleControls,
  };
}
```

---

## 10. Frontend — Blade Views

### A. `jadwal.blade.php` (Modifikasi)

**File:** `resources/views/frontdoor/dashboard/jadwal.blade.php`

Tambahkan di `<x-slot:heads>`: Flatpickr CDN + injeksi `window.pageConfig.activeDays`.
Tambahkan di bawah main content (masih di dalam `x-data="Dashboard"`): `<x-frontdoor.dashboard.jadwal.reschedule-drawer />`.

Perubahan spesifik pada `<x-slot:heads>`:

```blade
<x-slot:heads>
  {{-- Midtrans Snap JS --}}
  <script type="text/javascript" src="{{ config('midtrans.snap_js_url') }}"
    data-client-key="{{ config('midtrans.client_key') }}"></script>

  {{-- Flatpickr (CDN) --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

  {{-- Injeksi activeDays untuk Flatpickr reschedule --}}
  @php
    $activeDays = \App\Domains\MasterData\Models\Schedule::where('is_active', true)
        ->pluck('day')
        ->toArray();
  @endphp
  <script>
    window.pageConfig = window.pageConfig ?? {};
    window.pageConfig.activeDays = @json($activeDays);
  </script>
</x-slot:heads>
```

Tambahkan di dalam `<div x-data="Dashboard">`, setelah `</main>` dan sebelum `</div>`:

```blade
{{-- Reschedule Drawer (di dalam x-data="Dashboard" agar bisa akses state) --}}
<x-frontdoor.dashboard.jadwal.reschedule-drawer />
```

---

### B. `booking-card.blade.php` (Modifikasi)

**File:** `resources/views/components/frontdoor/dashboard/jadwal/booking-card.blade.php`

Di dalam `<div class="space-y-2 mt-auto">` (section biaya & aksi), tambahkan 2 tombol baru
**di bawah** tombol "Lanjutkan Pembayaran". Urutan akhir tombol: Bayar → Ubah Jadwal → Batalkan.

```blade
{{-- btn reschedule start --}}
<x-shared.button
  x-show="appointment.can_reschedule"
  x-on:click="openRescheduleDrawer(appointment)"
  class="w-full py-2.5 text-sm font-semibold border border-stone-300 text-stone-700 bg-white hover:bg-stone-50">
  Ubah Jadwal
</x-shared.button>
{{-- btn reschedule end --}}

{{-- btn batal start --}}
<x-shared.button
  x-show="appointment.can_cancel"
  x-on:click="triggerCancel(appointment.booking_code)"
  x-bind:disabled="state.isCancelling"
  class="w-full py-2.5 text-sm font-semibold border border-red-300 text-red-600 bg-white hover:bg-red-50">
  <span x-show="!state.isCancelling">Batalkan Reservasi</span>
  <span x-show="state.isCancelling">Membatalkan...</span>
</x-shared.button>
{{-- btn batal end --}}
```

---

### C. `reschedule-drawer.blade.php` (Baru)

**File:** `resources/views/components/frontdoor/dashboard/jadwal/reschedule-drawer.blade.php`

```blade
{{--
  * COMPONENT: RESCHEDULE DRAWER
  * Slide-over drawer untuk fitur Ubah Jadwal.
  *
  * Menggunakan x-shared.drawer sebagai wrapper.
  * Terhubung ke Alpine state dari Dashboard.js (via useReschedule.js).
  *
  * State yang diakses:
  *   - state.isRescheduleOpen
  *   - state.rescheduleTarget
  *   - state.selectedRescheduleDate
  *   - state.selectedRescheduleSlot
  *   - state.rescheduleSlots
  *   - state.isFetchingRescheduleSlots
  *   - state.isRescheduling
  *
  * Methods yang dipanggil:
  *   - closeRescheduleDrawer()
  *   - initRescheduleCalendar($el)
  *   - selectRescheduleSlot(slot)
  *   - submitReschedule()
--}}

<x-shared.drawer
  openState="state.isRescheduleOpen"
  closeAction="closeRescheduleDrawer()"
  title="Ubah Jadwal"
  maxWidth="max-w-lg"
  ariaLabelledBy="reschedule-drawer-title">

  {{-- Info booking yang sedang di-reschedule --}}
  <div class="mb-6 p-4 border border-stone-200 bg-white text-sm space-y-1">
    <p class="text-stone-500 text-xs uppercase tracking-wide font-medium">Jadwal Saat Ini</p>
    <p class="font-semibold text-stone-900" x-text="state.rescheduleTarget?.formatted_date"></p>
    <p class="text-stone-600" x-text="state.rescheduleTarget?.formatted_time"></p>
  </div>

  <div class="h-px bg-stone-200 mb-6"></div>

  {{-- Step 1: Pilih Tanggal (Flatpickr Inline) --}}
  <div class="mb-6">
    <p class="text-sm font-semibold text-stone-900 mb-4">Pilih Tanggal Baru</p>
    <div class="flex justify-center">
      {{-- Input tersembunyi, Flatpickr di-init via $watch saat drawer terbuka --}}
      <input
        type="text"
        class="hidden"
        x-ref="rescheduleCalendarInput"
        x-init="
          $watch('state.isRescheduleOpen', (val) => {
            if (val) {
              $nextTick(() => initRescheduleCalendar($refs.rescheduleCalendarInput));
            }
          })
        ">
    </div>
  </div>

  <div class="h-px bg-stone-200 mb-6"></div>

  {{-- Step 2: Pilih Slot Waktu --}}
  <div>
    <p class="text-sm font-semibold text-stone-900 mb-4">
      <template x-if="state.selectedRescheduleDate">
        <span x-text="'Pilih Waktu — ' + state.selectedRescheduleDate"></span>
      </template>
      <template x-if="!state.selectedRescheduleDate">
        <span class="text-stone-400 font-normal">Pilih tanggal terlebih dahulu...</span>
      </template>
    </p>

    {{-- Loading slot --}}
    <template x-if="state.isFetchingRescheduleSlots">
      <div class="flex items-center gap-2 text-stone-500 text-sm py-4">
        <i class="ri-loader-4-line animate-spin"></i>
        <span>Memuat slot tersedia...</span>
      </div>
    </template>

    {{-- Grid slot waktu --}}
    <template x-if="state.selectedRescheduleDate && !state.isFetchingRescheduleSlots">
      <div>
        <template x-if="state.rescheduleSlots.length === 0">
          <p class="text-sm text-stone-400 py-4">Tidak ada slot tersedia pada tanggal ini.</p>
        </template>

        <div class="grid grid-cols-2 gap-3">
          <template x-for="slot in state.rescheduleSlots" :key="slot.start_time">
            <x-shared.button
              type="button"
              size="custom"
              x-on:click="selectRescheduleSlot(slot)"
              x-bind:class="state.selectedRescheduleSlot?.start_time === slot.start_time
                ? 'border-stone-700 bg-stone-800 text-white'
                : 'border-stone-200 bg-white text-stone-600 hover:border-stone-400'"
              class="w-full border py-3 text-sm">
              <span x-text="slot.start_time"></span>
            </x-shared.button>
          </template>
        </div>
      </div>
    </template>
  </div>

  {{-- Footer --}}
  <x-slot:footer>
    <button
      type="button"
      x-on:click="closeRescheduleDrawer()"
      x-bind:disabled="state.isRescheduling"
      class="text-stone-600 hover:text-stone-900 transition font-semibold text-sm cursor-pointer disabled:opacity-50">
      Batal
    </button>
    <x-shared.button
      type="button"
      x-on:click="submitReschedule()"
      x-bind:disabled="!state.selectedRescheduleSlot || state.isRescheduling"
      class="bg-stone-700 text-stone-50 px-4 py-2 border border-stone-700 hover:bg-stone-800 font-semibold text-sm tracking-wide disabled:opacity-50 disabled:pointer-events-none">
      <span x-text="state.isRescheduling ? 'Menyimpan...' : 'Konfirmasi Ubah Jadwal'"></span>
    </x-shared.button>
  </x-slot:footer>

</x-shared.drawer>
```

---

## 11. Catatan Implementasi

### A. Urutan Pengerjaan yang Direkomendasikan

1. Modifikasi `BookingHistoryData.php` — tambah `can_cancel`, `can_reschedule`, `variant_duration`
2. Modifikasi `BookingRepository.php` — tambah `isSlotOccupiedExcluding()`
3. Modifikasi `DashboardService.php` — tambah `cancelBooking()` dan `rescheduleBooking()`
4. Modifikasi `DashboardController.php` — tambah `cancel()` dan `reschedule()`
5. Modifikasi `routes/frontdoor/dashboard.php` — tambah 2 route baru
6. Jalankan `php artisan ziggy:generate` — perbarui `ziggy.js`
7. Buat `resources/views/components/shared/drawer.blade.php`
8. Modifikasi `useState.js` — tambah state batal & reschedule
9. Buat `useCancel.js`
10. Buat `useReschedule.js`
11. Modifikasi `Dashboard.js` — inject composable baru
12. Modifikasi `booking-card.blade.php` — tambah tombol aksi
13. Buat `reschedule-drawer.blade.php`
14. Modifikasi `jadwal.blade.php` — inject `pageConfig.activeDays` + include drawer

### B. Dependency Flatpickr

Flatpickr dimuat via CDN di `<x-slot:heads>` pada `jadwal.blade.php`. Pastikan urutan script:
1. Flatpickr CSS
2. Flatpickr JS core
3. Flatpickr locale `id` (Bahasa Indonesia)

### C. `isSlotOccupiedExcluding` vs `isSlotOccupied`

Saat reschedule, **wajib** menggunakan `isSlotOccupiedExcluding` (bukan `isSlotOccupied`).
Alasannya: jika user hanya mengganti tanggal dengan slot yang sama, `isSlotOccupied` akan
mendeteksi slot lama milik user sendiri sebagai "terisi" dan reschedule gagal — ini adalah
*false positive* yang harus dihindari.

### D. Konversi Hari ISO ke Flatpickr

Tabel `schedules` menggunakan format ISO weekday (`1 = Senin`, `7 = Minggu`).
Flatpickr menggunakan format JavaScript Date (`0 = Minggu`, `6 = Sabtu`).
Konversi: `isoDay % 7`.

| ISO | Hari | Flatpickr |
|---|---|---|
| 1 | Senin | 1 |
| 2 | Selasa | 2 |
| 3 | Rabu | 3 |
| 4 | Kamis | 4 |
| 5 | Jumat | 5 |
| 6 | Sabtu | 6 |
| 7 | Minggu | 0 (hasil `7 % 7`) |
