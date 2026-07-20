# Spec: Admin Backdoor — Activity Logs (Jejak Audit)

**Tanggal:** 2026-07-19
**Branch:** `feat/admin-activity-logs`
**Scope:** Model Konfigurasi (LogsActivity) + Backend (Controller, DTO, Routes) + Frontend (Alpine.js Module, Blade View)
**Stack:** Laravel 13 · PHP 8.3 · Spatie Laravel Activitylog v4 · Alpine.js · Tailwind CSS v4
**Design System:** ThoughtStream (flat, no rounded corners, no shadows, stone palette, border separators)

---

## Daftar Isi

1. [Aturan Bisnis & Logika Domain](#1-aturan-bisnis--logika-domain)
2. [Struktur Direktori](#2-struktur-direktori)
3. [Konfigurasi Model — LogsActivity](#3-konfigurasi-model--logsactivity)
4. [Backend — Routes](#4-backend--routes)
5. [Backend — DTO](#5-backend--dto)
6. [Backend — Controller](#6-backend--controller)
7. [Frontend — JS Module (Alpine.js)](#7-frontend--js-module-alpinejs)
8. [Frontend — Blade View](#8-frontend--blade-view)
9. [Catatan Modifikasi File Existing](#9-catatan-modifikasi-file-existing)

---

## 1. Aturan Bisnis & Logika Domain

### A. Sumber Data

Tabel `activity_log` (dibuat oleh Spatie) dengan struktur relevan:

```
id | log_name | description | subject_type | subject_id | causer_type | causer_id | properties (JSON) | created_at
```

Relasi:
- `causer` → `User` (pelaku aksi, bisa null jika sistem)
- `subject` → Model apapun yang melakukan perubahan (Booking, Package, dll)
- `properties` → JSON berisi `attributes` (data baru) dan `old` (data lama)

### B. Tabel — Kolom

| No | Kolom | Sumber | Format |
|---|---|---|---|
| 1 | No | — | Nomor urut dengan offset pagination |
| 2 | Waktu Sesi | `activity_log.created_at` | Format `d M Y, H:i` (contoh: `19 Jul 2026, 18:05`) |
| 3 | Pelaku | `causer` relasi → `users.name` | Nama user. Jika `causer_id` null → tampilkan `"Sistem"` |
| 4 | Modul & ID | `subject_type` + `subject_id` | Potong namespace → nama class saja + ID (contoh: `Booking (ID: 5)`) |
| 5 | Aktivitas | `description` | Teks apa adanya + badge berwarna (hijau=created, kuning=updated, merah=deleted) |
| 6 | Aksi | `properties` (JSON) | Tombol "Lihat Detail" → memunculkan modal |

### C. Search

Live search dengan debounce 400ms terhadap:
- Deskripsi aktivitas (`activity_log.description`)
- Nama pelaku (`users.name` via whereHasMorph)

### D. Modal Detail

Saat tombol "Lihat Detail" diklik, modal muncul menampilkan raw JSON `properties` dalam tag `<pre>` yang diformat rapi (JSON.stringify dengan indentasi 2 spasi).

### E. Logika Badge Warna Aktivitas

| Description | Warna Badge |
|---|---|
| `created` | Hijau (`text-lime-700 bg-lime-100`) |
| `updated` | Kuning (`text-yellow-700 bg-yellow-100`) |
| `deleted` | Merah (`text-red-700 bg-red-100`) |
| Lainnya | Abu-abu (`text-stone-600 bg-stone-200`) |

---

## 2. Struktur Direktori

```
app/Domains/                                ← MODIFIKASI LogsActivity di model
├── Booking/Models/Booking.php
├── Payment/Models/Payment.php
├── Review/Models/Review.php
├── User/Models/User.php                    ← sudah ada getActivitylogOptions
└── MasterData/Models/
    ├── Addon.php
    ├── Background.php
    ├── Category.php
    ├── Feature.php
    ├── Package.php
    ├── PackageVariant.php
    └── Schedule.php

app/Domains/SystemSettings/                 ← BARU (domain baru)
└── Http/Controllers/Backdoor/
    └── ActivityLogController.php           ← BARU

app/Domains/SystemSettings/DTOs/            ← BARU
└── ActivityLogRowData.php                  ← BARU

routes/backdoor/system-settings/
└── activity-logs.php                       ← ISI (sudah ada, kosong)

resources/js/features/backdoor/system-settings/
└── ActivityLogs.js                         ← BARU

resources/views/backdoor/system-settings/activity-logs/
└── index.blade.php                         ← BARU
```

---

## 3. Konfigurasi Model — LogsActivity

> **Catatan:** `User` model sudah memiliki `getActivitylogOptions()`. Cukup pastikan `use LogsActivity` ada di trait list. Untuk model lain, tambahkan trait + method berikut.

### 3A. `Booking.php`

```php
// Tambahkan import
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

#[Guarded(['id'])]
class Booking extends Model
{
    use LogsActivity; // ← TAMBAHKAN ke trait list yang sudah ada

    // ... existing code tidak diubah ...

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('booking')
            ->setDescriptionForEvent(fn(string $event) => $event);
    }
}
```

### 3B. `Payment.php`

```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

#[Guarded(['id'])]
class Payment extends Model
{
    use LogsActivity; // ← TAMBAHKAN

    // ... existing code ...

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('payment')
            ->setDescriptionForEvent(fn(string $event) => $event);
    }
}
```

### 3C. `Review.php`

```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

#[Guarded(['id'])]
#[UseFactory(ReviewFactory::class)]
class Review extends Model
{
    use HasFactory, LogsActivity; // ← TAMBAHKAN LogsActivity

    // ... existing code ...

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('review')
            ->setDescriptionForEvent(fn(string $event) => $event);
    }
}
```

### 3D. `User.php`

Sudah punya `getActivitylogOptions()`. Pastikan trait `LogsActivity` ada:

```php
use HasFactory, HasRoles, Notifiable, CanResetPassword, HasUuids, LogsActivity;
// ↑ Pastikan LogsActivity ada di sini
```

Tambahkan import jika belum ada:
```php
use Spatie\Activitylog\Traits\LogsActivity;
```

### 3E. `Addon.php`

```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

#[Fillable('name', 'price', 'description', 'has_quantity', 'is_active')]
class Addon extends Model
{
    use LogsActivity; // ← TAMBAHKAN

    // ... existing code ...

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('master-data')
            ->setDescriptionForEvent(fn(string $event) => $event);
    }
}
```

### 3F. `Background.php`

```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

#[Fillable('name', 'description', 'is_active')]
class Background extends Model implements HasMedia
{
    use InteractsWithMedia, LogsActivity; // ← TAMBAHKAN

    // ... existing code ...

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('master-data')
            ->setDescriptionForEvent(fn(string $event) => $event);
    }
}
```

### 3G. `Category.php`

```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

#[Fillable('category_code', 'name', 'slug', 'is_active')]
class Category extends Model
{
    use HasSlug, LogsActivity; // ← TAMBAHKAN

    // ... existing code ...

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('master-data')
            ->setDescriptionForEvent(fn(string $event) => $event);
    }
}
```

### 3H. `Feature.php`

```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

#[Fillable('description', 'featureable_type', 'featureable_id')]
class Feature extends Model
{
    use LogsActivity; // ← TAMBAHKAN

    // ... existing code ...

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('master-data')
            ->setDescriptionForEvent(fn(string $event) => $event);
    }
}
```

### 3I. `Package.php`

```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

#[Fillable('category_id', 'name', 'description', 'slug', 'is_active')]
class Package extends Model implements HasMedia
{
    use HasSlug, InteractsWithMedia, LogsActivity; // ← TAMBAHKAN

    // ... existing code ...

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('master-data')
            ->setDescriptionForEvent(fn(string $event) => $event);
    }
}
```

### 3J. `PackageVariant.php`

```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

#[Fillable('package_id', 'name', 'price', 'duration', 'is_whatsapp_only', 'is_active')]
class PackageVariant extends Model
{
    use LogsActivity; // ← TAMBAHKAN

    // ... existing code ...

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('master-data')
            ->setDescriptionForEvent(fn(string $event) => $event);
    }
}
```

### 3K. `Schedule.php`

```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

#[Fillable('day', 'start_time', 'end_time', 'is_active')]
class Schedule extends Model
{
    use LogsActivity; // ← TAMBAHKAN

    // ... existing code ...

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('master-data')
            ->setDescriptionForEvent(fn(string $event) => $event);
    }
}
```

---

## 4. Backend — Routes

**File:** `routes/backdoor/system-settings/activity-logs.php`

```php
<?php

use App\Domains\SystemSettings\Http\Controllers\Backdoor\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::prefix('backdoor/activity-logs')
    ->name('backdoor.activity-logs.')
    ->group(function () {

        // Halaman index
        Route::get('/', [ActivityLogController::class, 'index'])
            ->name('index');

        // JSON data untuk tabel (useDatatable)
        Route::get('/data', [ActivityLogController::class, 'data'])
            ->name('data');
    });
```

---

## 5. Backend — DTO

**File:** `app/Domains/SystemSettings/DTOs/ActivityLogRowData.php`

```php
<?php

namespace App\Domains\SystemSettings\DTOs;

use Spatie\Activitylog\Models\Activity;
use Spatie\LaravelData\Data;

class ActivityLogRowData extends Data
{
    public function __construct(
        public readonly int    $id,
        public readonly string $waktu_sesi,
        public readonly string $pelaku,
        public readonly string $modul,
        public readonly string $aktivitas,
        public readonly ?array $properties,
    ) {}

    public static function fromModel(Activity $activity): self
    {
        // Potong namespace PHP menjadi nama class saja + ID
        $subjectClass = $activity->subject_type
            ? class_basename($activity->subject_type)
            : '-';

        $modul = $activity->subject_id
            ? "{$subjectClass} (ID: {$activity->subject_id})"
            : $subjectClass;

        return new self(
            id:         $activity->id,
            waktu_sesi: $activity->created_at->format('d M Y, H:i'),
            pelaku:     $activity->causer?->name ?? 'Sistem',
            modul:      $modul,
            aktivitas:  $activity->description,
            properties: $activity->properties?->toArray(),
        );
    }
}
```

---

## 6. Backend — Controller

**File:** `app/Domains/SystemSettings/Http/Controllers/Backdoor/ActivityLogController.php`

```php
<?php

namespace App\Domains\SystemSettings\Http\Controllers\Backdoor;

use App\Domains\SystemSettings\DTOs\ActivityLogRowData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Halaman index activity log.
     */
    public function index(): View
    {
        return view('backdoor.system-settings.activity-logs.index');
    }

    /**
     * JSON endpoint untuk useDatatable.
     */
    public function data(Request $request): JsonResponse
    {
        $search = $request->input('search', '');
        $limit  = max(1, min($request->integer('limit', 10), 100));

        $query = Activity::with('causer')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('description', 'ilike', "%{$search}%")
                          ->orWhereHasMorph(
                              'causer',
                              '*',
                              fn($u) => $u->where('name', 'ilike', "%{$search}%")
                          );
                });
            })
            ->latest('created_at');

        $paginated = $query->paginate($limit);

        return response()->json([
            'data'         => ActivityLogRowData::collect($paginated->items()),
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'total'        => $paginated->total(),
            'per_page'     => $paginated->perPage(),
        ]);
    }
}
```

---

## 7. Frontend — JS Module (Alpine.js)

**File:** `resources/js/features/backdoor/system-settings/ActivityLogs.js`

```javascript
import useDatatable from "@/lib/useDatatable";
import route from "@/lib/route";

export default function ActivityLogs(Alpine) {
  // --- State Tabel ---
  const { state: table, ...methods } = useDatatable(
    Alpine,
    route("backdoor.activity-logs.data"),
    { debounceMs: 400 }
  );
  Object.assign(table, methods);

  // --- State Modal Detail ---
  const modal = Alpine.reactive({
    isOpen: false,
    properties: null,
  });

  const openDetail = (properties) => {
    modal.properties = properties;
    modal.isOpen = true;
  };

  const closeDetail = () => {
    modal.isOpen = false;
    modal.properties = null;
  };

  // Helper: format JSON untuk <pre>
  const formatProperties = (properties) => {
    if (!properties) return "Tidak ada data perubahan.";
    return JSON.stringify(properties, null, 2);
  };

  // Helper: kelas badge berdasarkan description
  const badgeClass = (description) => {
    const map = {
      created: "text-lime-700 bg-lime-100",
      updated: "text-yellow-700 bg-yellow-100",
      deleted: "text-red-700 bg-red-100",
    };
    return map[description] ?? "text-stone-600 bg-stone-200";
  };

  return {
    table,
    modal,
    openDetail,
    closeDetail,
    formatProperties,
    badgeClass,
    init() {
      table.fetch();
    },
  };
}
```

---

## 8. Frontend — Blade View

**File:** `resources/views/backdoor/system-settings/activity-logs/index.blade.php`

```blade
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('backdoor.dashboard')],
    ['label' => 'Pengaturan Sistem', 'url' => '#'],
    ['label' => 'Activity Logs', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Activity Logs"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/system-settings/ActivityLogs"
>
  <x-slot:content>
    <div x-data="ActivityLogs" x-cloak class="w-full space-y-6">

      <x-backdoor.shared.page-header title="Activity Logs" />

      {{-- Table Card --}}
      <div class="relative overflow-visible border border-stone-200 bg-stone-50 p-6">

        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari aktivitas atau nama pelaku..." />
          </x-slot:left>
        </x-backdoor.table.header>

        <x-backdoor.table.container headers="No,Waktu Sesi,Pelaku,Modul & ID,Aktivitas,Aksi">
          <template x-for="(log, index) in table.data" :key="log.id">
            <tr
              class="border-b border-stone-200 transition hover:bg-stone-100"
              x-show="!table.isLoading"
              x-cloak
            >

              {{-- No --}}
              <x-backdoor.table.cell
                class="text-stone-500"
                x-text="index + 1 + ((table.pagination.current_page - 1) * table.pagination.per_page)"
              />

              {{-- Waktu Sesi --}}
              <x-backdoor.table.cell
                class="whitespace-nowrap text-sm text-stone-600 font-mono"
                x-text="log.waktu_sesi"
              />

              {{-- Pelaku --}}
              <x-backdoor.table.cell
                class="font-semibold text-stone-900"
                x-text="log.pelaku"
              />

              {{-- Modul & ID --}}
              <x-backdoor.table.cell
                class="text-sm text-stone-600"
                x-text="log.modul"
              />

              {{-- Aktivitas (Badge) --}}
              <x-backdoor.table.cell>
                <span
                  class="inline-block px-2 py-0.5 text-xs font-semibold uppercase tracking-wide"
                  :class="badgeClass(log.aktivitas)"
                  x-text="log.aktivitas"
                ></span>
              </x-backdoor.table.cell>

              {{-- Aksi: Tombol Lihat Detail --}}
              <x-backdoor.table.cell>
                <button
                  type="button"
                  x-on:click="openDetail(log.properties)"
                  class="text-xs font-medium text-stone-600 underline underline-offset-2 hover:text-stone-900 transition"
                >
                  Lihat Detail
                </button>
              </x-backdoor.table.cell>

            </tr>
          </template>
        </x-backdoor.table.container>

        <x-backdoor.table.pagination />

      </div>

      {{-- Modal Detail Perubahan --}}
      <div
        x-show="modal.isOpen"
        x-transition
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        x-on:click.self="closeDetail()"
      >
        <div class="w-full max-w-2xl bg-white border border-stone-300">

          {{-- Header Modal --}}
          <div class="flex items-center justify-between border-b border-stone-200 px-6 py-4">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-stone-700">
              Detail Perubahan
            </h2>
            <button
              type="button"
              x-on:click="closeDetail()"
              class="text-stone-400 hover:text-stone-700 transition"
              aria-label="Tutup modal"
            >
              <i class="ri-close-line text-xl"></i>
            </button>
          </div>

          {{-- Body Modal: JSON Pre --}}
          <div class="p-6">
            <pre
              class="overflow-auto max-h-96 bg-stone-100 border border-stone-200 p-4 text-xs text-stone-700 font-mono leading-relaxed"
              x-text="formatProperties(modal.properties)"
            ></pre>
          </div>

          {{-- Footer Modal --}}
          <div class="flex justify-end border-t border-stone-200 px-6 py-3">
            <button
              type="button"
              x-on:click="closeDetail()"
              class="bg-stone-800 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white hover:bg-stone-900 transition"
            >
              Tutup
            </button>
          </div>

        </div>
      </div>

    </div>
  </x-slot:content>
</x-layouts.backdoor.index>
```

---

## 9. Catatan Modifikasi File Existing

### `routes/backdoor/system-settings/system-settings.php` (atau file induk)

Pastikan file `activity-logs.php` sudah di-include:

```php
require __DIR__ . '/activity-logs.php';
```

### `resources/views/components/layouts/backdoor/components/sidebar-links.blade.php`

Tambahkan menu item di bawah grup Pengaturan Sistem:

```blade
{{-- activity logs start --}}
<x-layouts.backdoor.components.sidebar-link-item
  :href="route('backdoor.activity-logs.index')"
  :active="request()->routeIs('backdoor.activity-logs.*')"
  icon='ri-file-list-3-line'
  title='Activity Logs'
/>
{{-- activity logs end --}}
```
