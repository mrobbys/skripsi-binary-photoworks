# Spec: Admin Backdoor — Daftar Jadwal Sesi (Session Schedule List)

**Tanggal:** 2026-07-16
**Branch:** `feat/admin-session-schedule`
**Scope:** Backend (Controller, DTO, PDF Report) + Frontend (Alpine.js Modules, Blade Views)
**Stack:** Laravel 13 · PHP 8.3 · Alpine.js · Tailwind CSS v4 · Axios · Spatie Laravel PDF
**Design System:** ThoughtStream (flat, no rounded corners, no shadows, stone palette, border separators)

---

## Daftar Isi

1. [Aturan Bisnis & Logika Domain](#1-aturan-bisnis--logika-domain)
2. [Struktur Direktori File Baru](#2-struktur-direktori-file-baru)
3. [Backend — Routes](#3-backend--routes)
4. [Backend — DTO](#4-backend--dto)
5. [Backend — Controller](#5-backend--controller)
6. [Backend — PDF Report Controller](#6-backend--pdf-report-controller)
7. [Backend — PDF Blade View](#7-backend--pdf-blade-view)
8. [Frontend — JS Modules (Alpine.js)](#8-frontend--js-modules-alpinejs)
9. [Frontend — Blade Views](#9-frontend--blade-views)
10. [Catatan Implementasi](#10-catatan-implementasi)

---

## 1. Aturan Bisnis & Logika Domain

### A. Filter Data Jadwal

Tabel **hanya** menampilkan booking yang sudah terkonfirmasi secara finansial:

| Status Booking | Tampil? | Alasan |
|---|:---:|---|
| `Menunggu (PENDING)` | ❌ | Belum ada komitmen bayar |
| `DP Terbayar (DP_PAID)` | ✅ | Sudah bayar 60%, sesi pasti jalan |
| `Lunas (SUCCESS)` | ✅ | Sudah bayar 100%, sesi pasti jalan |
| `Selesai (DONE)` | ✅ | Historis — sesi sudah selesai |
| `Batal (CANCELLED)` | ❌ | Sesi tidak jalan |

Query filter: `whereIn('status', [DP_PAID, SUCCESS, DONE])`

### B. Urutan Sort

Data diurutkan berdasarkan tanggal sesi paling jauh ke depan tampil paling atas (descending):

```
ORDER BY booking_date DESC, start_time DESC
```

### C. Status Sesi Operasional (Di-derive di DTO)

Status sesi bukan disimpan di database, melainkan dihitung dinamis di DTO berdasarkan perbandingan `booking_date` + `start_time`/`end_time` dengan waktu server (`Carbon::now()`):

| Kondisi | `session_status` | Warna Badge |
|---|---|---|
| `booking_date > today` | `MENDATANG` | Stone (abu netral) |
| `booking_date = today` AND jam server < `start_time` | `MENUNGGU` | Amber/Kuning |
| `booking_date = today` AND jam server antara `start_time`–`end_time` | `SEDANG BERLANGSUNG` | Blue/Biru |
| `booking_date = today` AND jam server > `end_time` OR status = `DONE` | `SELESAI` | Lime/Hijau |
| `booking_date < today` AND status bukan `DONE` | `SELESAI` | Lime/Hijau |

### D. Widget Statistik (Formula)

```
Total Sesi Foto Hari Ini = COUNT(bookings)
    WHERE booking_date = CURRENT_DATE
    AND status IN ('DP Terbayar', 'Lunas', 'Selesai')

Total Sesi Selesai Hari Ini = COUNT(bookings)
    WHERE booking_date = CURRENT_DATE
    AND status = 'Selesai'

Total Jadwal Mendatang = COUNT(bookings)
    WHERE booking_date > CURRENT_DATE
    AND status IN ('DP Terbayar', 'Lunas')
```

### E. Aksi Baris Tabel (Row Actions ⋮)

| Aksi | Kondisi Muncul | Efek |
|---|---|---|
| **Lihat Detail Booking** | Selalu | Redirect ke `/backdoor/booking-management/{booking_code}` |
| **Input Link GDrive & Selesaikan** | `status = SUCCESS (Lunas)` | Tampilkan dialog input GDrive link → update `gdrive_link` + update `status → DONE` + kirim notif WA |
| **Tandai Lunas** | `status = DP_PAID` | Redirect ke halaman detail booking |

### F. PDF Cetak Jadwal Hari Ini

- **Trigger**: Klik tombol "Cetak Jadwal Hari Ini" → buka tab baru (`target="_blank"`) ke route PDF
- **Cakupan data**: Hanya booking dengan `booking_date = hari ini` AND `status IN (DP_PAID, SUCCESS, DONE)`, diurutkan `start_time ASC`
- **Library**: Spatie Laravel PDF (`pdf()->view()`)
- **Orientasi**: Portrait, format A4
- **Konten PDF**:
  - Header: Logo teks "BINARY PHOTOWORKS" + Judul "LAPORAN JADWAL OPERASIONAL HARIAN" + Tanggal (contoh: "Rabu, 16 Juli 2026")
  - Tabel: No | Waktu Sesi | Nama Klien | Paket Foto & Background | Keterangan (dari field `notes`)
  - Footer: Tanggal & jam cetak + copyright

### G. Pencarian

Search box mencari berdasarkan:
- Nama klien (`users.name`)
- Nomor HP klien (`users.phone`)
- Email klien (`users.email`)
- Kode booking (`bookings.booking_code`)
- Nama paket variant (`package_variants.name`)

---

## 2. Struktur Direktori File Baru

```
app/Domains/Booking/
├── DTOs/
│   └── SessionScheduleIndexData.php        ← BARU
└── Http/
    └── Controllers/Backdoor/
        └── SessionScheduleController.php    ← BARU

app/Http/Controllers/Reports/
└── SessionSchedulePdfController.php         ← BARU

routes/
└── backdoor/
    └── session-schedule.php                 ← MODIFIKASI (isi routes)
└── pdfs.php                                 ← MODIFIKASI (tambah route PDF)

resources/views/backdoor/session-schedule/
└── index.blade.php                          ← BARU

resources/views/pdfs/
└── session-schedule.blade.php               ← BARU

resources/js/features/backdoor/session-schedule/
└── index/
    ├── Index.js                             ← BARU
    └── useState.js                          ← BARU
```

---

## 3. Backend — Routes

### 3.1 `routes/backdoor/session-schedule.php`

```php
<?php

use App\Domains\Booking\Http\Controllers\Backdoor\SessionScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
  ->prefix('backdoor/session-schedule')
  ->name('backdoor.session-schedule.')
  ->group(function () {
    Route::get('/list', [SessionScheduleController::class, 'index'])
      ->name('list');

    Route::patch('/list/{booking:booking_code}/gdrive', [SessionScheduleController::class, 'updateGdrive'])
      ->name('list.gdrive');
  });
```

### 3.2 `routes/pdfs.php` — Tambah route berikut di dalam grup `middleware(['auth'])`

```php
Route::get('/session-schedule/daily-report', \App\Http\Controllers\Reports\SessionSchedulePdfController::class)
  ->name('session-schedule.daily-report');
```

---

## 4. Backend — DTO

### `app/Domains/Booking/DTOs/SessionScheduleIndexData.php`

DTO ini merepresentasikan satu baris data jadwal di tabel. `session_status` adalah properti turunan yang di-compute dari data jadwal dan waktu server saat ini.

```php
<?php

namespace App\Domains\Booking\DTOs;

use App\Domains\Booking\Models\Booking;
use App\Support\Formatter;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

class SessionScheduleIndexData extends Data
{
    public function __construct(
        public readonly int    $id,
        public readonly string $booking_code,
        public readonly string $user_name,
        public readonly string $user_email,
        public readonly string $user_phone,
        public readonly string $package_name,   // "{Package->name} — {Variant->name}"
        public readonly string $variant_name,
        public readonly string $background_name,
        public readonly string $booking_status, // nilai enum BookingStatus (label)
        public readonly string $session_status, // MENDATANG | MENUNGGU | SEDANG BERLANGSUNG | SELESAI
        public readonly string $formatted_date, // "Rabu, 16 Juli 2026"
        public readonly string $formatted_time, // "10:00 - 11:00 WITA"
        public readonly ?string $gdrive_link,
        public readonly ?string $notes,
    ) {}

    public static function fromModel(Booking $booking): self
    {
        $now         = Carbon::now();
        $bookingDate = Carbon::parse($booking->booking_date)->startOfDay();
        $today       = $now->copy()->startOfDay();

        // Buat Carbon dari time string untuk dibandingkan dengan $now
        $startTime = Carbon::parse($booking->start_time);
        $endTime   = Carbon::parse($booking->end_time);

        $todayStart = $today->copy()->setTimeFrom($startTime);
        $todayEnd   = $today->copy()->setTimeFrom($endTime);

        $sessionStatus = match (true) {
            $booking->status->value === 'Selesai'                              => 'SELESAI',
            $bookingDate->lt($today)                                            => 'SELESAI',
            $bookingDate->gt($today)                                            => 'MENDATANG',
            $bookingDate->eq($today) && $now->lt($todayStart)                  => 'MENUNGGU',
            $bookingDate->eq($today) && $now->between($todayStart, $todayEnd)  => 'SEDANG BERLANGSUNG',
            default                                                             => 'SELESAI',
        };

        return new self(
            id:               $booking->id,
            booking_code:     $booking->booking_code,
            user_name:        $booking->user->name,
            user_email:       $booking->user->email,
            user_phone:       $booking->user->phone,
            package_name:     $booking->packageVariant->package->name . ' — ' . $booking->packageVariant->name,
            variant_name:     $booking->packageVariant->name,
            background_name:  $booking->background?->name ?? '-',
            booking_status:   $booking->status->value,
            session_status:   $sessionStatus,
            formatted_date:   Formatter::dateId($booking->booking_date, 'l, d F Y'),
            formatted_time:   Formatter::timeRange($booking->start_time, $booking->end_time),
            gdrive_link:      $booking->gdrive_link,
            notes:            $booking->notes,
        );
    }
}
```

---

## 5. Backend — Controller

### `app/Domains/Booking/Http/Controllers/Backdoor/SessionScheduleController.php`

```php
<?php

namespace App\Domains\Booking\Http\Controllers\Backdoor;

use App\Domains\Booking\DTOs\SessionScheduleIndexData;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsappNotificationJob;
use App\Support\Formatter;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SessionScheduleController extends Controller
{
    /**
     * Halaman Index: Daftar Jadwal Sesi Foto.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->wantsJson()) {
            try {
                $query = Booking::with(['user', 'packageVariant.package', 'background'])
                    ->whereIn('status', [
                        BookingStatus::DP_PAID,
                        BookingStatus::SUCCESS,
                        BookingStatus::DONE,
                    ])
                    ->orderBy('booking_date', 'desc')
                    ->orderBy('start_time', 'desc');

                if ($search = $request->input('search')) {
                    $query->where(function ($q) use ($search) {
                        $q->where('booking_code', 'ilike', "%{$search}%")
                          ->orWhereHas('user', fn ($u) => $u
                              ->where('name', 'ilike', "%{$search}%")
                              ->orWhere('email', 'ilike', "%{$search}%")
                              ->orWhere('phone', 'ilike', "%{$search}%")
                          )
                          ->orWhereHas('packageVariant', fn ($v) => $v
                              ->where('name', 'ilike', "%{$search}%")
                          );
                    });
                }

                $limit    = max(1, min((int) $request->query('limit', 10), 100));
                $bookings = $query->paginate($limit);

                $today = Carbon::today();

                return response()->json([
                    'success'       => true,
                    'data'          => SessionScheduleIndexData::collect($bookings->items()),
                    'current_page'  => $bookings->currentPage(),
                    'last_page'     => $bookings->lastPage(),
                    'total'         => $bookings->total(),
                    'total_today'   => Booking::whereDate('booking_date', $today)
                        ->whereIn('status', [BookingStatus::DP_PAID, BookingStatus::SUCCESS, BookingStatus::DONE])
                        ->count(),
                    'done_today'    => Booking::whereDate('booking_date', $today)
                        ->where('status', BookingStatus::DONE)
                        ->count(),
                    'upcoming_total' => Booking::where('booking_date', '>', $today)
                        ->whereIn('status', [BookingStatus::DP_PAID, BookingStatus::SUCCESS])
                        ->count(),
                ]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        }

        return view('backdoor.session-schedule.index');
    }

    /**
     * Simpan link Google Drive & tandai sesi selesai (DONE).
     * Hanya booking berstatus Lunas (SUCCESS) yang bisa di-update.
     */
    public function updateGdrive(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->status !== BookingStatus::SUCCESS) {
            return response()->json([
                'success' => false,
                'message' => 'Booking ini tidak dalam status Lunas.',
            ], 422);
        }

        $validated = $request->validate([
            'gdrive_link' => ['required', 'url', 'starts_with:http://,https://'],
        ], [
            'gdrive_link.required'    => 'Link Google Drive wajib diisi.',
            'gdrive_link.url'         => 'Format link tidak valid.',
            'gdrive_link.starts_with' => 'Link harus diawali dengan http:// atau https://',
        ]);

        try {
            $booking->load(['user', 'packageVariant.package']);
            $booking->update([
                'gdrive_link' => $validated['gdrive_link'],
                'status'      => BookingStatus::DONE,
            ]);

            $isSendWa = $request->boolean('send_wa_notification', true);
            if ($isSendWa) {
                SendWhatsappNotificationJob::dispatch(
                    $booking->user->phone,
                    $this->buildGdriveMessage($booking)
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Sesi berhasil ditandai selesai.'
                    . ($isSendWa ? " Notifikasi WA dikirim ke {$booking->user->phone}." : ''),
                'data'    => SessionScheduleIndexData::fromModel($booking->refresh()),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan server.'], 500);
        }
    }

    /**
     * Pesan WA untuk notifikasi pengiriman link GDrive hasil foto.
     */
    private function buildGdriveMessage(Booking $booking): string
    {
        $code        = $booking->booking_code;
        $user        = $booking->user?->name;
        $package     = $booking->packageVariant?->package?->name;
        $variant     = $booking->packageVariant?->name;
        $gdriveLink  = $booking->gdrive_link;
        $bookingDate = Formatter::dateId($booking->booking_date, 'l, d F Y');

        return <<<TEXT
Halo {$user}, sesi foto Anda telah selesai!

Berikut adalah rincian pesanan Anda:
*Kode Booking* : {$code}
*Paket* : {$package} - {$variant}
*Tanggal Sesi* : {$bookingDate}

Berikut adalah Link Google Drive untuk mengunduh hasil foto Anda:
{$gdriveLink}

Terima kasih telah mempercayakan momen berharga Anda kepada Binary Photoworks!
TEXT;
    }
}
```

---

## 6. Backend — PDF Report Controller

### `app/Http/Controllers/Reports/SessionSchedulePdfController.php`

```php
<?php

namespace App\Http\Controllers\Reports;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Http\Controllers\Controller;
use App\Support\Formatter;
use Carbon\Carbon;
use Illuminate\Support\Fluent;

use function Spatie\LaravelPdf\Support\pdf;

class SessionSchedulePdfController extends Controller
{
    public function __invoke()
    {
        $today = Carbon::today();

        $bookings = Booking::with(['user', 'packageVariant.package', 'background'])
            ->whereDate('booking_date', $today)
            ->whereIn('status', [BookingStatus::DP_PAID, BookingStatus::SUCCESS, BookingStatus::DONE])
            ->orderBy('start_time', 'asc')
            ->get();

        $rows = $bookings->map(fn ($booking, $index) => new Fluent([
            'no'         => $index + 1,
            'time'       => Formatter::timeRange($booking->start_time, $booking->end_time),
            'name'       => $booking->user->name,
            'package'    => $booking->packageVariant->package->name . ' / ' . $booking->packageVariant->name,
            'background' => $booking->background?->name ?? '-',
            'notes'      => $booking->notes ?? '',
        ]));

        $printDate = Formatter::dateId($today, 'l, d F Y');
        $printTime = Carbon::now()->format('H:i');

        return pdf()
            ->view('pdfs.session-schedule', compact('rows', 'printDate', 'printTime'))
            ->format('a4')
            ->name("jadwal-harian-{$today->format('Y-m-d')}.pdf");
    }
}
```

---

## 7. Backend — PDF Blade View

### `resources/views/pdfs/session-schedule.blade.php`

```html
<x-layouts.pdf title="Laporan Jadwal Operasional Harian">
  <div class="max-w-4xl mx-auto p-10">
    {{-- header section start --}}
    <div class="flex justify-between items-start border-b-2 border-stone-900 pb-8 mb-8">
      {{-- section left start --}}
      <div>
        <img src="{{ public_path('assets/binary-logo/binary-logo-text-black.png') }}" alt="Binary Photoworks" class="h-10 object-contain">
      </div>
      {{-- section left end --}}

      {{-- section right start --}}
      <div class="text-right">
        <h1 class="text-2xl font-bold tracking-tight text-stone-900 uppercase">Laporan Jadwal Operasional</h1>
        <div class="mt-2 text-sm text-stone-600">
          <p>Tanggal: <span class="font-medium text-stone-900">{{ $printDate }}</span></p>
        </div>
      </div>
      {{-- section right end --}}
    </div>
    {{-- header section end --}}

    {{-- table section start --}}
    <div class="mb-10">
      @if($rows->isEmpty())
        <div class="text-center py-10">
          <p class="text-stone-500 font-medium">Tidak ada jadwal sesi foto untuk hari ini.</p>
        </div>
      @else
        <table class="w-full text-left text-sm border border-stone-300">
          <thead>
            <tr class="bg-stone-200 border-b-2 border-stone-400 text-stone-900">
              <th class="py-3 px-3 font-bold uppercase tracking-wider text-xs border-r border-stone-300 w-[5%] text-center">No</th>
              <th class="py-3 px-3 font-bold uppercase tracking-wider text-xs border-r border-stone-300 w-[15%]">Waktu Sesi</th>
              <th class="py-3 px-3 font-bold uppercase tracking-wider text-xs border-r border-stone-300 w-[20%]">Nama Klien</th>
              <th class="py-3 px-3 font-bold uppercase tracking-wider text-xs border-r border-stone-300 w-[35%]">Paket & Background</th>
              <th class="py-3 px-3 font-bold uppercase tracking-wider text-xs w-[25%]">Keterangan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone-300">
            @foreach($rows as $row)
              <tr class="even:bg-stone-50">
                <td class="py-3 px-3 text-center align-top border-r border-stone-300">{{ $row->no }}</td>
                <td class="py-3 px-3 font-medium align-top border-r border-stone-300">{{ $row->time }}</td>
                <td class="py-3 px-3 font-bold text-stone-900 align-top border-r border-stone-300">{{ $row->name }}</td>
                <td class="py-3 px-3 align-top border-r border-stone-300">
                  <p class="font-medium text-stone-900">{{ $row->package }}</p>
                  <p class="text-xs text-stone-500 mt-1">{{ $row->background }}</p>
                </td>
                <td class="py-3 px-3 align-top text-stone-700">{{ $row->notes ?: '-' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
    {{-- table section end --}}

    <!-- Footer Section -->
    <div class="border-t border-stone-200 pt-8 flex justify-between text-xs text-stone-500">
      <p>Dicetak pada: {{ $printDate }}, pukul {{ $printTime }} WITA</p>
      <p>Binary Photoworks &copy; {{ date('Y') }}</p>
    </div>
  </div>
</x-layouts.pdf>
```

> **Catatan**: Layout menggunakan komponen `<x-layouts.pdf>` yang sudah membawa konfigurasi Tailwind CSS untuk export Spatie Laravel PDF.

---

## 8. Frontend — JS Modules (Alpine.js)

### `resources/js/features/backdoor/session-schedule/index/useState.js`

```javascript
/**
 * State terpusat untuk halaman Daftar Jadwal Sesi.
 *
 * @param {import('alpinejs').Alpine} Alpine
 * @returns {object}
 */
export default function useState(Alpine) {
  return Alpine.reactive({
    // Widget stats
    totalToday:    0,
    doneToday:     0,
    upcomingTotal: 0,

    // State dialog GDrive
    gdrive: {
      isOpen:       false,
      bookingCode:  null,
      link:         '',
      sendWa:       true,
      isLoading:    false,
      error:        null,
    },
  });
}
```

### `resources/js/features/backdoor/session-schedule/index/Index.js`

```javascript
/**
 * Komponen Alpine.js untuk halaman Daftar Jadwal Sesi.
 *
 * File: resources/js/features/backdoor/session-schedule/index/Index.js
 * Penggunaan di Blade: <div x-data="Index">...</div>
 *
 * @param {import('alpinejs').Alpine} Alpine
 * @returns {object}
 */
import useDatatable from '@/lib/useDatatable';
import useState from './useState';
import route from '@/lib/route';
import { Toast } from '@/lib/sweetalert';
import axios from '@/lib/axiosInstance';
import { z } from 'zod';

const gdriveLinkSchema = z.object({
  gdriveLink: z
    .string()
    .min(1, 'Link Google Drive wajib diisi.')
    .url('Format link tidak valid.')
    .refine(
      (val) => val.startsWith('http://') || val.startsWith('https://'),
      { message: 'Link harus diawali dengan http:// atau https://' }
    ),
});

export default function Index(Alpine) {
  const state = useState(Alpine);

  const {
    state: table,
    fetch,
    setSearch,
    nextPage,
    prevPage,
    goToPage,
    reload,
    getPages,
  } = useDatatable(Alpine, route('backdoor.session-schedule.list'), {
    onSuccess: (res) => {
      if (res.total_today    !== undefined) state.totalToday    = res.total_today;
      if (res.done_today     !== undefined) state.doneToday     = res.done_today;
      if (res.upcoming_total !== undefined) state.upcomingTotal = res.upcoming_total;
    },
    onError: () => Toast.fire({ icon: 'error', title: 'Gagal memuat data jadwal.' }),
    debounceMs: 400,
  });

  Object.assign(table, { fetch, setSearch, nextPage, prevPage, goToPage, reload, getPages });

  const init = () => fetch();

  // ─── Dialog GDrive ──────────────────────────────────────────────────────────

  const openGdriveDialog = (bookingCode, existingLink = '') => {
    state.gdrive.isOpen      = true;
    state.gdrive.bookingCode = bookingCode;
    state.gdrive.link        = existingLink ?? '';
    state.gdrive.sendWa      = true;
    state.gdrive.isLoading   = false;
    state.gdrive.error       = null;
  };

  const closeGdriveDialog = () => {
    state.gdrive.isOpen      = false;
    state.gdrive.bookingCode = null;
    state.gdrive.link        = '';
    state.gdrive.error       = null;
  };

  const submitGdrive = async () => {
    state.gdrive.error = null;

    // Validasi frontend (Zod)
    const result = gdriveLinkSchema.safeParse({ gdriveLink: state.gdrive.link });
    if (!result.success) {
      state.gdrive.error = result.error.issues[0].message;
      return;
    }

    state.gdrive.isLoading = true;
    try {
      await axios.patch(
        route('backdoor.session-schedule.list.gdrive', state.gdrive.bookingCode),
        {
          gdrive_link:          state.gdrive.link,
          send_wa_notification: state.gdrive.sendWa,
        }
      );

      closeGdriveDialog();
      Toast.fire({ icon: 'success', title: 'Sesi berhasil ditandai selesai.' });
      reload();
    } catch (err) {
      const msg = err.response?.data?.errors?.gdrive_link?.[0]
        ?? err.response?.data?.message
        ?? 'Terjadi kesalahan.';
      state.gdrive.error = msg;
    } finally {
      state.gdrive.isLoading = false;
    }
  };

  // ─── Return ─────────────────────────────────────────────────────────────────

  return {
    state,
    table,
    init,
    openGdriveDialog,
    closeGdriveDialog,
    submitGdrive,
  };
}
```

---

## 9. Frontend — Blade Views

### `resources/views/backdoor/session-schedule/index.blade.php`

```blade
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('backdoor.dashboard')],
    ['label' => 'Jadwal Sesi', 'url' => ''],
    ['label' => 'Daftar Jadwal', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Daftar Jadwal Sesi"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/session-schedule/index/Index"
>

  <x-slot:content>
    <div
      x-data="Index"
      x-cloak
      x-init="init()"
      class="w-full space-y-6"
    >

      {{-- page header + tanggal hari ini --}}
      <div class="flex flex-col gap-1">
        <x-backdoor.shared.page-header title="Daftar Jadwal Sesi" />
        <p class="text-xs text-stone-500">
          Data jadwal hari ini: <strong>{{ \Carbon\Carbon::today()->locale('id')->translatedFormat('l, d F Y') }}</strong>
        </p>
      </div>

      {{-- stats section --}}
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <x-backdoor.shared.stats-card
          label="Total Sesi Foto Hari Ini"
          x-text="state.totalToday"
          suffix="Sesi"
        />
        <x-backdoor.shared.stats-card
          label="Total Sesi Selesai Hari Ini"
          x-text="state.doneToday"
          suffix="Sesi"
        />
        <x-backdoor.shared.stats-card
          label="Total Jadwal Mendatang"
          x-text="state.upcomingTotal"
          suffix="Sesi"
        />
      </div>

      {{-- table card --}}
      <div class="relative overflow-visible border border-stone-200 bg-stone-50 p-6">

        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search
              placeholder="Cari nama, email, no. HP, kode booking, atau paket..."
            />
          </x-slot:left>

          <x-slot:right>
            <a
              href="{{ route('session-schedule.daily-report') }}"
              target="_blank"
              class="focus:outline-hidden inline-flex cursor-pointer items-center justify-center gap-2 border border-stone-700 bg-stone-700 px-4 py-2 text-sm font-semibold tracking-wide text-stone-50 transition-all duration-150 hover:bg-stone-800 focus-visible:ring-2 focus-visible:ring-stone-500 focus-visible:ring-offset-2 active:scale-[0.98]"
            >
              <i class="ri-printer-line leading-none"></i>
              <span>Cetak Jadwal Hari Ini</span>
            </a>
          </x-slot:right>
        </x-backdoor.table.header>

        <x-backdoor.table.container
          headers="No,Waktu Sesi,Nama Klien,Paket Foto,Background,Status Sesi,Aksi"
        >
          <template
            x-for="(schedule, index) in table.data"
            :key="schedule.id"
          >
            <tr
              class="border-b border-stone-200 transition hover:bg-stone-100"
              x-show="!table.isLoading"
              x-cloak
            >

              <x-backdoor.table.cell
                class="text-stone-600"
                x-text="index + 1 + ((table.pagination.current_page - 1) * table.pagination.per_page)"
              />

              <x-backdoor.table.cell class="text-sm">
                <p class="font-medium text-stone-900" x-text="schedule.formatted_date"></p>
                <p class="mt-1 flex items-center gap-1 text-xs text-stone-500">
                  <i class="ri-time-line"></i>
                  <span x-text="schedule.formatted_time"></span>
                </p>
              </x-backdoor.table.cell>

              <x-backdoor.table.cell class="text-sm">
                <p class="font-semibold text-stone-900" x-text="schedule.user_name"></p>
                <p class="mt-0.5 text-xs text-stone-500" x-text="schedule.user_phone"></p>
              </x-backdoor.table.cell>

              <x-backdoor.table.cell class="text-sm">
                <div class="flex flex-col">
                  <span class="font-medium text-stone-900" x-text="schedule.package_name.split(' — ')[0]"></span>
                  <span class="text-xs text-stone-500" x-text="schedule.variant_name"></span>
                </div>
              </x-backdoor.table.cell>

              <x-backdoor.table.cell
                class="text-sm text-stone-700"
                x-text="schedule.background_name"
              />

              <x-backdoor.table.cell>
                <template x-if="schedule.session_status === 'MENDATANG'">
                  <span class="inline-flex items-center font-bold uppercase tracking-wider select-none px-3 py-1 text-xs bg-stone-100 text-stone-600 border border-stone-300">
                    MENDATANG
                  </span>
                </template>
                <template x-if="schedule.session_status === 'MENUNGGU'">
                  <span class="inline-flex items-center font-bold uppercase tracking-wider select-none px-3 py-1 text-xs bg-amber-50 text-amber-700 border border-amber-300">
                    MENUNGGU
                  </span>
                </template>
                <template x-if="schedule.session_status === 'SEDANG BERLANGSUNG'">
                  <span class="inline-flex items-center font-bold uppercase tracking-wider select-none px-3 py-1 text-xs bg-blue-50 text-blue-700 border border-blue-300">
                    SEDANG BERLANGSUNG
                  </span>
                </template>
                <template x-if="schedule.session_status === 'SELESAI'">
                  <span class="inline-flex items-center font-bold uppercase tracking-wider select-none px-3 py-1 text-xs bg-green-50 text-lime-700 border border-green-300">
                    SELESAI
                  </span>
                </template>
              </x-backdoor.table.cell>

              <x-backdoor.table.actions>
                {{-- Lihat Detail Booking — selalu tampil --}}
                <x-backdoor.table.action-item
                  x-on:click="closeDropdown()"
                  x-bind:href="`{{ route('backdoor.booking-management.show', ':booking_code') }}`
                    .replace(':booking_code', schedule.booking_code)"
                  color="text-blue-600"
                  text="Lihat Detail Booking"
                />

                {{-- Input GDrive & Selesaikan — hanya jika status Lunas (SUCCESS) --}}
                <template x-if="schedule.booking_status === 'Lunas'">
                  <x-backdoor.table.action-item
                    x-on:click="closeDropdown(); openGdriveDialog(schedule.booking_code, schedule.gdrive_link)"
                    color="text-lime-600"
                    text="Input GDrive & Selesaikan"
                  />
                </template>

                {{-- Tandai Lunas — hanya jika status DP Terbayar (DP_PAID) --}}
                <template x-if="schedule.booking_status === 'DP Terbayar'">
                  <x-backdoor.table.action-item
                    x-on:click="closeDropdown()"
                    x-bind:href="`{{ route('backdoor.booking-management.show', ':booking_code') }}`
                      .replace(':booking_code', schedule.booking_code)"
                    color="text-amber-600"
                    text="Tandai Lunas"
                  />
                </template>
              </x-backdoor.table.actions>

            </tr>
          </template>
        </x-backdoor.table.container>

        <x-backdoor.table.pagination />

      </div>
      {{-- table card end --}}

      {{-- dialog gdrive start --}}
      <template x-if="state.gdrive.isOpen">
        <div
          class="fixed inset-0 z-50 flex items-center justify-center bg-stone-900/60"
          x-on:click.self="closeGdriveDialog()"
        >
          <div class="w-full max-w-md border border-stone-300 bg-white">

            <div class="flex items-center justify-between border-b border-stone-300 bg-stone-100 px-5 py-3">
              <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">
                Input GDrive & Selesaikan Sesi
              </h2>
              <button
                type="button"
                x-on:click="closeGdriveDialog()"
                class="text-stone-400 hover:text-stone-700"
              >
                <i class="ri-close-line text-lg leading-none"></i>
              </button>
            </div>

            <div class="space-y-4 p-5">
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-stone-600">
                  Link Google Drive Hasil Foto
                </label>
                <input
                  type="url"
                  x-model="state.gdrive.link"
                  placeholder="https://drive.google.com/..."
                  class="w-full border border-stone-300 px-3 py-2.5 text-sm focus:border-stone-500 focus:outline-none"
                  x-bind:class="state.gdrive.error ? 'border-red-400' : ''"
                >
                <p
                  x-show="state.gdrive.error"
                  x-text="state.gdrive.error"
                  class="mt-1 text-xs text-red-600"
                ></p>
              </div>

              <div class="flex items-center gap-2">
                <input
                  type="checkbox"
                  id="send-wa-sched"
                  x-model="state.gdrive.sendWa"
                  class="h-4 w-4 accent-stone-700"
                >
                <label for="send-wa-sched" class="text-sm text-stone-700">
                  Kirim notifikasi WhatsApp ke klien
                </label>
              </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-stone-300 px-5 py-3">
              <button
                type="button"
                x-on:click="closeGdriveDialog()"
                class="border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-100"
              >
                Batal
              </button>
              <button
                type="button"
                x-on:click="submitGdrive()"
                x-bind:disabled="state.gdrive.isLoading"
                class="bg-stone-800 px-4 py-2 text-sm font-semibold text-stone-50 hover:bg-stone-900 disabled:opacity-50"
              >
                <span x-text="state.gdrive.isLoading ? 'Menyimpan...' : 'Simpan & Selesaikan'"></span>
              </button>
            </div>

          </div>
        </div>
      </template>
      {{-- dialog gdrive end --}}

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
```

---

## 10. Catatan Implementasi

### A. Widget Tanggal "Hari Ini"

Teks tanggal di bawah page header dirender langsung dari PHP (`Carbon::today()`) di Blade — tidak perlu Alpine. Ini lebih *Ponytail* dan tidak ada risiko mismatch timezone browser vs server.

### B. Tidak Ada Halaman Create/Show Baru

Semua aksi berat (buat booking baru, detail lengkap, tandai lunas) diarahkan ke halaman Manajemen Pemesanan yang sudah ada. Ini mengikuti prinsip *Ponytail* — tidak membuat duplikasi CRUD.

### C. Search dengan `ilike`

Query search menggunakan `ilike` (case-insensitive, untuk PostgreSQL/Supabase). Untuk MySQL, ganti `ilike` dengan `like`.

### D. `SessionScheduleIndexData::collect()`

Karena `SessionScheduleIndexData` extend `Spatie\LaravelData\Data`, method `::collect()` tersedia secara otomatis dan akan memanggil `fromModel()` untuk setiap item dalam collection.

### E. Font PDF

Gunakan `DejaVu Sans` sebagai font fallback yang pasti tersedia di Browsershot/Chromium. Hindari Google Fonts di template PDF (membutuhkan koneksi internet saat render).

### F. Sidebar Link

Pastikan link **Daftar Jadwal** di `sidebar-links.blade.php` sudah diarahkan ke `route('backdoor.session-schedule.list')`.

### G. Dependency Spatie Laravel PDF

Sudah terinstall dan terbukti berjalan di `PaymentReceiptController`. Tidak perlu instalasi ulang.
