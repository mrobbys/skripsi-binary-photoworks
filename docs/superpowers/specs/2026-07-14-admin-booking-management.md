# Spec: Admin Backdoor — Manajemen Pemesanan (Booking Management)

**Tanggal:** 2026-07-14
**Branch:** `feat/admin-booking-management`
**Scope:** Backend (Controller, Service, DTO, FormRequest) + Frontend (Alpine.js Modules, Blade Views)
**Stack:** Laravel 13 · PHP 8.3 · Alpine.js · Tailwind CSS v4 · Axios · Choices.js · Flatpickr
**Design System:** ThoughtStream (flat, no rounded corners, no shadows, stone palette, border separators, high-density admin UI)

---

## Daftar Isi

1. [Aturan Bisnis & Logika Domain](#1-aturan-bisnis--logika-domain)
2. [Struktur Direktori File Baru & Modifikasi](#2-struktur-direktori-file-baru--modifikasi)
3. [Backend — Routes](#3-backend--routes)
4. [Backend — DTOs](#4-backend--dtos)
5. [Backend — FormRequests](#5-backend--formrequests)
6. [Backend — Services](#6-backend--services)
7. [Backend — Controller](#7-backend--controller)
8. [Frontend — JS Modules (Alpine.js)](#8-frontend--js-modules-alpinejs)
9. [Frontend — Blade Views](#9-frontend--blade-views)
10. [Catatan Implementasi](#10-catatan-implementasi)

---

## 1. Aturan Bisnis & Logika Domain

### A. Aturan Umum

| Aturan | Detail |
|---|---|
| Klien | Wajib terdaftar di aplikasi (memiliki akun). Admin mencari via Choices.js. |
| Booking Code | Digenerate otomatis oleh sistem saat `store()`. Format mengikuti generator yang sudah ada. |
| Edit Paket/Tanggal | **DILARANG KERAS.** Perubahan paket menggunakan metode "Void & Recreate" (batal booking lama, buat baru). |

### B. Aksi Baris Tabel (Row Actions)

| Aksi | Kondisi Muncul | Efek |
|---|---|---|
| **Lihat Detail** | Selalu | Redirect ke `/admin/operations/bookings/{id}` |
| **Tandai Lunas** | Status = `DP_PAID` saja | Insert baris payment ke-2 (40%), update status booking → `SUCCESS` |
| **Input Link GDrive** | Status = `SUCCESS` atau `DONE` | Simpan `gdrive_link`, kirim notif WA via Fonnte |
| **Batalkan Booking** | Status bukan `CANCELLED` | Update status booking → `CANCELLED`, update payment → `CANCELLED` |

### C. Aturan Ledger Pembayaran (Tabel `payments`)

1. **Booking Manual Lunas:** Insert 1 baris `payments` (`amount` = total harga, `status` = `SETTLEMENT`, `payment_purpose` = `LUNAS`, `payment_type` = `cash`).
2. **Booking Manual Pending:** Insert 1 baris `payments` (`amount` = 0, `status` = `PENDING`, `payment_purpose` = `DP`).
3. **Tandai Lunas (dari DP_PAID):** Insert baris payment ke-2 (`amount` = sisa 40%, `status` = `SETTLEMENT`, `payment_purpose` = `PELUNASAN`, `payment_type` = `cash`).

### D. Aturan Add-ons

- `has_quantity = true` → input qty bebas (min 1, max ditentukan logika bisnis).
- `has_quantity = false` → input qty dikunci di angka `1` (disabled).
- Upsell di Halaman Detail: langsung INSERT ke `addon_booking`, update `total_price` di `bookings`.

### E. Widget Statistik (Formula Kalkulasi)

```
Total Transaksi Sukses = SUM(payments.amount) WHERE status = 'Settlement'
Jumlah Lunas          = COUNT(bookings)       WHERE status = 'Lunas'
Jumlah DP Terbayar    = COUNT(bookings)       WHERE status = 'DP Terbayar'
```

---

## 2. Struktur Direktori File Baru & Modifikasi

```
app/Domains/Booking/
├── DTOs/
│   ├── ManualBookingData.php          ← BARU
│   └── UpsellAddonData.php            ← BARU
├── Http/
│   ├── Controllers/Backdoor/
│   │   └── ManageBookingController.php ← BARU
│   └── Requests/
│       ├── StoreManualBookingRequest.php ← BARU
│       └── UpsellAddonRequest.php        ← BARU
└── Services/
    ├── CreateManualBookingService.php  ← BARU
    └── SettleBookingService.php        ← BARU

routes/
└── web.php                             ← MODIFIKASI (tambah grup admin bookings)

resources/views/backdoor/operations/bookings/
├── index.blade.php                     ← BARU
├── create.blade.php                    ← BARU
└── show.blade.php                      ← BARU

resources/js/features/backdoor/bookings/
├── Index.js                            ← BARU
├── Create.js                           ← BARU
├── Show.js                             ← BARU
└── useUpsellAddon.js                   ← BARU
```

---

## 3. Backend — Routes

Tambahkan grup rute berikut di dalam grup middleware `auth` dan `role:admin` yang sudah ada di `routes/web.php`.

```php
// routes/web.php — di dalam grup admin

use App\Domains\Booking\Http\Controllers\Backdoor\ManageBookingController;

Route::prefix('operations/bookings')->name('backdoor.bookings.')->group(function () {
    Route::get('/',             [ManageBookingController::class, 'index'])->name('index');
    Route::get('/create',       [ManageBookingController::class, 'create'])->name('create');
    Route::post('/',            [ManageBookingController::class, 'store'])->name('store');
    Route::get('/{booking}',    [ManageBookingController::class, 'show'])->name('show');
    Route::patch('/{booking}/settle',  [ManageBookingController::class, 'settle'])->name('settle');
    Route::patch('/{booking}/gdrive',  [ManageBookingController::class, 'updateGdrive'])->name('gdrive');
    Route::delete('/{booking}', [ManageBookingController::class, 'cancel'])->name('cancel');
    Route::post('/{booking}/addons',   [ManageBookingController::class, 'upsellAddon'])->name('addons.upsell');
});
```

---

## 4. Backend — DTOs

### 4.1 `ManualBookingData.php`

```php
<?php

namespace App\Domains\Booking\DTOs;

use Spatie\LaravelData\Data;

class ManualBookingData extends Data
{
    public function __construct(
        public readonly int    $user_id,
        public readonly int    $package_variant_id,
        public readonly int    $background_id,
        public readonly string $booking_date,
        public readonly string $start_time,
        public readonly string $status,
        public readonly array  $addons,  // [['addon_id' => int, 'quantity' => int], ...]
    ) {}
}
```

### 4.2 `UpsellAddonData.php`

```php
<?php

namespace App\Domains\Booking\DTOs;

use Spatie\LaravelData\Data;

class UpsellAddonData extends Data
{
    public function __construct(
        public readonly int $addon_id,
        public readonly int $quantity,
    ) {}
}
```

---

## 5. Backend — FormRequests

### 5.1 `StoreManualBookingRequest.php`

```php
<?php

namespace App\Domains\Booking\Http\Requests;

use App\Domains\Booking\DTOs\ManualBookingData;
use App\Domains\Booking\Enums\BookingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManualBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'            => ['required', 'integer', 'exists:users,id'],
            'package_variant_id' => ['required', 'integer', 'exists:package_variants,id'],
            'background_id'      => ['required', 'integer', 'exists:backgrounds,id'],
            'booking_date'       => ['required', 'date', 'after_or_equal:today'],
            'start_time'         => ['required', 'date_format:H:i'],
            'status'             => ['required', Rule::in([BookingStatus::PENDING->value, BookingStatus::SUCCESS->value])],
            'addons'             => ['nullable', 'array'],
            'addons.*.addon_id'  => ['required_with:addons', 'integer', 'exists:addons,id'],
            'addons.*.quantity'  => ['required_with:addons', 'integer', 'min:1'],
        ];
    }

    public function toDto(): ManualBookingData
    {
        $validated = $this->validated();

        return new ManualBookingData(
            user_id:            $validated['user_id'],
            package_variant_id: $validated['package_variant_id'],
            background_id:      $validated['background_id'],
            booking_date:       $validated['booking_date'],
            start_time:         $validated['start_time'],
            status:             $validated['status'],
            addons:             $validated['addons'] ?? [],
        );
    }

    public function messages(): array
    {
        return [
            'user_id.required'            => 'Klien wajib dipilih.',
            'user_id.exists'              => 'Klien tidak ditemukan.',
            'package_variant_id.required' => 'Paket & varian wajib dipilih.',
            'package_variant_id.exists'   => 'Varian paket tidak ditemukan.',
            'background_id.required'      => 'Background wajib dipilih.',
            'booking_date.required'       => 'Tanggal sesi wajib diisi.',
            'booking_date.after_or_equal' => 'Tanggal sesi tidak boleh di masa lalu.',
            'start_time.required'         => 'Slot waktu wajib dipilih.',
            'status.required'             => 'Status booking wajib dipilih.',
            'status.in'                   => 'Status tidak valid.',
            'addons.*.addon_id.exists'    => 'Salah satu layanan tambahan tidak ditemukan.',
            'addons.*.quantity.min'       => 'Kuantitas layanan tambahan minimal 1.',
        ];
    }
}
```

### 5.2 `UpsellAddonRequest.php`

```php
<?php

namespace App\Domains\Booking\Http\Requests;

use App\Domains\Booking\DTOs\UpsellAddonData;
use Illuminate\Foundation\Http\FormRequest;

class UpsellAddonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'addon_id' => ['required', 'integer', 'exists:addons,id'],
            'quantity'  => ['required', 'integer', 'min:1'],
        ];
    }

    public function toDto(): UpsellAddonData
    {
        $validated = $this->validated();

        return new UpsellAddonData(
            addon_id: $validated['addon_id'],
            quantity:  $validated['quantity'],
        );
    }

    public function messages(): array
    {
        return [
            'addon_id.required' => 'Layanan tambahan wajib dipilih.',
            'addon_id.exists'   => 'Layanan tambahan tidak ditemukan.',
            'quantity.required' => 'Kuantitas wajib diisi.',
            'quantity.min'      => 'Kuantitas minimal 1.',
        ];
    }
}
```

---

## 6. Backend — Services

### 6.1 `CreateManualBookingService.php`

Service ini menangani seluruh transaksi pembuatan booking manual secara atomik.

```php
<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\DTOs\ManualBookingData;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\Payment\Enums\PaymentPurpose;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateManualBookingService
{
    /**
     * Jalankan pembuatan booking manual dalam satu transaksi database.
     */
    public function execute(ManualBookingData $data): Booking
    {
        return DB::transaction(function () use ($data) {
            $variant = PackageVariant::findOrFail($data->package_variant_id);
            $status  = BookingStatus::from($data->status);

            // 1. Hitung end_time berdasarkan durasi varian
            $startTime = \Carbon\Carbon::createFromFormat('H:i', $data->start_time);
            $endTime   = $startTime->copy()->addMinutes($variant->duration_minutes);

            // 2. Hitung total_price: harga varian + semua addon
            $addonIds    = collect($data->addons)->pluck('addon_id')->toArray();
            $addonModels = Addon::whereIn('id', $addonIds)->get()->keyBy('id');

            $addonTotal = collect($data->addons)->sum(function ($item) use ($addonModels) {
                $addon = $addonModels->get($item['addon_id']);
                return $addon ? $addon->price * $item['quantity'] : 0;
            });

            $totalPrice = $variant->price + $addonTotal;

            // 3. Buat booking
            $booking = Booking::create([
                'booking_code'       => $this->generateBookingCode(),
                'user_id'            => $data->user_id,
                'package_variant_id' => $data->package_variant_id,
                'background_id'      => $data->background_id,
                'booking_date'       => $data->booking_date,
                'start_time'         => $startTime->format('H:i:s'),
                'end_time'           => $endTime->format('H:i:s'),
                'total_price'        => $totalPrice,
                'payment_scheme'     => \App\Domains\Booking\Enums\PaymentScheme::FULL,
                'status'             => $status,
                'gdrive_link'        => null,
                'source'             => 'manual',
            ]);

            // 4. Attach addons ke pivot
            foreach ($data->addons as $item) {
                $addon = $addonModels->get($item['addon_id']);
                if (! $addon) continue;

                $booking->addons()->attach($item['addon_id'], [
                    'quantity'          => $item['quantity'],
                    'price_at_purchase' => $addon->price,
                ]);
            }

            // 5. Insert ledger payment
            if ($status === BookingStatus::SUCCESS) {
                // Bayar lunas di tempat (cash/QRIS statis)
                Payment::create([
                    'booking_id'      => $booking->id,
                    'order_id'        => $booking->booking_code,
                    'amount'          => $totalPrice,
                    'status'          => PaymentStatus::SETTLEMENT,
                    'payment_purpose' => PaymentPurpose::LUNAS,
                    'payment_type'    => 'cash',
                    'pay_date'        => now(),
                ]);
            } else {
                // Pending — insert placeholder payment
                Payment::create([
                    'booking_id'      => $booking->id,
                    'order_id'        => $booking->booking_code,
                    'amount'          => 0,
                    'status'          => PaymentStatus::PENDING,
                    'payment_purpose' => PaymentPurpose::DP,
                    'payment_type'    => null,
                    'pay_date'        => null,
                ]);
            }

            return $booking;
        });
    }

    /**
     * Generate booking code unik.
     * Format: BPW-YYMMDD-{4 huruf random uppercase}
     */
    private function generateBookingCode(): string
    {
        do {
            $code = 'BPW-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));
        } while (Booking::where('booking_code', $code)->exists());

        return $code;
    }
}
```

### 6.2 `SettleBookingService.php`

Service ini menangani pelunasan kasir (insert payment ke-2 sebesar 40% sisa).

```php
<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\Payment\Enums\PaymentPurpose;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Models\Payment;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SettleBookingService
{
    /**
     * Tandai booking sebagai lunas penuh.
     * Insert baris payment ke-2 (sisa 40%) dan update status booking.
     */
    public function execute(Booking $booking): void
    {
        if ($booking->status !== BookingStatus::DP_PAID) {
            throw new RuntimeException('Booking ini tidak dalam status DP Terbayar.');
        }

        DB::transaction(function () use ($booking) {
            // Hitung total yang sudah masuk
            $totalPaid = $booking->payments()
                ->where('status', PaymentStatus::SETTLEMENT)
                ->sum('amount');

            $remaining = $booking->total_price - $totalPaid;

            if ($remaining <= 0) {
                throw new RuntimeException('Tagihan booking ini sudah lunas sepenuhnya.');
            }

            // Insert baris ledger pelunasan kasir
            Payment::create([
                'booking_id'      => $booking->id,
                'order_id'        => $booking->booking_code . '-PLN',
                'amount'          => $remaining,
                'status'          => PaymentStatus::SETTLEMENT,
                'payment_purpose' => PaymentPurpose::PELUNASAN,
                'payment_type'    => 'cash',
                'pay_date'        => now(),
            ]);

            // Update status booking
            $booking->update(['status' => BookingStatus::SUCCESS]);
        });
    }
}
```

---

## 7. Backend — Controller

### 7.1 `ManageBookingController.php`

```php
<?php

namespace App\Domains\Booking\Http\Controllers\Backdoor;

use App\Domains\Booking\Http\Requests\StoreManualBookingRequest;
use App\Domains\Booking\Http\Requests\UpsellAddonRequest;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Services\CreateManualBookingService;
use App\Domains\Booking\Services\SettleBookingService;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Models\Background;
use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Models\Payment;
use App\Domains\User\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ManageBookingController extends Controller
{
    public function __construct(
        private readonly CreateManualBookingService $createService,
        private readonly SettleBookingService       $settleService,
    ) {}

    /**
     * Halaman Index: Widget statistik + DataTables (AJAX).
     */
    public function index(Request $request): View|JsonResponse
    {
        // Jika request dari DataTables AJAX (search)
        if ($request->expectsJson()) {
            $query = Booking::with(['user', 'packageVariant.package', 'payments'])
                ->whereIn('status', [
                    BookingStatus::PENDING,
                    BookingStatus::DP_PAID,
                    BookingStatus::SUCCESS,
                    BookingStatus::DONE,
                ])
                ->latest();

            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('booking_code', 'ilike', "%{$search}%")
                      ->orWhereHas('user', fn ($u) => $u->where('name', 'ilike', "%{$search}%"));
                });
            }

            $bookings = $query->paginate(15);

            return response()->json($bookings);
        }

        // Widget statistik
        $stats = [
            'total_revenue' => Payment::where('status', PaymentStatus::SETTLEMENT)->sum('amount'),
            'count_success' => Booking::where('status', BookingStatus::SUCCESS)->count(),
            'count_dp_paid' => Booking::where('status', BookingStatus::DP_PAID)->count(),
        ];

        return view('backdoor.operations.bookings.index', compact('stats'));
    }

    /**
     * Halaman Create: Form pembuatan booking manual.
     */
    public function create(): View
    {
        $users    = User::orderBy('name')->get(['id', 'name', 'email']);
        $packages = Package::with('variants')->where('is_active', true)->orderBy('name')->get();
        $backgrounds = Background::where('is_active', true)->orderBy('name')->get();
        $addons   = Addon::where('is_active', true)->orderBy('name')->get();

        return view('backdoor.operations.bookings.create', compact(
            'users', 'packages', 'backgrounds', 'addons'
        ));
    }

    /**
     * Simpan booking manual baru.
     */
    public function store(StoreManualBookingRequest $request): RedirectResponse
    {
        $booking = $this->createService->execute($request->toDto());

        return redirect()
            ->route('backdoor.bookings.show', $booking)
            ->with('success', "Booking {$booking->booking_code} berhasil dibuat.");
    }

    /**
     * Halaman Detail: Ringkasan booking (read-only) + inline upsell addon.
     */
    public function show(Booking $booking): View
    {
        $booking->load([
            'user',
            'packageVariant.package',
            'background',
            'addons',
            'payments',
        ]);

        $availableAddons = Addon::where('is_active', true)->orderBy('name')->get();

        return view('backdoor.operations.bookings.show', compact('booking', 'availableAddons'));
    }

    /**
     * Tandai booking lunas penuh (pelunasan kasir manual).
     */
    public function settle(Booking $booking): JsonResponse
    {
        try {
            $this->settleService->execute($booking);

            return response()->json([
                'success' => true,
                'message' => "Booking {$booking->booking_code} berhasil ditandai lunas.",
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan server.'], 500);
        }
    }

    /**
     * Simpan link Google Drive hasil edit foto.
     */
    public function updateGdrive(Request $request, Booking $booking): JsonResponse
    {
        $validated = $request->validate([
            'gdrive_link' => ['required', 'url', 'max:500'],
        ], [
            'gdrive_link.required' => 'Link Google Drive wajib diisi.',
            'gdrive_link.url'      => 'Format link tidak valid.',
        ]);

        $booking->update(['gdrive_link' => $validated['gdrive_link'], 'status' => BookingStatus::DONE]);

        // TODO: Kirim notifikasi WA via Fonnte Service
        // FonnteService::sendGdriveLink($booking);

        return response()->json([
            'success' => true,
            'message' => "Link GDrive berhasil disimpan. Notifikasi WA dikirim ke {$booking->user->phone}.",
        ]);
    }

    /**
     * Batalkan booking.
     */
    public function cancel(Booking $booking): JsonResponse
    {
        DB::transaction(function () use ($booking) {
            $booking->update(['status' => BookingStatus::CANCELLED]);
            $booking->payments()->update(['status' => \App\Domains\Payment\Enums\PaymentStatus::CANCELLED]);
        });

        return response()->json([
            'success' => true,
            'message' => "Booking {$booking->booking_code} berhasil dibatalkan.",
        ]);
    }

    /**
     * Tambah addon ke booking yang sudah ada (upsell di halaman detail).
     */
    public function upsellAddon(UpsellAddonRequest $request, Booking $booking): JsonResponse
    {
        $dto   = $request->toDto();
        $addon = Addon::findOrFail($dto->addon_id);

        DB::transaction(function () use ($booking, $addon, $dto) {
            // Attach atau update pivot (jika addon sama sudah ada, qty bertambah)
            if ($booking->addons()->where('addon_id', $addon->id)->exists()) {
                $booking->addons()->updateExistingPivot($addon->id, [
                    'quantity' => DB::raw("quantity + {$dto->quantity}"),
                ]);
            } else {
                $booking->addons()->attach($addon->id, [
                    'quantity'          => $dto->quantity,
                    'price_at_purchase' => $addon->price,
                ]);
            }

            // Update total_price booking
            $addonSubtotal = $addon->price * $dto->quantity;
            $booking->increment('total_price', $addonSubtotal);
        });

        return response()->json([
            'success' => true,
            'message' => "Layanan \"{$addon->name}\" berhasil ditambahkan ke tagihan.",
        ]);
    }
}
```

---

## 8. Frontend — JS Modules (Alpine.js)

### 8.1 `Index.js` — DataTables Handler

```javascript
/**
 * Logika halaman Index Manajemen Pemesanan.
 * Menangani DataTables native Alpine.js dengan live search debounce 400ms,
 * serta aksi baris (settle, gdrive, cancel).
 *
 * File: resources/js/features/backdoor/bookings/Index.js
 * Penggunaan di Blade: <div x-data="Index">
 *
 * @param {import('alpinejs').Alpine} Alpine
 */
import route from "@/lib/route";
import { Toast, confirmModal } from "@/lib/sweetalert";

export default function Index(Alpine) {
  // ---------------------------------------------------------------------------
  // State
  // ---------------------------------------------------------------------------
  const state = Alpine.reactive({
    bookings:    [],
    meta:        {},
    currentPage: 1,
    search:      '',
    isLoading:   false,

    // GDrive modal inline
    gdriveBookingId: null,
    gdriveLink:      '',
    isGdriveOpen:    false,
    isGdriveLoading: false,
  });

  // ---------------------------------------------------------------------------
  // Side Effects — search debounce 400ms
  // ---------------------------------------------------------------------------
  let debounceTimer = null;
  Alpine.effect(() => {
    const keyword = state.search;
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      state.currentPage = 1;
      fetchBookings(keyword, 1);
    }, 400);
  });

  // ---------------------------------------------------------------------------
  // Methods
  // ---------------------------------------------------------------------------

  /** Ambil data bookings dari server */
  const fetchBookings = async (search = '', page = 1) => {
    state.isLoading = true;
    try {
      const res = await window.axios.get(route('backdoor.bookings.index'), {
        params: { search, page },
        headers: { Accept: 'application/json' },
      });
      state.bookings    = res.data.data;
      state.meta        = res.data.meta ?? res.data;
      state.currentPage = page;
    } catch {
      Toast.fire({ icon: 'error', title: 'Gagal memuat data pemesanan.' });
    } finally {
      state.isLoading = false;
    }
  };

  /** Inisialisasi — muat data pertama kali */
  const init = () => fetchBookings();

  /** Navigasi halaman DataTables */
  const goToPage = (page) => fetchBookings(state.search, page);

  /** Tandai lunas (settle) */
  const settle = async (bookingId, bookingCode) => {
    const confirmed = await confirmModal({
      title: 'Tandai Lunas?',
      text:  `Booking ${bookingCode} akan ditandai lunas penuh. Pastikan pembayaran sudah diterima di kasir.`,
      confirmButtonText: 'Ya, Tandai Lunas',
    });
    if (!confirmed) return;

    try {
      const res = await window.axios.patch(route('backdoor.bookings.settle', bookingId));
      Toast.fire({ icon: 'success', title: res.data.message });
      fetchBookings(state.search, state.currentPage);
    } catch (err) {
      const msg = err?.response?.data?.message ?? 'Gagal memproses pelunasan.';
      Toast.fire({ icon: 'error', title: msg });
    }
  };

  /** Buka form input GDrive */
  const openGdrive = (bookingId) => {
    state.gdriveBookingId = bookingId;
    state.gdriveLink      = '';
    state.isGdriveOpen    = true;
  };

  /** Tutup form GDrive */
  const closeGdrive = () => {
    state.isGdriveOpen    = false;
    state.gdriveBookingId = null;
    state.gdriveLink      = '';
  };

  /** Submit link GDrive */
  const submitGdrive = async () => {
    state.isGdriveLoading = true;
    try {
      const res = await window.axios.patch(
        route('backdoor.bookings.gdrive', state.gdriveBookingId),
        { gdrive_link: state.gdriveLink }
      );
      Toast.fire({ icon: 'success', title: res.data.message });
      closeGdrive();
      fetchBookings(state.search, state.currentPage);
    } catch (err) {
      const msg = err?.response?.data?.message ?? 'Gagal menyimpan link GDrive.';
      Toast.fire({ icon: 'error', title: msg });
    } finally {
      state.isGdriveLoading = false;
    }
  };

  /** Batalkan booking */
  const cancel = async (bookingId, bookingCode) => {
    const confirmed = await confirmModal({
      title:             'Batalkan Booking?',
      text:              `Booking ${bookingCode} akan dibatalkan secara permanen.`,
      icon:              'warning',
      confirmButtonText: 'Ya, Batalkan',
    });
    if (!confirmed) return;

    try {
      const res = await window.axios.delete(route('backdoor.bookings.cancel', bookingId));
      Toast.fire({ icon: 'success', title: res.data.message });
      fetchBookings(state.search, state.currentPage);
    } catch (err) {
      const msg = err?.response?.data?.message ?? 'Gagal membatalkan booking.';
      Toast.fire({ icon: 'error', title: msg });
    }
  };

  // ---------------------------------------------------------------------------
  // Return
  // ---------------------------------------------------------------------------
  return {
    state,
    init,
    goToPage,
    settle,
    openGdrive,
    closeGdrive,
    submitGdrive,
    cancel,
  };
}
```

### 8.2 `Create.js` — Form Booking Manual

```javascript
/**
 * Logika form pembuatan booking manual oleh admin.
 * Mengelola dropdown bertingkat (Paket → Varian → Time Slot),
 * Flatpickr kalender, dan Dynamic Repeater untuk Add-ons.
 *
 * File: resources/js/features/backdoor/bookings/Create.js
 * Penggunaan di Blade: <div x-data="Create">
 *
 * @param {import('alpinejs').Alpine} Alpine
 */
import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";

export default function Create(Alpine) {
  // ---------------------------------------------------------------------------
  // State
  // ---------------------------------------------------------------------------
  const state = Alpine.reactive({
    // Form fields
    userId:           null,
    packageId:        null,
    variantId:        null,
    backgroundId:     null,
    bookingDate:      '',
    startTime:        '',
    bookingStatus:    '',
    addons:           [], // [{ addon_id: null, quantity: 1, has_quantity: true }]

    // Computed
    variants:        [],  // Dimuat saat packageId berubah
    timeSlots:       [],  // Dimuat saat variantId + bookingDate berubah
    totalPrice:      0,   // Dikalkulasi secara reaktif

    // UI state
    isLoading:       false,
    errors:          {},

    // Data master (diinisialisasi dari Blade via init())
    allPackages:     [],
    allAddons:       [],
  });

  // ---------------------------------------------------------------------------
  // Side Effects
  // ---------------------------------------------------------------------------

  /** Muat varian ketika paket berubah */
  Alpine.effect(() => {
    if (state.packageId) {
      const pkg = state.allPackages.find(p => p.id == state.packageId);
      state.variants  = pkg ? pkg.variants : [];
      state.variantId = null;
      state.timeSlots = [];
      state.startTime = '';
    }
  });

  /** Kalkulasi total price secara reaktif */
  Alpine.effect(() => {
    const variant = state.variants.find(v => v.id == state.variantId);
    const basePrice = variant ? variant.price : 0;

    const addonTotal = state.addons.reduce((sum, item) => {
      const addon = state.allAddons.find(a => a.id == item.addon_id);
      return sum + (addon ? addon.price * item.quantity : 0);
    }, 0);

    state.totalPrice = basePrice + addonTotal;
  });

  // ---------------------------------------------------------------------------
  // Methods
  // ---------------------------------------------------------------------------

  /** Inisialisasi — terima data master dari Blade */
  const init = (packages, addons) => {
    state.allPackages = packages;
    state.allAddons   = addons;
  };

  /** Muat time slots berdasarkan varian & tanggal yang dipilih */
  const loadTimeSlots = async () => {
    if (!state.variantId || !state.bookingDate) return;

    try {
      const res = await window.axios.get(route('api.timeslots'), {
        params: {
          variant_id:   state.variantId,
          booking_date: state.bookingDate,
        },
      });
      state.timeSlots = res.data;
      state.startTime = '';
    } catch {
      Toast.fire({ icon: 'error', title: 'Gagal memuat slot waktu.' });
    }
  };

  /** Tambah baris add-on baru di Repeater */
  const addAddonRow = () => {
    state.addons.push({ addon_id: null, quantity: 1 });
  };

  /** Hapus baris add-on dari Repeater */
  const removeAddonRow = (index) => {
    state.addons.splice(index, 1);
  };

  /**
   * Cek apakah addon yang dipilih di baris ini memiliki has_quantity = false.
   * Jika ya, paksa quantity = 1 dan disable input qty.
   */
  const onAddonChange = (index) => {
    const addonId = state.addons[index].addon_id;
    const addon   = state.allAddons.find(a => a.id == addonId);
    if (addon && !addon.has_quantity) {
      state.addons[index].quantity = 1;
    }
  };

  const isQtyDisabled = (index) => {
    const addonId = state.addons[index].addon_id;
    const addon   = state.allAddons.find(a => a.id == addonId);
    return addon ? !addon.has_quantity : false;
  };

  /** Submit form */
  const submit = async () => {
    state.isLoading = true;
    state.errors    = {};

    const payload = {
      user_id:            state.userId,
      package_variant_id: state.variantId,
      background_id:      state.backgroundId,
      booking_date:       state.bookingDate,
      start_time:         state.startTime,
      status:             state.bookingStatus,
      addons:             state.addons.filter(a => a.addon_id),
    };

    try {
      const res = await window.axios.post(route('backdoor.bookings.store'), payload);
      // Redirect ke halaman detail (server mengirim redirect, axios ikuti)
      window.location.href = res.request.responseURL;
    } catch (err) {
      if (err.response?.status === 422) {
        state.errors = err.response.data.errors;
        Toast.fire({ icon: 'warning', title: 'Periksa kembali isian form.' });
        return;
      }
      Toast.fire({ icon: 'error', title: 'Terjadi kesalahan. Coba lagi.' });
    } finally {
      state.isLoading = false;
    }
  };

  // ---------------------------------------------------------------------------
  // Return
  // ---------------------------------------------------------------------------
  return {
    state,
    init,
    loadTimeSlots,
    addAddonRow,
    removeAddonRow,
    onAddonChange,
    isQtyDisabled,
    submit,
  };
}
```

### 8.3 `useUpsellAddon.js` — Composable Upsell Detail

```javascript
/**
 * Composable hook untuk form Inline Upsell Add-on di halaman Detail.
 *
 * File: resources/js/features/backdoor/bookings/useUpsellAddon.js
 */
import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";

export default function useUpsellAddon({ state }) {
  const submitUpsell = async (bookingId) => {
    if (!state.upsell.addonId) {
      Toast.fire({ icon: 'warning', title: 'Pilih layanan tambahan terlebih dahulu.' });
      return;
    }

    state.upsell.isLoading = true;

    try {
      const res = await window.axios.post(
        route('backdoor.bookings.addons.upsell', bookingId),
        {
          addon_id: state.upsell.addonId,
          quantity:  state.upsell.quantity,
        }
      );

      Toast.fire({ icon: 'success', title: res.data.message });

      // Reset form upsell & reload halaman agar total price ter-update
      state.upsell.addonId  = null;
      state.upsell.quantity = 1;
      window.location.reload();
    } catch (err) {
      const msg = err?.response?.data?.message ?? 'Gagal menambahkan layanan tambahan.';
      Toast.fire({ icon: 'error', title: msg });
    } finally {
      state.upsell.isLoading = false;
    }
  };

  const onUpsellAddonChange = (allAddons) => {
    const addon = allAddons.find(a => a.id == state.upsell.addonId);
    if (addon && !addon.has_quantity) {
      state.upsell.quantity = 1;
    }
  };

  return { submitUpsell, onUpsellAddonChange };
}
```

### 8.4 `Show.js` — Orchestrator Detail Page

```javascript
/**
 * Logika halaman Detail Pemesanan.
 * Menangani aksi Tandai Lunas, Input GDrive, dan Inline Upsell Add-on.
 *
 * File: resources/js/features/backdoor/bookings/Show.js
 * Penggunaan di Blade: <div x-data="Show">
 *
 * @param {import('alpinejs').Alpine} Alpine
 */
import route from "@/lib/route";
import { Toast, confirmModal } from "@/lib/sweetalert";
import useUpsellAddon from "./useUpsellAddon.js";

export default function Show(Alpine) {
  // ---------------------------------------------------------------------------
  // State
  // ---------------------------------------------------------------------------
  const state = Alpine.reactive({
    bookingId:   null,
    bookingCode: '',

    // Aksi GDrive
    gdriveLink:      '',
    isGdriveLoading: false,

    // Upsell add-on inline
    upsell: {
      addonId:   null,
      quantity:  1,
      isLoading: false,
    },

    // Data master addons (dari Blade)
    allAddons: [],
  });

  // ---------------------------------------------------------------------------
  // Composable hooks
  // ---------------------------------------------------------------------------
  const { submitUpsell, onUpsellAddonChange } = useUpsellAddon({ state });

  // ---------------------------------------------------------------------------
  // Methods
  // ---------------------------------------------------------------------------

  /** Inisialisasi — terima data dari Blade */
  const init = (bookingId, bookingCode, addons) => {
    state.bookingId   = bookingId;
    state.bookingCode = bookingCode;
    state.allAddons   = addons;
  };

  /** Tandai Lunas */
  const settle = async () => {
    const confirmed = await confirmModal({
      title: 'Tandai Lunas?',
      text:  `Booking ${state.bookingCode} akan ditandai lunas penuh. Pastikan pembayaran sudah diterima di kasir.`,
      confirmButtonText: 'Ya, Tandai Lunas',
    });
    if (!confirmed) return;

    try {
      const res = await window.axios.patch(route('backdoor.bookings.settle', state.bookingId));
      Toast.fire({ icon: 'success', title: res.data.message });
      window.location.reload();
    } catch (err) {
      const msg = err?.response?.data?.message ?? 'Gagal memproses pelunasan.';
      Toast.fire({ icon: 'error', title: msg });
    }
  };

  /** Simpan Link GDrive */
  const submitGdrive = async () => {
    state.isGdriveLoading = true;
    try {
      const res = await window.axios.patch(
        route('backdoor.bookings.gdrive', state.bookingId),
        { gdrive_link: state.gdriveLink }
      );
      Toast.fire({ icon: 'success', title: res.data.message });
      window.location.reload();
    } catch (err) {
      const msg = err?.response?.data?.message ?? 'Gagal menyimpan link GDrive.';
      Toast.fire({ icon: 'error', title: msg });
    } finally {
      state.isGdriveLoading = false;
    }
  };

  // ---------------------------------------------------------------------------
  // Return
  // ---------------------------------------------------------------------------
  return {
    state,
    init,
    settle,
    submitGdrive,
    submitUpsell: () => submitUpsell(state.bookingId),
    onUpsellAddonChange: () => onUpsellAddonChange(state.allAddons),
  };
}
```

---

## 9. Frontend — Blade Views

### 9.1 `index.blade.php`

```blade
<x-layouts.backdoor
    title="Manajemen Pemesanan"
    js-module="backdoor/bookings/Index">

    <x-slot:content>
        <div x-data="Index" x-init="init()" x-cloak>

            {{-- ============================================================ --}}
            {{-- WIDGET STATISTIK                                              --}}
            {{-- ============================================================ --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-px border border-stone-200 bg-stone-200 mb-6">

                {{-- Total Transaksi Sukses --}}
                <div class="bg-white p-6">
                    <p class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1">Total Transaksi Sukses</p>
                    <p class="text-2xl font-bold text-stone-900">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
                </div>

                {{-- Booking Lunas --}}
                <div class="bg-white p-6">
                    <p class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1">Booking Lunas</p>
                    <p class="text-2xl font-bold text-stone-900">{{ $stats['count_success'] }} <span class="text-sm font-normal text-stone-400">Sesi</span></p>
                </div>

                {{-- DP Terbayar --}}
                <div class="bg-white p-6">
                    <p class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1">DP Terbayar</p>
                    <p class="text-2xl font-bold text-stone-900">{{ $stats['count_dp_paid'] }} <span class="text-sm font-normal text-stone-400">Sesi</span></p>
                </div>

            </div>

            {{-- ============================================================ --}}
            {{-- TOOLBAR: SEARCH + TAMBAH                                     --}}
            {{-- ============================================================ --}}
            <div class="flex items-center justify-between gap-4 mb-4">
                <div class="relative flex-1 max-w-sm">
                    <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 text-sm"></i>
                    <input
                        type="text"
                        x-model="state.search"
                        placeholder="Cari kode booking atau nama klien..."
                        class="w-full border border-stone-300 bg-white pl-9 pr-4 py-2 text-sm text-stone-900 placeholder-stone-400 focus:outline-none focus:border-stone-500 focus:ring-1 focus:ring-stone-500">
                </div>
                <a href="{{ route('backdoor.bookings.create') }}"
                   class="inline-flex items-center gap-2 bg-stone-800 text-white text-sm font-semibold px-4 py-2 hover:bg-stone-900 transition-colors">
                    <i class="ri-add-line"></i> Tambah Booking
                </a>
            </div>

            {{-- ============================================================ --}}
            {{-- DATATABLES                                                   --}}
            {{-- ============================================================ --}}
            <div class="border border-stone-200 bg-white">
                <table class="w-full text-sm">
                    <thead class="bg-stone-100 border-b border-stone-200">
                        <tr>
                            <th class="text-left text-xs font-semibold text-stone-500 uppercase tracking-wider px-4 py-3 w-10">No.</th>
                            <th class="text-left text-xs font-semibold text-stone-500 uppercase tracking-wider px-4 py-3">Kode Booking</th>
                            <th class="text-left text-xs font-semibold text-stone-500 uppercase tracking-wider px-4 py-3">Nama Klien</th>
                            <th class="text-left text-xs font-semibold text-stone-500 uppercase tracking-wider px-4 py-3">Jadwal Sesi</th>
                            <th class="text-left text-xs font-semibold text-stone-500 uppercase tracking-wider px-4 py-3">Paket & Varian</th>
                            <th class="text-left text-xs font-semibold text-stone-500 uppercase tracking-wider px-4 py-3">Total Bayar</th>
                            <th class="text-center text-xs font-semibold text-stone-500 uppercase tracking-wider px-4 py-3">Status</th>
                            <th class="text-center text-xs font-semibold text-stone-500 uppercase tracking-wider px-4 py-3 w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">

                        {{-- Loading state --}}
                        <template x-if="state.isLoading">
                            <tr>
                                <td colspan="8" class="text-center text-stone-400 py-12 text-sm">
                                    <i class="ri-loader-4-line animate-spin text-xl block mb-2"></i>
                                    Memuat data...
                                </td>
                            </tr>
                        </template>

                        {{-- Empty state --}}
                        <template x-if="!state.isLoading && state.bookings.length === 0">
                            <tr>
                                <td colspan="8" class="text-center text-stone-400 py-12 text-sm">
                                    <i class="ri-inbox-2-line text-3xl block mb-2"></i>
                                    Tidak ada data pemesanan ditemukan.
                                </td>
                            </tr>
                        </template>

                        {{-- Data rows --}}
                        <template x-for="(booking, index) in state.bookings" :key="booking.id">
                            <tr class="hover:bg-stone-50 transition-colors">
                                <td class="px-4 py-3 text-stone-400 text-xs" x-text="index + 1 + ((state.meta.current_page - 1) * state.meta.per_page)"></td>
                                <td class="px-4 py-3 font-mono font-semibold text-stone-800 text-xs" x-text="booking.booking_code"></td>
                                <td class="px-4 py-3 text-stone-700" x-text="booking.user?.name ?? '-'"></td>
                                <td class="px-4 py-3 text-stone-600 text-xs" x-text="booking.booking_date"></td>
                                <td class="px-4 py-3 text-stone-600 text-xs">
                                    <span x-text="booking.package_variant?.package?.name ?? '-'"></span>
                                    <span class="text-stone-400"> — </span>
                                    <span x-text="booking.package_variant?.name ?? ''"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-stone-800 text-xs" x-text="'Rp ' + Number(booking.total_price).toLocaleString('id-ID')"></span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-block px-2 py-0.5 text-xs font-bold uppercase tracking-wide border"
                                        :class="{
                                            'bg-emerald-50 text-emerald-700 border-emerald-200': booking.status === 'Lunas',
                                            'bg-sky-50 text-sky-700 border-sky-200':             booking.status === 'DP Terbayar',
                                            'bg-amber-50 text-amber-700 border-amber-200':       booking.status === 'Menunggu',
                                            'bg-stone-100 text-stone-500 border-stone-200':      booking.status === 'Batal',
                                            'bg-violet-50 text-violet-700 border-violet-200':    booking.status === 'Selesai',
                                        }"
                                        x-text="booking.status">
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center relative">
                                    {{-- Dropdown aksi 3 titik --}}
                                    <div x-data="{ open: false }" class="relative inline-block">
                                        <button @click="open = !open" type="button"
                                            class="p-1.5 text-stone-400 hover:text-stone-700 hover:bg-stone-100 transition-colors">
                                            <i class="ri-more-2-fill text-base"></i>
                                        </button>
                                        <div
                                            x-show="open"
                                            x-on:click.outside="open = false"
                                            x-transition.opacity
                                            class="absolute right-0 z-20 mt-1 w-48 bg-white border border-stone-200 py-1">

                                            {{-- Lihat Detail --}}
                                            <a :href="`/admin/operations/bookings/${booking.id}`"
                                               class="flex items-center gap-2 px-4 py-2 text-sm text-stone-700 hover:bg-stone-50">
                                                <i class="ri-eye-line text-stone-400"></i> Lihat Detail
                                            </a>

                                            {{-- Tandai Lunas — hanya jika DP_PAID --}}
                                            <template x-if="booking.status === 'DP Terbayar'">
                                                <button type="button"
                                                    @click="open = false; settle(booking.id, booking.booking_code)"
                                                    class="w-full flex items-center gap-2 px-4 py-2 text-sm text-emerald-700 hover:bg-stone-50">
                                                    <i class="ri-checkbox-circle-line text-emerald-500"></i> Tandai Lunas
                                                </button>
                                            </template>

                                            {{-- Input GDrive — hanya jika Lunas/Selesai --}}
                                            <template x-if="booking.status === 'Lunas' || booking.status === 'Selesai'">
                                                <button type="button"
                                                    @click="open = false; openGdrive(booking.id)"
                                                    class="w-full flex items-center gap-2 px-4 py-2 text-sm text-sky-700 hover:bg-stone-50">
                                                    <i class="ri-drive-line text-sky-500"></i> Input Link GDrive
                                                </button>
                                            </template>

                                            {{-- Batalkan Booking --}}
                                            <template x-if="booking.status !== 'Batal'">
                                                <button type="button"
                                                    @click="open = false; cancel(booking.id, booking.booking_code)"
                                                    class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-stone-50">
                                                    <i class="ri-close-circle-line text-red-400"></i> Batalkan Booking
                                                </button>
                                            </template>

                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>

                    </tbody>
                </table>

                {{-- Pagination --}}
                <div class="flex items-center justify-between px-4 py-3 border-t border-stone-200 bg-stone-50" x-show="state.meta.last_page > 1">
                    <p class="text-xs text-stone-500">
                        Menampilkan <span x-text="state.meta.from ?? 0"></span>–<span x-text="state.meta.to ?? 0"></span>
                        dari <span x-text="state.meta.total ?? 0"></span> data
                    </p>
                    <div class="flex gap-1">
                        <button @click="goToPage(state.currentPage - 1)" :disabled="state.currentPage <= 1"
                            class="px-3 py-1.5 text-xs border border-stone-200 text-stone-600 hover:bg-stone-100 disabled:opacity-40 disabled:pointer-events-none transition">
                            <i class="ri-arrow-left-s-line"></i>
                        </button>
                        <button @click="goToPage(state.currentPage + 1)" :disabled="state.currentPage >= state.meta.last_page"
                            class="px-3 py-1.5 text-xs border border-stone-200 text-stone-600 hover:bg-stone-100 disabled:opacity-40 disabled:pointer-events-none transition">
                            <i class="ri-arrow-right-s-line"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- POPUP FORM INPUT GDRIVE (Inline Modal)                       --}}
            {{-- ============================================================ --}}
            <div
                x-show="state.isGdriveOpen"
                x-on:keydown.escape.window="closeGdrive()"
                class="fixed inset-0 z-50 flex items-center justify-center"
                x-cloak>

                {{-- Backdrop --}}
                <div class="absolute inset-0 bg-stone-900/60" @click="closeGdrive()"></div>

                {{-- Dialog --}}
                <div class="relative z-10 bg-white border border-stone-200 w-full max-w-md mx-4 p-6">
                    <h3 class="text-base font-bold text-stone-900 mb-4">Input Link Google Drive</h3>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wide mb-2">
                            URL Folder Google Drive <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="url"
                            x-model="state.gdriveLink"
                            placeholder="https://drive.google.com/drive/folders/..."
                            class="w-full border border-stone-300 bg-white px-3 py-2.5 text-sm text-stone-900 placeholder-stone-400 focus:outline-none focus:border-stone-500 focus:ring-1 focus:ring-stone-500">
                    </div>

                    <div class="flex justify-end gap-4 mt-6">
                        <button type="button" @click="closeGdrive()"
                            class="text-sm font-semibold text-stone-600 hover:text-stone-900">
                            Batal
                        </button>
                        <button type="button" @click="submitGdrive()" :disabled="state.isGdriveLoading || !state.gdriveLink"
                            class="bg-stone-800 text-white text-sm font-semibold px-4 py-2 hover:bg-stone-900 disabled:opacity-50 disabled:pointer-events-none transition">
                            <span x-text="state.isGdriveLoading ? 'Menyimpan...' : 'Simpan & Kirim WA'"></span>
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </x-slot:content>

</x-layouts.backdoor>
```

### 9.2 `create.blade.php`

```blade
<x-layouts.backdoor
    title="Tambah Booking Manual"
    js-module="backdoor/bookings/Create">

    <x-slot:content>
        <div
            x-data="Create"
            x-init="init(
                {{ Js::from($packages) }},
                {{ Js::from($addons) }}
            )"
            x-cloak>

            {{-- Header --}}
            <div class="flex items-center gap-4 mb-6">
                <a href="{{ route('backdoor.bookings.index') }}"
                   class="text-stone-400 hover:text-stone-700 transition-colors">
                    <i class="ri-arrow-left-line text-xl"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-stone-900">Tambah Booking Manual</h1>
                    <p class="text-xs text-stone-500 mt-0.5">Buat pesanan baru untuk klien yang datang langsung atau via WhatsApp.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- ============================================================ --}}
                {{-- KOLOM KIRI: Form Utama                                       --}}
                {{-- ============================================================ --}}
                <div class="lg:col-span-2 space-y-5">

                    {{-- Section: Informasi Klien --}}
                    <div class="border border-stone-200 bg-white">
                        <div class="px-5 py-3 border-b border-stone-200 bg-stone-50">
                            <h2 class="text-xs font-bold text-stone-700 uppercase tracking-widest">Informasi Klien</h2>
                        </div>
                        <div class="p-5">
                            <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wide mb-2">
                                Pilih Klien <span class="text-red-500">*</span>
                            </label>
                            <select x-data="choices({ placeholder: true, placeholderValue: '--- Cari nama atau email klien ---', searchPlaceholderValue: 'Ketik nama klien...' })"
                                x-modelable="value"
                                x-model="state.userId">
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} — {{ $user->email }}</option>
                                @endforeach
                            </select>
                            <template x-if="state.errors['user_id']">
                                <p class="mt-1.5 text-xs text-red-600" x-text="state.errors['user_id'][0]"></p>
                            </template>
                        </div>
                    </div>

                    {{-- Section: Detail Paket --}}
                    <div class="border border-stone-200 bg-white">
                        <div class="px-5 py-3 border-b border-stone-200 bg-stone-50">
                            <h2 class="text-xs font-bold text-stone-700 uppercase tracking-widest">Detail Paket</h2>
                        </div>
                        <div class="p-5 space-y-4">

                            {{-- Pilih Paket --}}
                            <div>
                                <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wide mb-2">
                                    Paket <span class="text-red-500">*</span>
                                </label>
                                <select x-data="choices({ placeholder: true, placeholderValue: '--- Pilih Paket ---' })"
                                    x-modelable="value"
                                    x-model="state.packageId">
                                    @foreach ($packages as $pkg)
                                        <option value="{{ $pkg->id }}">{{ $pkg->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Pilih Varian (muncul setelah paket dipilih) --}}
                            <div x-show="state.variants.length > 0">
                                <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wide mb-2">
                                    Varian <span class="text-red-500">*</span>
                                </label>
                                <select
                                    x-data="choices({ placeholder: true, placeholderValue: '--- Pilih Varian ---' })"
                                    x-modelable="value"
                                    x-model="state.variantId"
                                    @change="loadTimeSlots()">
                                    <template x-for="variant in state.variants" :key="variant.id">
                                        <option :value="variant.id" x-text="variant.name + ' (' + variant.duration_minutes + ' menit) — Rp ' + Number(variant.price).toLocaleString('id-ID')"></option>
                                    </template>
                                </select>
                                <template x-if="state.errors['package_variant_id']">
                                    <p class="mt-1.5 text-xs text-red-600" x-text="state.errors['package_variant_id'][0]"></p>
                                </template>
                            </div>

                            {{-- Pilih Background --}}
                            <div>
                                <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wide mb-2">
                                    Background <span class="text-red-500">*</span>
                                </label>
                                <select x-data="choices({ placeholder: true, placeholderValue: '--- Pilih Background ---' })"
                                    x-modelable="value"
                                    x-model="state.backgroundId">
                                    @foreach ($backgrounds as $bg)
                                        <option value="{{ $bg->id }}">{{ $bg->name }}</option>
                                    @endforeach
                                </select>
                                <template x-if="state.errors['background_id']">
                                    <p class="mt-1.5 text-xs text-red-600" x-text="state.errors['background_id'][0]"></p>
                                </template>
                            </div>

                        </div>
                    </div>

                    {{-- Section: Jadwal Sesi --}}
                    <div class="border border-stone-200 bg-white">
                        <div class="px-5 py-3 border-b border-stone-200 bg-stone-50">
                            <h2 class="text-xs font-bold text-stone-700 uppercase tracking-widest">Jadwal Sesi</h2>
                        </div>
                        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

                            {{-- Tanggal (Flatpickr) --}}
                            <div>
                                <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wide mb-2">
                                    Tanggal Sesi <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    x-model="state.bookingDate"
                                    x-init="flatpickr($el, {
                                        dateFormat: 'Y-m-d',
                                        minDate: 'today',
                                        locale: 'id',
                                        onChange: (selectedDates, dateStr) => {
                                            state.bookingDate = dateStr;
                                            loadTimeSlots();
                                        }
                                    })"
                                    placeholder="Pilih tanggal..."
                                    readonly
                                    class="w-full border border-stone-300 bg-white px-3 py-2.5 text-sm text-stone-900 placeholder-stone-400 focus:outline-none focus:border-stone-500 focus:ring-1 focus:ring-stone-500 cursor-pointer">
                                <template x-if="state.errors['booking_date']">
                                    <p class="mt-1.5 text-xs text-red-600" x-text="state.errors['booking_date'][0]"></p>
                                </template>
                            </div>

                            {{-- Slot Waktu --}}
                            <div>
                                <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wide mb-2">
                                    Slot Waktu <span class="text-red-500">*</span>
                                </label>
                                <select
                                    x-data="choices({ placeholder: true, placeholderValue: '--- Pilih Slot Waktu ---' })"
                                    x-modelable="value"
                                    x-model="state.startTime"
                                    :disabled="state.timeSlots.length === 0">
                                    <template x-for="slot in state.timeSlots" :key="slot.start">
                                        <option :value="slot.start" :disabled="slot.is_taken"
                                            x-text="slot.start + ' – ' + slot.end + (slot.is_taken ? ' (Terpesan)' : '')"></option>
                                    </template>
                                </select>
                                <template x-if="state.errors['start_time']">
                                    <p class="mt-1.5 text-xs text-red-600" x-text="state.errors['start_time'][0]"></p>
                                </template>
                            </div>

                        </div>
                    </div>

                    {{-- Section: Layanan Tambahan (Dynamic Repeater) --}}
                    <div class="border border-stone-200 bg-white">
                        <div class="px-5 py-3 border-b border-stone-200 bg-stone-50 flex justify-between items-center">
                            <h2 class="text-xs font-bold text-stone-700 uppercase tracking-widest">Layanan Tambahan</h2>
                            <button type="button" @click="addAddonRow()"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-stone-600 hover:text-stone-900 transition-colors">
                                <i class="ri-add-line"></i> Tambah Layanan
                            </button>
                        </div>
                        <div class="p-5 space-y-3">

                            <template x-if="state.addons.length === 0">
                                <p class="text-xs text-stone-400 italic">Belum ada layanan tambahan. Klik "+ Tambah Layanan" untuk menambahkan.</p>
                            </template>

                            <template x-for="(item, index) in state.addons" :key="index">
                                <div class="flex items-center gap-3">

                                    {{-- Dropdown Addon --}}
                                    <div class="flex-1">
                                        <select
                                            x-data="choices({ placeholder: true, placeholderValue: '--- Pilih Layanan ---' })"
                                            x-modelable="value"
                                            x-model="state.addons[index].addon_id"
                                            @change="onAddonChange(index)">
                                            <template x-for="addon in state.allAddons" :key="addon.id">
                                                <option :value="addon.id"
                                                    x-text="addon.name + ' — Rp ' + Number(addon.price).toLocaleString('id-ID')"></option>
                                            </template>
                                        </select>
                                    </div>

                                    {{-- Input Qty --}}
                                    <div class="w-20">
                                        <input
                                            type="number"
                                            x-model.number="state.addons[index].quantity"
                                            :disabled="isQtyDisabled(index)"
                                            min="1"
                                            class="w-full border border-stone-300 bg-white px-3 py-2.5 text-sm text-center text-stone-900 focus:outline-none focus:border-stone-500 disabled:bg-stone-100 disabled:text-stone-400">
                                    </div>

                                    {{-- Hapus baris --}}
                                    <button type="button" @click="removeAddonRow(index)"
                                        class="text-stone-300 hover:text-red-500 transition-colors p-1">
                                        <i class="ri-delete-bin-line text-lg"></i>
                                    </button>

                                </div>
                            </template>

                        </div>
                    </div>

                </div>

                {{-- ============================================================ --}}
                {{-- KOLOM KANAN: Ringkasan & Aksi                                --}}
                {{-- ============================================================ --}}
                <div class="space-y-4">

                    {{-- Ringkasan Harga --}}
                    <div class="border border-stone-200 bg-white">
                        <div class="px-5 py-3 border-b border-stone-200 bg-stone-50">
                            <h2 class="text-xs font-bold text-stone-700 uppercase tracking-widest">Ringkasan Pesanan</h2>
                        </div>
                        <div class="p-5">
                            <div class="flex justify-between items-center border-t border-stone-200 pt-3">
                                <span class="text-sm font-bold text-stone-900">Total Harga</span>
                                <span class="text-lg font-bold text-stone-900"
                                    x-text="'Rp ' + state.totalPrice.toLocaleString('id-ID')"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Status Booking --}}
                    <div class="border border-stone-200 bg-white">
                        <div class="px-5 py-3 border-b border-stone-200 bg-stone-50">
                            <h2 class="text-xs font-bold text-stone-700 uppercase tracking-widest">Status Awal Booking</h2>
                        </div>
                        <div class="p-5">
                            <select x-data="choices({ placeholder: true, placeholderValue: '--- Pilih Status ---' })"
                                x-modelable="value"
                                x-model="state.bookingStatus">
                                <option value="Lunas">LUNAS — Bayar di tempat (Cash/QRIS)</option>
                                <option value="Menunggu">PENDING — Menunggu Transfer WA</option>
                            </select>
                            <template x-if="state.errors['status']">
                                <p class="mt-1.5 text-xs text-red-600" x-text="state.errors['status'][0]"></p>
                            </template>
                            <p class="text-xs text-stone-400 mt-2">
                                <i class="ri-information-line"></i>
                                Pilih <strong>LUNAS</strong> jika klien bayar langsung di studio.
                                Pilih <strong>PENDING</strong> jika menunggu transfer dari WA.
                            </p>
                        </div>
                    </div>

                    {{-- Tombol Simpan --}}
                    <button type="button" @click="submit()" :disabled="state.isLoading"
                        class="w-full bg-stone-800 text-white text-sm font-bold py-3 hover:bg-stone-900 transition-colors disabled:opacity-50 disabled:pointer-events-none">
                        <span x-text="state.isLoading ? 'Menyimpan...' : 'Simpan Pesanan'"></span>
                    </button>

                    <a href="{{ route('backdoor.bookings.index') }}"
                       class="block w-full text-center text-sm font-semibold text-stone-600 hover:text-stone-900 py-2 border border-stone-200 hover:bg-stone-50 transition-colors">
                        Batal
                    </a>

                </div>

            </div>

        </div>
    </x-slot:content>

</x-layouts.backdoor>
```

### 9.3 `show.blade.php`

```blade
<x-layouts.backdoor
    title="Detail Booking — {{ $booking->booking_code }}"
    js-module="backdoor/bookings/Show">

    <x-slot:content>
        <div
            x-data="Show"
            x-init="init(
                {{ $booking->id }},
                '{{ $booking->booking_code }}',
                {{ Js::from($availableAddons) }}
            )"
            x-cloak>

            {{-- Header --}}
            <div class="flex items-start justify-between mb-6">
                <div class="flex items-center gap-4">
                    <a href="{{ route('backdoor.bookings.index') }}"
                       class="text-stone-400 hover:text-stone-700 transition-colors">
                        <i class="ri-arrow-left-line text-xl"></i>
                    </a>
                    <div>
                        <h1 class="text-xl font-bold text-stone-900 font-mono">{{ $booking->booking_code }}</h1>
                        <p class="text-xs text-stone-500 mt-0.5">Detail Pemesanan — Hanya Baca</p>
                    </div>
                </div>

                {{-- Badge Status --}}
                <span class="inline-block px-3 py-1 text-xs font-bold uppercase tracking-widest border
                    @if($booking->status === \App\Domains\Booking\Enums\BookingStatus::SUCCESS) bg-emerald-50 text-emerald-700 border-emerald-200
                    @elseif($booking->status === \App\Domains\Booking\Enums\BookingStatus::DP_PAID) bg-sky-50 text-sky-700 border-sky-200
                    @elseif($booking->status === \App\Domains\Booking\Enums\BookingStatus::PENDING) bg-amber-50 text-amber-700 border-amber-200
                    @elseif($booking->status === \App\Domains\Booking\Enums\BookingStatus::CANCELLED) bg-stone-100 text-stone-500 border-stone-200
                    @else bg-violet-50 text-violet-700 border-violet-200 @endif">
                    {{ $booking->status->label() }}
                </span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- ============================================================ --}}
                {{-- KOLOM KIRI: Rincian Booking                                  --}}
                {{-- ============================================================ --}}
                <div class="lg:col-span-2 space-y-5">

                    {{-- Info Klien --}}
                    <div class="border border-stone-200 bg-white">
                        <div class="px-5 py-3 border-b border-stone-200 bg-stone-50">
                            <h2 class="text-xs font-bold text-stone-700 uppercase tracking-widest">Informasi Klien</h2>
                        </div>
                        <div class="p-5 grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-stone-400 uppercase tracking-wide mb-1">Nama</p>
                                <p class="text-sm font-semibold text-stone-800">{{ $booking->user->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-stone-400 uppercase tracking-wide mb-1">Email</p>
                                <p class="text-sm text-stone-700">{{ $booking->user->email }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-stone-400 uppercase tracking-wide mb-1">No. WhatsApp</p>
                                <p class="text-sm text-stone-700">{{ $booking->user->phone ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Info Paket & Jadwal --}}
                    <div class="border border-stone-200 bg-white">
                        <div class="px-5 py-3 border-b border-stone-200 bg-stone-50">
                            <h2 class="text-xs font-bold text-stone-700 uppercase tracking-widest">Paket & Jadwal</h2>
                        </div>
                        <div class="p-5 grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-stone-400 uppercase tracking-wide mb-1">Paket</p>
                                <p class="text-sm font-semibold text-stone-800">{{ $booking->packageVariant->package->name }}</p>
                                <p class="text-xs text-stone-500">{{ $booking->packageVariant->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-stone-400 uppercase tracking-wide mb-1">Background</p>
                                <p class="text-sm text-stone-700">{{ $booking->background->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-stone-400 uppercase tracking-wide mb-1">Tanggal Sesi</p>
                                <p class="text-sm font-semibold text-stone-800">{{ $booking->booking_date->translatedFormat('l, d F Y') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-stone-400 uppercase tracking-wide mb-1">Waktu</p>
                                <p class="text-sm text-stone-700">
                                    {{ $booking->start_time->format('H:i') }} – {{ $booking->end_time->format('H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Daftar Add-ons --}}
                    <div class="border border-stone-200 bg-white">
                        <div class="px-5 py-3 border-b border-stone-200 bg-stone-50">
                            <h2 class="text-xs font-bold text-stone-700 uppercase tracking-widest">Layanan Tambahan</h2>
                        </div>

                        @if($booking->addons->isEmpty())
                            <p class="px-5 py-4 text-xs text-stone-400 italic">Tidak ada layanan tambahan.</p>
                        @else
                            <table class="w-full text-sm">
                                <thead class="border-b border-stone-100">
                                    <tr>
                                        <th class="text-left text-xs font-semibold text-stone-500 uppercase px-5 py-2">Layanan</th>
                                        <th class="text-center text-xs font-semibold text-stone-500 uppercase px-5 py-2">Qty</th>
                                        <th class="text-right text-xs font-semibold text-stone-500 uppercase px-5 py-2">Harga Satuan</th>
                                        <th class="text-right text-xs font-semibold text-stone-500 uppercase px-5 py-2">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-100">
                                    @foreach($booking->addons as $addon)
                                        <tr>
                                            <td class="px-5 py-3 text-stone-700">{{ $addon->name }}</td>
                                            <td class="px-5 py-3 text-center text-stone-600">{{ $addon->pivot->quantity }}</td>
                                            <td class="px-5 py-3 text-right text-stone-600">Rp {{ number_format($addon->pivot->price_at_purchase, 0, ',', '.') }}</td>
                                            <td class="px-5 py-3 text-right font-semibold text-stone-800">Rp {{ number_format($addon->pivot->quantity * $addon->pivot->price_at_purchase, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        {{-- ============================================ --}}
                        {{-- INLINE FORM UPSELL ADD-ON                   --}}
                        {{-- Hanya tampil jika status bukan Cancelled/Done --}}
                        {{-- ============================================ --}}
                        @if(!in_array($booking->status, [\App\Domains\Booking\Enums\BookingStatus::CANCELLED, \App\Domains\Booking\Enums\BookingStatus::DONE]))
                            <div class="border-t border-stone-200 bg-stone-50 px-5 py-4">
                                <p class="text-xs font-bold text-stone-600 uppercase tracking-widest mb-3">+ Tambahkan Layanan ke Tagihan</p>
                                <div class="flex items-center gap-3">

                                    {{-- Dropdown Addon --}}
                                    <div class="flex-1">
                                        <select
                                            x-data="choices({ placeholder: true, placeholderValue: '--- Pilih Layanan ---' })"
                                            x-modelable="value"
                                            x-model="state.upsell.addonId"
                                            @change="onUpsellAddonChange()">
                                            @foreach ($availableAddons as $addon)
                                                <option value="{{ $addon->id }}">
                                                    {{ $addon->name }} — Rp {{ number_format($addon->price, 0, ',', '.') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Input Qty --}}
                                    <div class="w-20">
                                        <input
                                            type="number"
                                            x-model.number="state.upsell.quantity"
                                            :disabled="state.upsell.addonId && !state.allAddons.find(a => a.id == state.upsell.addonId)?.has_quantity"
                                            min="1"
                                            class="w-full border border-stone-300 bg-white px-3 py-2.5 text-sm text-center text-stone-900 focus:outline-none focus:border-stone-500 disabled:bg-stone-200 disabled:text-stone-400">
                                    </div>

                                    {{-- Tombol Tambah --}}
                                    <button type="button"
                                        @click="submitUpsell()"
                                        :disabled="state.upsell.isLoading || !state.upsell.addonId"
                                        class="bg-stone-800 text-white text-xs font-bold px-4 py-2.5 hover:bg-stone-900 transition-colors disabled:opacity-50 disabled:pointer-events-none whitespace-nowrap">
                                        <span x-text="state.upsell.isLoading ? 'Menambahkan...' : '+ Tambah ke Tagihan'"></span>
                                    </button>

                                </div>
                            </div>
                        @endif

                    </div>

                    {{-- Link GDrive (jika sudah ada) --}}
                    @if($booking->gdrive_link)
                        <div class="border border-stone-200 bg-white px-5 py-4 flex items-center gap-3">
                            <i class="ri-drive-line text-xl text-sky-500"></i>
                            <div>
                                <p class="text-xs text-stone-400 uppercase tracking-wide mb-0.5">Link Hasil Foto (Google Drive)</p>
                                <a href="{{ $booking->gdrive_link }}" target="_blank"
                                   class="text-sm text-sky-600 hover:underline break-all">{{ $booking->gdrive_link }}</a>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- ============================================================ --}}
                {{-- KOLOM KANAN: Ringkasan Finansial & Tombol Aksi               --}}
                {{-- ============================================================ --}}
                <div class="space-y-4">

                    {{-- Ringkasan Pembayaran --}}
                    <div class="border border-stone-200 bg-white">
                        <div class="px-5 py-3 border-b border-stone-200 bg-stone-50">
                            <h2 class="text-xs font-bold text-stone-700 uppercase tracking-widest">Ringkasan Pembayaran</h2>
                        </div>
                        <div class="p-5 space-y-3">
                            @php
                                $totalPaid = $booking->payments->where('status', \App\Domains\Payment\Enums\PaymentStatus::SETTLEMENT)->sum('amount');
                                $remaining = $booking->total_price - $totalPaid;
                            @endphp
                            <div class="flex justify-between text-sm">
                                <span class="text-stone-500">Total Tagihan</span>
                                <span class="font-semibold text-stone-800">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-stone-500">Total Terbayar</span>
                                <span class="font-semibold text-emerald-700">Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
                            </div>
                            @if($remaining > 0)
                                <div class="flex justify-between text-sm border-t border-stone-100 pt-3">
                                    <span class="text-stone-500">Sisa Tagihan</span>
                                    <span class="font-bold text-red-600">Rp {{ number_format($remaining, 0, ',', '.') }}</span>
                                </div>
                            @else
                                <div class="flex justify-between text-sm border-t border-stone-100 pt-3">
                                    <span class="text-stone-500">Sisa Tagihan</span>
                                    <span class="font-bold text-emerald-600">Rp 0 (LUNAS)</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="space-y-2">

                        {{-- Tandai Lunas — hanya jika DP_PAID --}}
                        @if($booking->status === \App\Domains\Booking\Enums\BookingStatus::DP_PAID)
                            <button type="button" @click="settle()"
                                class="w-full bg-emerald-600 text-white text-sm font-bold py-3 hover:bg-emerald-700 transition-colors">
                                <i class="ri-checkbox-circle-line mr-1.5"></i> Tandai Lunas Penuh
                            </button>
                        @endif

                        {{-- Input GDrive — jika Lunas atau Done --}}
                        @if(in_array($booking->status, [\App\Domains\Booking\Enums\BookingStatus::SUCCESS, \App\Domains\Booking\Enums\BookingStatus::DONE]))
                            <div class="border border-stone-200 bg-white p-4 space-y-3">
                                <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wide">
                                    {{ $booking->gdrive_link ? 'Perbarui' : 'Input' }} Link GDrive
                                </label>
                                <input
                                    type="url"
                                    x-model="state.gdriveLink"
                                    value="{{ $booking->gdrive_link }}"
                                    placeholder="https://drive.google.com/..."
                                    class="w-full border border-stone-300 bg-white px-3 py-2.5 text-sm text-stone-900 placeholder-stone-400 focus:outline-none focus:border-stone-500 focus:ring-1 focus:ring-stone-500">
                                <button type="button" @click="submitGdrive()" :disabled="state.isGdriveLoading || !state.gdriveLink"
                                    class="w-full bg-sky-600 text-white text-sm font-bold py-2.5 hover:bg-sky-700 transition-colors disabled:opacity-50 disabled:pointer-events-none">
                                    <span x-text="state.isGdriveLoading ? 'Menyimpan...' : 'Simpan & Kirim Notif WA'"></span>
                                </button>
                            </div>
                        @endif

                        {{-- Riwayat Pembayaran (collapsible) --}}
                        <div x-data="{ open: false }" class="border border-stone-200 bg-white">
                            <button @click="open = !open" type="button"
                                class="w-full flex items-center justify-between px-4 py-3 text-xs font-semibold text-stone-600 uppercase tracking-wide hover:bg-stone-50">
                                <span>Riwayat Pembayaran</span>
                                <i class="ri-arrow-down-s-line transition-transform" :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="open" x-transition class="border-t border-stone-100 divide-y divide-stone-100">
                                @forelse($booking->payments as $payment)
                                    <div class="px-4 py-3 flex justify-between items-start">
                                        <div>
                                            <p class="text-xs font-semibold text-stone-700">{{ $payment->payment_purpose?->name ?? '-' }}</p>
                                            <p class="text-xs text-stone-400 mono">{{ $payment->order_id }}</p>
                                            <p class="text-xs text-stone-400">{{ $payment->pay_date?->format('d M Y, H:i') ?? '-' }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs font-bold text-stone-800">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                                            <span class="text-xs px-1.5 py-0.5
                                                @if($payment->status === \App\Domains\Payment\Enums\PaymentStatus::SETTLEMENT) text-emerald-700 bg-emerald-50
                                                @else text-stone-500 bg-stone-100 @endif">
                                                {{ $payment->status->label() }}
                                            </span>
                                        </div>
                                    </div>
                                @empty
                                    <p class="px-4 py-3 text-xs text-stone-400 italic">Belum ada riwayat pembayaran.</p>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </x-slot:content>

</x-layouts.backdoor>
```

---

## 10. Catatan Implementasi

### A. Hal yang Perlu Diperhatikan

1. **`source` column di `bookings`:** Spec ini mengasumsikan ada kolom `source` (tipe `string`, nullable) di tabel `bookings` untuk membedakan booking dari frontdoor (`midtrans`) vs. backdoor (`manual`). Jika belum ada, tambahkan migration baru.

2. **`PaymentScheme::FULL`:** Semua booking manual menggunakan skema `FULL` (lunas). Pastikan enum `PaymentScheme` memiliki case `FULL`.

3. **API Time Slots (`route('api.timeslots')`):** Method `loadTimeSlots()` di `Create.js` memanggil endpoint API untuk mendapatkan slot waktu yang tersedia. Pastikan endpoint ini sudah ada dan menerima parameter `variant_id` dan `booking_date`. Jika belum ada, endpoint ini harus dibuat terpisah.

4. **Choices.js Reactive Dropdown di Repeater:** Karena Choices.js membungkus elemen `<select>` native, integrasi dengan `x-for` Alpine membutuhkan perhatian khusus. Setiap kali baris baru ditambahkan ke `state.addons`, Choices.js harus di-inisialisasi ulang pada `<select>` tersebut. Solusi terbaik adalah menggunakan directive `x-init` pada setiap elemen `<select>` di dalam `x-for`, bukan meletakkan `x-data="choices()"` (yang bisa berkonflik). Alternatif: gunakan wrapper komponen Blade `<x-shared.choices-select />`.

5. **Fonnte Service:** Baris komentar `// TODO: FonnteService::sendGdriveLink($booking)` di `ManageBookingController@updateGdrive` harus diimplementasikan menggunakan Service Layer Fonnte yang sudah ada di project.

### B. Ringkasan Alur Kritis

```
[TAMBAH BOOKING MANUAL]
Form Submit → StoreManualBookingRequest (validasi)
           → toDto() → ManualBookingData
           → CreateManualBookingService::execute()
               ├── Booking::create()
               ├── $booking->addons()->attach() (pivot)
               ├── Payment::create() [status=SETTLEMENT jika LUNAS | status=PENDING jika PENDING]
               └── return $booking
           → redirect ke show/{id}

[TANDAI LUNAS - dari DP_PAID]
Klik Tombol → axios.patch /settle
           → ManageBookingController::settle()
           → SettleBookingService::execute()
               ├── Hitung sisa = total_price - SUM(payments.amount where Settlement)
               ├── Payment::create() [purpose=PELUNASAN, amount=sisa, type=cash]
               └── $booking->update(['status' => SUCCESS])
           → response JSON success

[UPSELL ADDON - di Detail]
Klik "+ Tambah ke Tagihan" → axios.post /addons
           → ManageBookingController::upsellAddon()
               ├── $booking->addons()->attach() atau updateExistingPivot()
               └── $booking->increment('total_price', subtotal)
           → response JSON success → window.location.reload()
```
