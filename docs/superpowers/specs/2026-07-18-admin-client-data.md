# Spec: Admin Backdoor — Data Klien (Client Data)

**Tanggal:** 2026-07-18
**Branch:** `feat/admin-client-data`
**Scope:** Backend (Controller, DTO, Routes) + Frontend (Alpine.js Modules, Blade Views)
**Stack:** Laravel 13 · PHP 8.3 · Alpine.js · Tailwind CSS v4 · Axios · Spatie Permission
**Design System:** ThoughtStream (flat, no rounded corners, no shadows, stone palette, border separators)

> ⚠️ **Perbedaan dengan Manajemen User di Pengaturan Sistem:**
> Fitur "Data Klien" ini adalah fitur *operasional* yang difokuskan untuk melihat daftar pelanggan studio beserta riwayat booking mereka. Ini **BERBEDA** dengan "Manajemen User" di menu Pengaturan Sistem yang berfokus pada pengelolaan akun dan role (RBAC/Spatie Permission).

---

## Daftar Isi

1. [Aturan Bisnis & Logika Domain](#1-aturan-bisnis--logika-domain)
2. [Struktur Direktori File Baru](#2-struktur-direktori-file-baru)
3. [Backend — Routes](#3-backend--routes)
4. [Backend — DTOs](#4-backend--dtos)
5. [Backend — Controller](#5-backend--controller)
6. [Frontend — JS Modules (Alpine.js)](#6-frontend--js-modules-alpinejs)
7. [Frontend — Blade Views](#7-frontend--blade-views)
8. [Catatan Implementasi](#8-catatan-implementasi)

---

## 1. Aturan Bisnis & Logika Domain

### A. Definisi "Klien"

Klien adalah `User` yang **memiliki role `user`** (Spatie Permission). Akun internal (superadmin, admin, owner) **dieksklusi** dari daftar ini.

Query filter:

```php
User::role('user')
```

### B. Widget Statistik — Formula (Halaman Index)

Dua card statistik yang ditampilkan di atas tabel:

```
Total Klien Terdaftar = COUNT(users)
    WHERE EXISTS (model_has_roles WHERE role = 'user')

Klien Baru Bulan Ini = COUNT(users)
    WHERE EXISTS (model_has_roles WHERE role = 'user')
    AND MONTH(users.created_at) = MONTH(CURRENT_DATE)
    AND YEAR(users.created_at) = YEAR(CURRENT_DATE)
```

Kedua nilai ini dikembalikan bersamaan dari method `index()` controller dan di-pass ke view sebagai variabel PHP biasa (bukan JSON), karena bersifat statis per-request.

### C. Kolom Tabel Index

| Kolom | Sumber Data | Keterangan |
|---|---|---|
| No | — | Nomor urut row (index + offset pagination) |
| Nama Klien | `users.name` | Teks tebal |
| Email | `users.email` | — |
| Nomor HP | `users.phone` | Nullable, tampilkan `-` jika null |
| Tanggal Bergabung | `users.created_at` | Format: `d M Y` (contoh: `01 Jan 2026`) |
| Total Booking | `bookings_count` | Via `withCount('bookings')`, hanya status DP_PAID, Lunas (SUCCESS), Selesai (DONE) |
| Aksi | — | Tombol 3 titik vertikal |

### D. Formula Total Booking per Klien

Menggunakan `withCount` dengan constraint untuk efisiensi:

```php
User::role('user')
    ->withCount(['bookings as bookings_count' => function ($q) {
        $q->whereIn('status', [
            BookingStatus::DP_PAID,
            BookingStatus::SUCCESS,
            BookingStatus::DONE,
        ]);
    }])
```

### E. Pencarian (Live Search — Halaman Index)

Search box mencari secara real-time dengan debounce 400ms terhadap:
- Nama klien (`users.name`)
- Email (`users.email`)
- Nomor HP (`users.phone`)

### F. Aksi Baris Tabel (Menu 3 Titik)

| Aksi | Efek |
|---|---|
| **Lihat Detail** | Redirect ke `/backdoor/client-data/{uuid}` (halaman show) |

### G. Halaman Show — Profile Card

Bagian atas halaman menampilkan card profil klien yang berisi:
- Avatar inisial (dari nama, `rounded-full` sesuai design system)
- Nama lengkap
- Email
- Nomor HP (atau `-` jika null)
- Tanggal bergabung (format panjang, contoh: `Jumat, 01 Januari 2026`)
- Total booking valid (badge angka)

### H. Halaman Show — Tabel Riwayat Booking

Tabel di bawah profile card menampilkan **semua** riwayat booking dari klien tersebut, dengan:
- **Search**: mencari `booking_code`, nama paket
- **Pagination**: server-side via `useDatatable.js`
- **Tanpa filter** (tidak perlu)
- **Kolom**: No | Kode Booking | Tanggal Sesi | Paket Foto | Status | Total Bayar | Aksi

| Kolom Riwayat Booking | Sumber | Keterangan |
|---|---|---|
| No | — | Nomor urut |
| Kode Booking | `bookings.booking_code` | Font mono, uppercase |
| Tanggal Sesi | `bookings.booking_date` + `start_time`–`end_time` | Format: `d M Y · HH:mm–HH:mm` |
| Paket Foto | `packageVariant.package.name` + `packageVariant.name` | 2 baris |
| Status | `bookings.status` | Badge warna sesuai status (Menunggu=warning, DP=stone, Lunas=lime, Batal=danger, Selesai=lime) |
| Total Bayar | `bookings.total_price` | Format Rupiah |
| Aksi | — | Tombol "Lihat Detail" mengarah ke `/backdoor/booking-management/{booking_code}` |

---

## 2. Struktur Direktori File Baru

```
app/Domains/User/
├── DTOs/
│   ├── ClientDataIndexData.php         ← BARU (DTO untuk row tabel index)
│   └── ClientDataShowData.php          ← BARU (DTO untuk profile card di halaman show)
└── Http/
    └── Controllers/
        └── Backdoor/                   ← BARU (folder baru, sejajar dengan Frontdoor)
            └── ClientDataController.php ← BARU

app/Domains/Booking/DTOs/
└── ClientBookingHistoryData.php        ← BARU (DTO untuk row tabel riwayat booking di show)

routes/backdoor/
└── client-data.php                     ← BARU (sudah di-require di backdoor.php)

resources/js/features/backdoor/client-data/
├── index/
│   └── Index.js                        ← BARU (Alpine komponen untuk halaman index)
└── show/
    └── Show.js                         ← BARU (Alpine komponen untuk halaman show)

resources/views/backdoor/client-data/
├── index.blade.php                     ← BARU
└── show.blade.php                      ← BARU
```

---

## 3. Backend — Routes

**File:** `routes/backdoor/client-data.php`

```php
<?php

use App\Domains\User\Http\Controllers\Backdoor\ClientDataController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
  ->prefix('backdoor/client-data')
  ->name('backdoor.client-data.')
  ->group(function () {

    // halaman daftar klien
    Route::get('/', [ClientDataController::class, 'index'])
      ->name('index');

    // json data untuk tabel index (useDatatable)
    Route::get('/data', [ClientDataController::class, 'data'])
      ->name('data');

    // halaman detail klien
    Route::get('/{user:uuid}', [ClientDataController::class, 'show'])
      ->name('show');

    // json data untuk tabel riwayat booking di halaman show
    Route::get('/{user:uuid}/bookings', [ClientDataController::class, 'bookings'])
      ->name('bookings');
  });
```

> **Catatan:** `{user:uuid}` menggunakan Route Model Binding dengan key `uuid`. UUID lebih aman dari `id` karena tidak sequential / tidak bisa ditebak.

---

## 4. Backend — DTOs

### 4A. `ClientDataIndexData.php`

**File:** `app/Domains/User/DTOs/ClientDataIndexData.php`

DTO ini merepresentasikan satu baris data di tabel index.

```php
<?php

namespace App\Domains\User\DTOs;

use App\Domains\User\Models\User;
use App\Support\Formatter;
use Spatie\LaravelData\Data;

class ClientDataIndexData extends Data
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $phone,
        public readonly string $joined_at,      // "01 Jan 2026"
        public readonly int $bookings_count,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            uuid: $user->uuid,
            name: $user->name,
            email: $user->email,
            phone: $user->phone,
            joined_at: $user->created_at->format('d M Y'),
            bookings_count: $user->bookings_count ?? 0,
        );
    }
}
```

### 4B. `ClientDataShowData.php`

**File:** `app/Domains/User/DTOs/ClientDataShowData.php`

DTO ini dipakai untuk profile card di halaman show. Di-pass dari controller ke Blade sebagai variabel PHP biasa (bukan JSON).

```php
<?php

namespace App\Domains\User\DTOs;

use App\Domains\User\Models\User;
use App\Domains\Booking\Enums\BookingStatus;

class ClientDataShowData
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $phone,
        public readonly string $joined_at,          // "Jumat, 01 Januari 2026"
        public readonly int $total_valid_bookings,  // count status DP_PAID, SUCCESS, DONE
    ) {}

    public static function fromModel(User $user): self
    {
        $totalValid = $user->bookings()
            ->whereIn('status', [
                BookingStatus::DP_PAID,
                BookingStatus::SUCCESS,
                BookingStatus::DONE,
            ])
            ->count();

        return new self(
            uuid: $user->uuid,
            name: $user->name,
            email: $user->email,
            phone: $user->phone,
            joined_at: $user->created_at->locale('id')->translatedFormat('l, d F Y'),
            total_valid_bookings: $totalValid,
        );
    }
}
```

### 4C. `ClientBookingHistoryData.php`

**File:** `app/Domains/Booking/DTOs/ClientBookingHistoryData.php`

DTO untuk satu baris di tabel riwayat booking pada halaman show.

```php
<?php

namespace App\Domains\Booking\DTOs;

use App\Domains\Booking\Models\Booking;
use App\Support\Formatter;
use Spatie\LaravelData\Data;

class ClientBookingHistoryData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $booking_code,
        public readonly string $formatted_date,   // "18 Jul 2026 · 13:00–14:00"
        public readonly string $package_name,     // nama paket
        public readonly string $variant_name,     // nama varian
        public readonly string $status,           // "Lunas", "Selesai", dll
        public readonly string $formatted_price,  // "Rp 350.000"
    ) {}

    public static function fromModel(Booking $booking): self
    {
        return new self(
            id: $booking->id,
            booking_code: $booking->booking_code,
            formatted_date: $booking->booking_date->format('d M Y')
                . ' · '
                . Formatter::timeRange($booking->start_time, $booking->end_time),
            package_name: $booking->packageVariant->package->name,
            variant_name: $booking->packageVariant->name,
            status: $booking->status->value,
            formatted_price: Formatter::rupiah($booking->total_price),
        );
    }
}
```

---

## 5. Backend — Controller

**File:** `app/Domains/User/Http/Controllers/Backdoor/ClientDataController.php`

```php
<?php

namespace App\Domains\User\Http\Controllers\Backdoor;

use App\Domains\Booking\DTOs\ClientBookingHistoryData;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\User\DTOs\ClientDataIndexData;
use App\Domains\User\DTOs\ClientDataShowData;
use App\Domains\User\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientDataController extends Controller
{
    /**
     * Halaman daftar klien (index).
     * Statistik card dihitung di sini dan di-pass ke view.
     */
    public function index(): View
    {
        $totalClients = User::role('user')->count();

        $newClientsThisMonth = User::role('user')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('backdoor.client-data.index', [
            'totalClients'       => $totalClients,
            'newClientsThisMonth' => $newClientsThisMonth,
        ]);
    }

    /**
     * JSON endpoint untuk useDatatable — tabel index klien.
     */
    public function data(Request $request): JsonResponse
    {
        $search = $request->input('search', '');
        $limit  = $request->integer('limit', 10);
        $page   = $request->integer('page', 1);

        $query = User::role('user')
            ->withCount(['bookings as bookings_count' => function ($q) {
                $q->whereIn('status', [
                    BookingStatus::DP_PAID,
                    BookingStatus::SUCCESS,
                    BookingStatus::DONE,
                ]);
            }])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'ilike', "%{$search}%")
                          ->orWhere('email', 'ilike', "%{$search}%")
                          ->orWhere('phone', 'ilike', "%{$search}%");
                });
            })
            ->latest('created_at');

        $paginated = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'data'         => ClientDataIndexData::collect($paginated->items()),
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'total'        => $paginated->total(),
            'per_page'     => $paginated->perPage(),
        ]);
    }

    /**
     * Halaman detail klien (show).
     * Profile card di-pass sebagai Blade variable.
     */
    public function show(User $user): View
    {
        return view('backdoor.client-data.show', [
            'client' => ClientDataShowData::fromModel($user),
        ]);
    }

    /**
     * JSON endpoint untuk useDatatable — tabel riwayat booking di halaman show.
     */
    public function bookings(Request $request, User $user): JsonResponse
    {
        $search = $request->input('search', '');
        $limit  = $request->integer('limit', 10);
        $page   = $request->integer('page', 1);

        $query = $user->bookings()
            ->with(['packageVariant.package'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('booking_code', 'ilike', "%{$search}%")
                          ->orWhereHas('packageVariant.package', function ($pkg) use ($search) {
                              $pkg->where('name', 'ilike', "%{$search}%");
                          });
                });
            })
            ->latest('booking_date');

        $paginated = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'data'         => ClientBookingHistoryData::collect($paginated->items()),
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'total'        => $paginated->total(),
            'per_page'     => $paginated->perPage(),
        ]);
    }
}
```

> **Catatan Postgres:** Penggunaan `ilike` (case-insensitive LIKE) adalah native Postgres, lebih efisien daripada `LOWER(column) LIKE LOWER(?)`.

---

## 6. Frontend — JS Modules (Alpine.js)

### 6A. `Index.js` — Halaman Daftar Klien

**File:** `resources/js/features/backdoor/client-data/index/Index.js`

```javascript
/**
 * Logika halaman Daftar Data Klien.
 *
 * File: resources/js/features/backdoor/client-data/index/Index.js
 * Penggunaan di Blade: <div x-data="Index">
 *
 * @param {import('alpinejs').Alpine} Alpine
 * @returns {object}
 */
import useDatatable from "@/lib/useDatatable";
import route from "@/lib/route";

export default function Index(Alpine) {
  const table = useDatatable(Alpine, route("backdoor.client-data.data"), {
    debounceMs: 400,
  });

  // Muat data pertama kali
  table.fetch();

  return {
    table,
  };
}
```

### 6B. `Show.js` — Halaman Detail Klien

**File:** `resources/js/features/backdoor/client-data/show/Show.js`

Profile card sudah di-render server-side (Blade). JS ini hanya mengelola tabel riwayat booking.

```javascript
/**
 * Logika halaman Detail Klien.
 *
 * File: resources/js/features/backdoor/client-data/show/Show.js
 * Penggunaan di Blade: <div x-data="Show">
 * UUID klien dikirim via data-user-uuid attribute pada root element.
 *
 * @param {import('alpinejs').Alpine} Alpine
 * @returns {object}
 */
import useDatatable from "@/lib/useDatatable";
import route from "@/lib/route";

export default function Show(Alpine) {
  // UUID dibaca dari data attribute yang disisipkan oleh Blade
  const getUserUuid = () =>
    document.getElementById("show-root")?.dataset?.userUuid ?? "";

  const table = useDatatable(
    Alpine,
    () => route("backdoor.client-data.bookings", getUserUuid()),
    { debounceMs: 400 }
  );

  table.fetch();

  return {
    table,
  };
}
```

---

## 7. Frontend — Blade Views

### 7A. `index.blade.php`

**File:** `resources/views/backdoor/client-data/index.blade.php`

```blade
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('backdoor.dashboard')],
    ['label' => 'Data Klien', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Data Klien"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/client-data/index/Index"
>

  <x-slot:content>
    <div
      x-data="Index"
      x-cloak
      class="w-full space-y-6"
    >

      {{-- Page Header --}}
      <x-backdoor.shared.page-header title="Data Klien" />

      {{-- Stats Cards --}}
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <x-backdoor.shared.stats-card
          label="Total Klien Terdaftar"
          value="{{ $totalClients }}"
          suffix="Klien"
        />
        <x-backdoor.shared.stats-card
          label="Klien Baru Bulan Ini"
          value="{{ $newClientsThisMonth }}"
          suffix="Klien"
        />
      </div>
      {{-- Stats Cards End --}}

      {{-- Table Card --}}
      <div class="relative overflow-visible border border-stone-200 bg-stone-50 p-6">

        {{-- Table Header (search only, no right-side button) --}}
        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari nama, email, atau nomor HP..." />
          </x-slot:left>
        </x-backdoor.table.header>

        {{-- Table --}}
        <x-backdoor.table.container headers="No,Nama Klien,Email,Nomor HP,Tgl. Bergabung,Total Booking,Aksi">
          <template x-for="(client, index) in table.state.data" :key="client.uuid">
            <tr
              class="border-b border-stone-200 transition hover:bg-stone-100"
              x-show="!table.state.isLoading"
              x-cloak
            >

              <x-backdoor.table.cell
                class="text-stone-600"
                x-text="index + 1 + ((table.state.pagination.current_page - 1) * table.state.pagination.per_page)"
              />

              <x-backdoor.table.cell
                class="font-semibold text-stone-900"
                x-text="client.name"
              />

              <x-backdoor.table.cell
                class="text-stone-600"
                x-text="client.email"
              />

              <x-backdoor.table.cell
                class="text-stone-600"
                x-text="client.phone ?? '-'"
              />

              <x-backdoor.table.cell
                class="text-sm text-stone-500"
                x-text="client.joined_at"
              />

              <x-backdoor.table.cell>
                <span class="font-semibold text-stone-800" x-text="client.bookings_count"></span>
                <span class="text-xs text-stone-500"> Sesi</span>
              </x-backdoor.table.cell>

              <x-backdoor.table.actions>
                <x-backdoor.table.action-item
                  x-on:click="closeDropdown()"
                  x-bind:href="`{{ route('backdoor.client-data.show', ':uuid') }}`
                    .replace(':uuid', client.uuid)"
                  color="text-blue-600"
                  text="Lihat Detail"
                />
              </x-backdoor.table.actions>

            </tr>
          </template>
        </x-backdoor.table.container>

        <x-backdoor.table.pagination />

      </div>
      {{-- Table Card End --}}

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
```

### 7B. `show.blade.php`

**File:** `resources/views/backdoor/client-data/show.blade.php`

```blade
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('backdoor.dashboard')],
    ['label' => 'Data Klien', 'url' => route('backdoor.client-data.index')],
    ['label' => $client->name, 'url' => ''],
  ];

  // Ambil inisial dari nama untuk avatar
  $initials = collect(explode(' ', $client->name))
    ->map(fn($w) => strtoupper(substr($w, 0, 1)))
    ->take(2)
    ->implode('');
@endphp

<x-layouts.backdoor.index
  title="Detail Klien — {{ $client->name }}"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/client-data/show/Show"
>

  <x-slot:content>
    <div
      id="show-root"
      data-user-uuid="{{ $client->uuid }}"
      x-data="Show"
      x-cloak
      class="w-full space-y-6"
    >

      {{-- Page Header --}}
      <x-backdoor.shared.page-header title="Detail Klien" />

      {{-- Profile Card --}}
      <div class="border border-stone-200 bg-stone-50 p-6">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-start">

          {{-- Avatar Inisial --}}
          <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-stone-700 text-xl font-bold text-stone-50">
            {{ $initials }}
          </div>

          {{-- Info Klien --}}
          <div class="flex flex-1 flex-col gap-4">
            <div>
              <h2 class="text-xl font-bold text-stone-900">{{ $client->name }}</h2>
              <p class="mt-0.5 text-sm text-stone-500">Bergabung sejak {{ $client->joined_at }}</p>
            </div>

            <div class="grid grid-cols-1 gap-4 border-t border-stone-200 pt-4 sm:grid-cols-3">
              {{-- Email --}}
              <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Email</p>
                <p class="mt-1 text-sm font-medium text-stone-800">{{ $client->email }}</p>
              </div>
              {{-- Nomor HP --}}
              <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Nomor HP</p>
                <p class="mt-1 text-sm font-medium text-stone-800">{{ $client->phone ?? '-' }}</p>
              </div>
              {{-- Total Booking --}}
              <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Total Booking Valid</p>
                <p class="mt-1 text-sm font-bold text-stone-900">{{ $client->total_valid_bookings }} <span class="font-normal text-stone-500">Sesi</span></p>
              </div>
            </div>
          </div>

        </div>
      </div>
      {{-- Profile Card End --}}

      {{-- Booking History Table --}}
      <div class="relative overflow-visible border border-stone-200 bg-stone-50 p-6">

        <div class="mb-4">
          <h3 class="text-base font-semibold text-stone-800">Riwayat Booking</h3>
          <p class="mt-0.5 text-sm text-stone-500">Semua data pemesanan yang pernah dilakukan oleh klien ini.</p>
        </div>

        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari kode booking atau nama paket..." />
          </x-slot:left>
        </x-backdoor.table.header>

        <x-backdoor.table.container headers="No,Kode Booking,Tanggal Sesi,Paket Foto,Status,Total Bayar,Aksi">
          <template x-for="(booking, index) in table.state.data" :key="booking.id">
            <tr
              class="border-b border-stone-200 transition hover:bg-stone-100"
              x-show="!table.state.isLoading"
              x-cloak
            >

              <x-backdoor.table.cell
                class="text-stone-600"
                x-text="index + 1 + ((table.state.pagination.current_page - 1) * table.state.pagination.per_page)"
              />

              <x-backdoor.table.cell
                class="font-mono text-xs font-semibold uppercase text-stone-800"
                x-text="booking.booking_code"
              />

              <x-backdoor.table.cell
                class="text-sm text-stone-700"
                x-text="booking.formatted_date"
              />

              <x-backdoor.table.cell class="text-sm">
                <p class="font-medium text-stone-900" x-text="booking.package_name"></p>
                <p class="mt-0.5 text-xs text-stone-500" x-text="booking.variant_name"></p>
              </x-backdoor.table.cell>

              <x-backdoor.table.cell>
                {{-- Badge status booking --}}
                <x-shared.badge
                  alpine="booking.status === 'Lunas' || booking.status === 'Selesai'"
                  variant="lime"
                  x-text="booking.status"
                />
                <x-shared.badge
                  alpine="booking.status === 'DP Terbayar'"
                  variant="secondary"
                  x-text="booking.status"
                />
                <x-shared.badge
                  alpine="booking.status === 'Menunggu'"
                  variant="warning"
                  x-text="booking.status"
                />
                <x-shared.badge
                  alpine="booking.status === 'Batal'"
                  variant="danger"
                  x-text="booking.status"
                />
              </x-backdoor.table.cell>

              <x-backdoor.table.cell
                class="font-semibold text-stone-800"
                x-text="booking.formatted_price"
              />

              <x-backdoor.table.actions>
                <x-backdoor.table.action-item
                  x-on:click="closeDropdown()"
                  x-bind:href="`{{ route('backdoor.booking-management.show', ':booking_code') }}`
                    .replace(':booking_code', booking.booking_code)"
                  color="text-blue-600"
                  text="Lihat Detail Booking"
                />
              </x-backdoor.table.actions>

            </tr>
          </template>
        </x-backdoor.table.container>

        <x-backdoor.table.pagination />

      </div>
      {{-- Booking History Table End --}}

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
```

---

## 8. Catatan Implementasi

### A. Route Model Binding — `{user:uuid}`

Pastikan di model `User.php` UUID digunakan sebagai key dengan route model binding. Karena `User` menggunakan `HasUuids`, tambahkan `getRouteKeyName()`:

```php
// Di User.php
public function getRouteKeyName(): string
{
    return 'uuid';
}
```

Atau lebih simpel, cukup pastikan route menggunakan `{user:uuid}` dan Laravel akan otomatis mencari berdasarkan kolom `uuid`.

### B. Sidebar Link

Tambahkan link "Data Klien" di sidebar (`sidebar-links.blade.php`). Berdasarkan desain Figma, menu ini berada di level pertama (bukan sub-item dari menu lain):

```blade
{{-- data klien start --}}
<x-layouts.backdoor.components.sidebar-link
  :href="route('backdoor.client-data.index')"
  :active="request()->routeIs('backdoor.client-data.*')"
  title='Data Klien'
/>
{{-- data klien end --}}
```

### C. `ilike` vs `LIKE` di Postgres

Seluruh query `LIKE` menggunakan `ilike` (case-insensitive, native Postgres). Ini lebih efisien dari `LOWER(col) LIKE LOWER(?)` karena Postgres dapat menggunakan `pg_trgm` index jika tersedia.

### D. N+1 Query — `withCount`

Penggunaan `withCount` dengan constraint di query tabel index memastikan tidak ada N+1. Data `bookings_count` sudah tersedia langsung di model instance tanpa perlu query tambahan per-row.

### E. Search di Halaman Show — orWhereHas

`orWhereHas('packageVariant.package', ...)` adalah cara yang benar untuk search pada relasi nested. Eloquent akan melakukan subquery `EXISTS` yang efisien.

### F. Debounce 400ms

Sesuai spesifikasi di `docs/admin-operations/client-data.md`, debounce untuk search input adalah **400ms**.
