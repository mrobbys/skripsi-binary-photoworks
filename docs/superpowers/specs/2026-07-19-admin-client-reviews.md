# Spec: Admin Backdoor — Ulasan Klien (Client Reviews)

**Tanggal:** 2026-07-19
**Branch:** `feat/admin-client-reviews`
**Scope:** Backend (Controller, DTO, Routes) + Frontend (Alpine.js Module, Blade View)
**Stack:** Laravel 13 · PHP 8.3 · Alpine.js · Tailwind CSS v4 · Axios · SweetAlert2 · Tippy.js
**Design System:** ThoughtStream (flat, no rounded corners, no shadows, stone palette, border separators)

---

## Daftar Isi

1. [Aturan Bisnis & Logika Domain](#1-aturan-bisnis--logika-domain)
2. [Struktur Direktori File Baru / Dimodifikasi](#2-struktur-direktori)
3. [Backend — Routes](#3-backend--routes)
4. [Backend — DTO](#4-backend--dto)
5. [Backend — Controller](#5-backend--controller)
6. [Frontend — JS Module (Alpine.js)](#6-frontend--js-module-alpinejs)
7. [Frontend — Blade View](#7-frontend--blade-view)

---

## 1. Aturan Bisnis & Logika Domain

### A. Sumber Data

Tabel `reviews` dengan struktur:

```
id | user_id | rating (int 1-5) | comment (text) | created_at | updated_at
```

Relasi: `Review belongsTo User` (sudah ada di `Review.php`).

### B. Stats Card (4 Card)

Diambil via endpoint JSON terpisah (`/stats`), bukan dari render server-side.

| Label | Formula |
|---|---|
| Rata-rata Rating | `AVG(reviews.rating)` dibulatkan 1 desimal |
| Total Ulasan | `COUNT(reviews.id)` |
| Ulasan Bintang 5 | `COUNT WHERE rating = 5` |
| Ulasan Mengecewakan | `COUNT WHERE rating <= 2` |

> **"Ulasan Mengecewakan"** — label yang empatik dan bisa ditindaklanjuti admin untuk respons cepat.

### C. Tabel — Kolom

| No | Kolom | Sumber | Format |
|---|---|---|---|
| 1 | No | — | Nomor urut dengan offset pagination |
| 2 | Nama Klien | `users.name` via relasi | Teks |
| 3 | Rating | `reviews.rating` | `"4 / 5"` — font mono, hijau ≥4, kuning =3, merah ≤2 |
| 4 | Komentar | `reviews.comment` | Truncate 1 baris + Tippy.js (hover + klik) untuk teks lengkap |
| 5 | Tanggal | `reviews.created_at` | Format `d M Y` (contoh: `18 Jul 2026`) |
| 6 | Aksi | — | Tombol delete dengan `confirmModal` SweetAlert2 |

### D. Search

Live search dengan debounce 400ms terhadap:
- Nama klien (`users.name`)
- Isi komentar (`reviews.comment`)

### E. Delete Flow

1. User klik "Hapus" di dropdown aksi baris.
2. `confirmModal` SweetAlert2 muncul.
3. Jika dikonfirmasi → kirim `DELETE /backdoor/client-reviews/{id}`.
4. Setelah sukses → `table.reload()` + re-fetch `/stats` untuk memperbarui state statistik.

---

## 2. Struktur Direktori

```
app/Domains/Review/
├── DTOs/
│   ├── ClientReviewRowData.php             ← BARU
│   └── ClientReviewStatsData.php           ← BARU
└── Http/
    └── Controllers/
        └── Backdoor/
            └── ReviewManagementController.php  ← DIMODIFIKASI

routes/backdoor/
└── client-reviews.php                      ← DIMODIFIKASI

resources/js/features/backdoor/client-reviews/
└── index/
    └── Index.js                            ← BARU

resources/views/backdoor/client-reviews/
└── index.blade.php                         ← DIMODIFIKASI (isi file kosong)
```

---

## 3. Backend — Routes

**File:** `routes/backdoor/client-reviews.php`

```php
<?php

use App\Domains\Review\Http\Controllers\Backdoor\ReviewManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('backdoor/client-reviews')
    ->name('backdoor.client-reviews.')
    ->group(function () {

        // Halaman index
        Route::get('/', [ReviewManagementController::class, 'index'])
            ->name('index');

        // JSON data untuk tabel (useDatatable)
        Route::get('/data', [ReviewManagementController::class, 'data'])
            ->name('data');

        // JSON data untuk stats card
        Route::get('/stats', [ReviewManagementController::class, 'stats'])
            ->name('stats');

        // Hapus review
        Route::delete('/{review}', [ReviewManagementController::class, 'destroy'])
            ->name('destroy');
    });
```

> **Tambahkan** `require __DIR__ . '/client-reviews.php';` di `routes/backdoor/backdoor.php`.

---

## 4. Backend — DTO

### 4A. `ClientReviewRowData.php`

DTO untuk satu baris tabel. `extends Data` (Spatie) karena dikembalikan sebagai JSON collection.

**File:** `app/Domains/Review/DTOs/ClientReviewRowData.php`

```php
<?php

namespace App\Domains\Review\DTOs;

use App\Domains\Review\Models\Review;
use Spatie\LaravelData\Data;

class ClientReviewRowData extends Data
{
    public function __construct(
        public readonly int    $id,
        public readonly string $client_name,
        public readonly int    $rating,
        public readonly string $comment,
        public readonly string $created_at,
    ) {}

    public static function fromModel(Review $review): self
    {
        return new self(
            id:          $review->id,
            client_name: $review->user->name,
            rating:      $review->rating,
            comment:     $review->comment,
            created_at:  $review->created_at->format('d M Y'),
        );
    }
}
```

### 4B. `ClientReviewStatsData.php`

Plain PHP class (tidak `extends Data`) karena hanya 1 objek JSON, bukan collection.

**File:** `app/Domains/Review/DTOs/ClientReviewStatsData.php`

```php
<?php

namespace App\Domains\Review\DTOs;

class ClientReviewStatsData
{
    public function __construct(
        public readonly float $average_rating,
        public readonly int   $total_reviews,
        public readonly int   $five_star_reviews,
        public readonly int   $disappointing_reviews,
    ) {}
}
```

---

## 5. Backend — Controller

**File:** `app/Domains/Review/Http/Controllers/Backdoor/ReviewManagementController.php`

```php
<?php

namespace App\Domains\Review\Http\Controllers\Backdoor;

use App\Domains\Review\DTOs\ClientReviewRowData;
use App\Domains\Review\DTOs\ClientReviewStatsData;
use App\Domains\Review\Models\Review;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewManagementController extends Controller
{
    /**
     * Halaman daftar ulasan.
     */
    public function index(): View
    {
        return view('backdoor.client-reviews.index');
    }

    /**
     * JSON endpoint untuk useDatatable.
     */
    public function data(Request $request): JsonResponse
    {
        $search = $request->input('search', '');
        $limit  = max(1, min($request->integer('limit', 10), 100));

        $query = Review::with('user')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('comment', 'ilike', "%{$search}%")
                          ->orWhereHas('user', fn($u) => $u->where('name', 'ilike', "%{$search}%"));
                });
            })
            ->latest('created_at');

        $paginated = $query->paginate($limit);

        return response()->json([
            'data'         => ClientReviewRowData::collect($paginated->items()),
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'total'        => $paginated->total(),
            'per_page'     => $paginated->perPage(),
        ]);
    }

    /**
     * JSON endpoint untuk stats card.
     */
    public function stats(): JsonResponse
    {
        $stats = new ClientReviewStatsData(
            average_rating:        round(Review::avg('rating') ?? 0, 1),
            total_reviews:         Review::count(),
            five_star_reviews:     Review::where('rating', 5)->count(),
            disappointing_reviews: Review::where('rating', '<=', 2)->count(),
        );

        return response()->json($stats);
    }

    /**
     * Hapus satu review.
     */
    public function destroy(Review $review): JsonResponse
    {
        $review->delete();

        return response()->json(['message' => 'Ulasan berhasil dihapus.']);
    }
}
```

> **Catatan Performa:** Empat agregasi di `stats()` menggunakan 4 query terpisah. Untuk optimasi lanjut, bisa direfactor menjadi 1 query dengan Postgres conditional aggregates (`COUNT(*) FILTER (WHERE ...)`).

---

## 6. Frontend — JS Module (Alpine.js)

**File:** `resources/js/features/backdoor/client-reviews/index/Index.js`

```javascript
import useDatatable from "@/lib/useDatatable";
import { confirmModal, Toast } from "@/lib/sweetalert";
import route from "@/lib/route";
import axios from "@/lib/axiosInstance";

export default function Index(Alpine) {
  // --- State Tabel ---
  const { state: table, ...methods } = useDatatable(
    Alpine,
    route("backdoor.client-reviews.data"),
    { debounceMs: 400 }
  );
  Object.assign(table, methods);

  // --- State Stats ---
  const stats = Alpine.reactive({
    average_rating:        '-',
    total_reviews:         '-',
    five_star_reviews:     '-',
    disappointing_reviews: '-',
  });

  const fetchStats = async () => {
    const res = await axios.get(route("backdoor.client-reviews.stats"));
    Object.assign(stats, res.data);
  };

  // --- Aksi Delete ---
  const deleteReview = async (id) => {
    const result = await confirmModal(
      'Hapus Ulasan?',
      'Ulasan yang dihapus tidak dapat dikembalikan.',
      'warning',
      'Ya, Hapus'
    );

    if (!result.isConfirmed) return;

    try {
      await axios.delete(route("backdoor.client-reviews.destroy", id));
      Toast.fire({ icon: 'success', title: 'Ulasan berhasil dihapus.' });

      // Reload tabel + update stats secara bersamaan
      table.reload();
      fetchStats();
    } catch {
      Toast.fire({ icon: 'error', title: 'Gagal menghapus ulasan.' });
    }
  };

  return {
    table,
    stats,
    deleteReview,
    init() {
      table.fetch();
      fetchStats();
    },
  };
}
```

---

## 7. Frontend — Blade View

**File:** `resources/views/backdoor/client-reviews/index.blade.php`

```blade
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('backdoor.dashboard')],
    ['label' => 'Ulasan Klien', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Ulasan Klien"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/client-reviews/index/Index"
>
  <x-slot:content>
    <div x-data="Index" x-cloak class="w-full space-y-6">

      <x-backdoor.shared.page-header title="Ulasan Klien" />

      {{-- Stats Cards --}}
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">

        <x-backdoor.shared.stats-card
          label="Rata-rata Rating"
          x-text="stats.average_rating + ' / 5'"
        />

        <x-backdoor.shared.stats-card
          label="Total Ulasan"
          x-text="stats.total_reviews + ' Ulasan'"
        />

        <x-backdoor.shared.stats-card
          label="Ulasan Bintang 5"
          x-text="stats.five_star_reviews + ' Ulasan'"
        />

        <x-backdoor.shared.stats-card
          label="Ulasan Mengecewakan"
          x-text="stats.disappointing_reviews + ' Ulasan'"
        />

      </div>

      {{-- Table Card --}}
      <div class="relative overflow-visible border border-stone-200 bg-stone-50 p-6">

        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari nama klien atau komentar..." />
          </x-slot:left>
        </x-backdoor.table.header>

        <x-backdoor.table.container headers="No,Nama Klien,Rating,Komentar,Tanggal,Aksi">
          <template x-for="(review, index) in table.data" :key="review.id">
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

              {{-- Nama Klien --}}
              <x-backdoor.table.cell
                class="font-semibold text-stone-900"
                x-text="review.client_name"
              />

              {{-- Rating --}}
              <x-backdoor.table.cell>
                <span
                  class="font-mono text-sm font-bold"
                  :class="{
                    'text-lime-600':   review.rating >= 4,
                    'text-yellow-600': review.rating === 3,
                    'text-red-600':    review.rating <= 2
                  }"
                  x-text="review.rating + ' / 5'"
                ></span>
              </x-backdoor.table.cell>

              {{-- Komentar (truncate + Tippy.js hover+klik tooltip) --}}
              <x-backdoor.table.cell class="max-w-xs">
                <span
                  class="block max-w-[200px] truncate text-sm text-stone-700 cursor-default"
                  x-text="review.comment"
                  x-init="
                    $nextTick(() => {
                      tippy($el, {
                        content: review.comment,
                        trigger: 'mouseenter click',
                        placement: 'top',
                        maxWidth: 320,
                        theme: 'custom',
                        arrow: false,
                      });
                    })
                  "
                ></span>
              </x-backdoor.table.cell>

              {{-- Tanggal --}}
              <x-backdoor.table.cell
                class="text-sm text-stone-500"
                x-text="review.created_at"
              />

              {{-- Aksi --}}
              <x-backdoor.table.actions>
                <x-backdoor.table.action-item
                  x-on:click="deleteReview(review.id); closeDropdown()"
                  color="text-red-600"
                  text="Hapus"
                />
              </x-backdoor.table.actions>

            </tr>
          </template>
        </x-backdoor.table.container>

        <x-backdoor.table.pagination />

      </div>

    </div>
  </x-slot:content>
</x-layouts.backdoor.index>
```

---

## Catatan Modifikasi File Existing

### `routes/backdoor/backdoor.php`

```php
require __DIR__ . '/client-reviews.php';
```

### `resources/views/components/layouts/backdoor/components/sidebar-links.blade.php`

```blade
{{-- ulasan klien start --}}
<x-layouts.backdoor.components.sidebar-link-item
  :href="route('backdoor.client-reviews.index')"
  :active="request()->routeIs('backdoor.client-reviews.*')"
  icon='ri-star-line'
  title='Ulasan Klien'
/>
{{-- ulasan klien end --}}
```
