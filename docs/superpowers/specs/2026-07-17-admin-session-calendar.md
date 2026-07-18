# Spec: Admin Backdoor — Kalender Sesi (Session Calendar)

**Tanggal:** 2026-07-17
**Branch:** `feat/admin-session-schedule`
**Scope:** Backend (Controller, Routes) + Frontend (Alpine.js, Blade View) + FullCalendar v7 Upgrade
**Stack:** Laravel 13 · PHP 8.3 · Alpine.js · Tailwind CSS v4 · FullCalendar v7
**Design System:** ThoughtStream (flat, no rounded corners, no shadows, stone palette, border separators)

---

## Daftar Isi

1. [Aturan Bisnis & Logika Domain](#1-aturan-bisnis--logika-domain)
2. [Struktur Direktori File Baru](#2-struktur-direktori-file-baru)
3. [Package — Upgrade FullCalendar v7](#3-package--upgrade-fullcalendar-v7)
4. [Backend — Routes](#4-backend--routes)
5. [Backend — Controller](#5-backend--controller)
6. [Frontend — JS Modules (Alpine.js)](#6-frontend--js-modules-alpinejs)
7. [Frontend — Blade View](#7-frontend--blade-view)
8. [Modifikasi Sidebar](#8-modifikasi-sidebar)
9. [Catatan Implementasi](#9-catatan-implementasi)

---

## 1. Aturan Bisnis & Logika Domain

### A. Jenis Event di Kalender

| Jenis | Sumber Data | Warna | Keterangan |
|---|---|---|---|
| **Sesi Dikonfirmasi** | Booking `DP_PAID` + `SUCCESS` | `#44403c` (stone-700) | Sesi akan/sedang berjalan |
| **Sesi Selesai** | Booking `DONE` | `#a8a29e` (stone-400) | Historis, sudah diselesaikan admin |
| **Hari Libur** | `schedules.is_active = false` | `#fce7f3` (pink-100) | Background event recurring per hari-of-week |
| **Tersedia** | Hari kerja tanpa booking | Putih (default) | Tidak ada event, slot masih kosong |

Filter query booking: `whereIn('status', [DP_PAID, SUCCESS, DONE])`

### B. Format Tampilan Event per View Mode

| View | Format Event | Keterangan |
|---|---|---|
| **Bulan** (`dayGridMonth`) | `3 Jadwal` | Semua event per hari collapse menjadi ringkasan jumlah via `moreLinkContent` |
| **Minggu** (`timeGridWeek`) | `Wisuda Pkt 1 — Robby S.` | Grid waktu jam per jam, event bar di slot waktu |
| **Hari** (`timeGridDay`) | `Wisuda Pkt 1 — Robby S.` | Grid waktu paling detail, ada `nowIndicator` |
| **List** (`listMonth`) | `Wisuda Pkt 1 — Robby S.` | Daftar semua event bulan berjalan |

### C. Aksi Klik Event

Setiap event booking yang diklik akan membawa pengguna ke halaman detail jadwal:

```
Route: backdoor.session-schedule.list.show
URL:   /backdoor/session-schedule/list/{booking_code}/detail
```

### D. Format Data API (JSON FullCalendar Event Object)

Endpoint `GET /backdoor/session-schedule/calendar/events` mengembalikan array event dalam format FullCalendar standar. FullCalendar secara otomatis mengirimkan param `?start=...&end=...` saat render/navigasi.

```json
[
  {
    "id": "booking-12",
    "title": "Wisuda Pkt 1 — Robby S.",
    "start": "2026-07-17T10:00:00",
    "end": "2026-07-17T11:00:00",
    "url": "/backdoor/session-schedule/list/BPW-ABC123/detail",
    "color": "#44403c",
    "extendedProps": {
      "booking_code": "BPW-ABC123",
      "status": "DP Terbayar"
    }
  },
  {
    "id": "holiday-7",
    "title": "Hari Libur",
    "daysOfWeek": [0],
    "display": "background",
    "color": "#fce7f3",
    "allDay": true
  }
]
```

### E. Konversi Day-of-Week (DB → FullCalendar)

Tabel `schedules` menggunakan format ISO (1=Senin, …, 7=Minggu).
FullCalendar menggunakan format JavaScript (0=Minggu, 1=Senin, …, 6=Sabtu).

**Formula konversi:** `$schedule->day % 7`
- `7 % 7 = 0` → Minggu di FullCalendar ✅
- `1 % 7 = 1` → Senin ✅
- `6 % 7 = 6` → Sabtu ✅

---

## 2. Struktur Direktori File Baru

```
app/Domains/Booking/
└── Http/Controllers/Backdoor/
    └── SessionScheduleCalendarController.php   ← BARU

routes/backdoor/
└── session-schedule.php                        ← MODIFIKASI (tambah 2 route)

resources/js/features/backdoor/session-schedule/
├── index/          (sudah ada — Daftar Jadwal)
└── calendar/
    ├── Calendar.js                             ← BARU
    └── useState.js                             ← BARU

resources/views/backdoor/session-schedule/
├── index.blade.php (sudah ada — Daftar Jadwal)
└── calendar.blade.php                          ← BARU

resources/views/components/layouts/backdoor/components/
└── sidebar-links.blade.php                     ← MODIFIKASI (aktifkan link kalender)
```

---

## 3. Package — Upgrade FullCalendar v7

### 3.1 Perintah Install

```bash
# Hapus v6 lama, pasang v7 (single bundled package)
npm uninstall fullcalendar
npm install fullcalendar@^7
```

### 3.2 Perubahan Import (v6 → v7)

FullCalendar v7 mengkonsolidasikan semua plugin ke dalam satu paket — tidak perlu lagi mengimpor `@fullcalendar/core`, `@fullcalendar/daygrid`, dst. secara terpisah.

```diff
- // v6 — import plugin terpisah
- import { Calendar } from '@fullcalendar/core';
- import dayGridPlugin from '@fullcalendar/daygrid';
- import timeGridPlugin from '@fullcalendar/timegrid';
- import listPlugin from '@fullcalendar/list';
- import interactionPlugin from '@fullcalendar/interaction';
- import idLocale from '@fullcalendar/core/locales/id';

+ // v7 — satu package, satu import
+ import { Calendar } from 'fullcalendar';
+ import idLocale from 'fullcalendar/locales/id';
```

> **Catatan:** CSS FullCalendar juga diimpor dari satu lokasi:
> ```js
> import 'fullcalendar/index.global.css';
> ```

---

## 4. Backend — Routes

### `routes/backdoor/session-schedule.php`

```php
<?php

use App\Domains\Booking\Http\Controllers\Backdoor\ManageBookingController;
use App\Domains\Booking\Http\Controllers\Backdoor\SessionScheduleCalendarController;
use App\Domains\Booking\Http\Controllers\Backdoor\SessionScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
  ->prefix('backdoor/session-schedule')
  ->name('backdoor.session-schedule.')
  ->group(function () {

    // ── Daftar Jadwal (sudah ada) ──────────────────────────────────────────
    Route::get('/list', [SessionScheduleController::class, 'index'])
      ->name('list');

    Route::get('/list/{booking:booking_code}/detail', [ManageBookingController::class, 'show'])
      ->name('list.show');

    // ── Kalender Sesi (BARU) ───────────────────────────────────────────────
    Route::get('/calendar', [SessionScheduleCalendarController::class, 'index'])
      ->name('calendar');

    Route::get('/calendar/events', [SessionScheduleCalendarController::class, 'events'])
      ->name('calendar.events');
  });
```

---

## 5. Backend — Controller

### `app/Domains/Booking/Http/Controllers/Backdoor/SessionScheduleCalendarController.php`

```php
<?php

namespace App\Domains\Booking\Http\Controllers\Backdoor;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\MasterData\Models\Schedule;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SessionScheduleCalendarController extends Controller
{
    /**
     * Halaman Kalender Sesi — hanya render Blade view.
     */
    public function index(): View
    {
        return view('backdoor.session-schedule.calendar');
    }

    /**
     * API Events untuk FullCalendar.
     *
     * FullCalendar mengirim parameter `start` dan `end` secara otomatis
     * berdasarkan rentang tanggal yang sedang ditampilkan di layar.
     *
     * Format response: array of FullCalendar Event Objects (JSON).
     */
    public function events(Request $request): JsonResponse
    {
        $start = $request->input('start');
        $end   = $request->input('end');

        // ── 1. Booking Events ──────────────────────────────────────────────
        $bookings = Booking::with(['user', 'packageVariant.package'])
            ->whereIn('status', [
                BookingStatus::DP_PAID,
                BookingStatus::SUCCESS,
                BookingStatus::DONE,
            ])
            ->when($start, fn ($q) => $q->whereDate('booking_date', '>=', $start))
            ->when($end,   fn ($q) => $q->whereDate('booking_date', '<=', $end))
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get();

        $events = $bookings->map(fn (Booking $booking) => [
            'id'    => 'booking-' . $booking->id,
            'title' => $booking->packageVariant->package->name
                     . ' — '
                     . $booking->user->name,
            'start' => $booking->booking_date->format('Y-m-d') . 'T' . $booking->start_time->format('H:i:s'),
            'end'   => $booking->booking_date->format('Y-m-d') . 'T' . $booking->end_time->format('H:i:s'),
            'url'   => route('backdoor.session-schedule.list.show', $booking->booking_code),
            // DONE → lebih redup (stone-400), aktif → gelap (stone-700)
            'color' => $booking->status === BookingStatus::DONE
                       ? '#a8a29e'
                       : '#44403c',
            'extendedProps' => [
                'booking_code' => $booking->booking_code,
                'status'       => $booking->status->value,
            ],
        ])->toArray();

        // ── 2. Hari Libur (Recurring Background Events) ────────────────────
        // Ambil semua hari di mana studio libur (is_active = false).
        // Dirender sebagai background event recurring per day-of-week.
        //
        // Konversi hari: DB ISO (1=Sen…7=Min) → FullCalendar JS (0=Min…6=Sab)
        // Formula: $schedule->day % 7
        $holidays = Schedule::where('is_active', false)->get();

        foreach ($holidays as $schedule) {
            $fcDow = $schedule->day % 7;

            $events[] = [
                'id'         => 'holiday-' . $schedule->day,
                'title'      => 'Hari Libur',
                'daysOfWeek' => [$fcDow],
                'display'    => 'background',
                'color'      => '#fce7f3',
                'allDay'     => true,
            ];
        }

        return response()->json($events);
    }
}
```

---

## 6. Frontend — JS Modules (Alpine.js)

### `resources/js/features/backdoor/session-schedule/calendar/useState.js`

```javascript
/**
 * State terpusat untuk halaman Kalender Sesi.
 *
 * @param {import('alpinejs').Alpine} Alpine
 * @returns {object}
 */
export default function useState(Alpine) {
  return Alpine.reactive({
    // Label view yang sedang aktif (dipakai untuk tampilan UI jika diperlukan)
    currentViewLabel: 'Bulan',
  });
}
```

### `resources/js/features/backdoor/session-schedule/calendar/Calendar.js`

```javascript
/**
 * Komponen Alpine.js untuk halaman Kalender Sesi.
 *
 * File: resources/js/features/backdoor/session-schedule/calendar/Calendar.js
 * Penggunaan di Blade: <div x-data="Calendar" x-init="init($refs)">...</div>
 *
 * Konfigurasi FullCalendar:
 *  - 4 view: dayGridMonth, timeGridWeek, timeGridDay, listMonth
 *  - Month view: semua event collapse jadi "N Jadwal" (moreLinkContent)
 *  - Week/Day view: timeGrid dengan grid jam + nowIndicator
 *  - Locale: Bahasa Indonesia (id)
 *  - Events: fetch dari route 'backdoor.session-schedule.calendar.events'
 *  - Klik event: redirect ke halaman detail booking
 *
 * @param {import('alpinejs').Alpine} Alpine
 * @returns {object}
 */
import 'fullcalendar/index.global.css';
import { Calendar } from 'fullcalendar';
import idLocale from 'fullcalendar/locales/id';
import route from '@/lib/route';
import useState from './useState';

export default function CalendarPage(Alpine) {
  const state = useState(Alpine);

  // Referensi instance FullCalendar — dipakai jika perlu destroy atau refetch
  let calendarInstance = null;

  // ─── Init ────────────────────────────────────────────────────────────────

  /**
   * Dipanggil via x-init="init($refs)" di Blade.
   * $refs.calendarEl adalah elemen <div x-ref="calendarEl"> di Blade.
   */
  const init = (refs) => {
    const el = refs.calendarEl;

    calendarInstance = new Calendar(el, {

      // ── Locale Bahasa Indonesia ─────────────────────────────────────────
      locale: idLocale,

      // ── Header Toolbar ────────────────────────────────────────────────────
      headerToolbar: {
        left:   'prev,next today',
        center: 'title',
        right:  'dayGridMonth,timeGridWeek,timeGridDay,listMonth',
      },

      // ── Override Label Tombol ─────────────────────────────────────────────
      buttonText: {
        today: 'Hari Ini',
        month: 'Bulan',
        week:  'Minggu',
        day:   'Hari',
        list:  'List',
      },

      // ── Initial View ──────────────────────────────────────────────────────
      initialView: 'dayGridMonth',

      // ── View-specific Config ──────────────────────────────────────────────
      views: {
        dayGridMonth: {
          // dayMaxEvents: 0 → semua event tersembunyi, muncul sebagai "+N more"
          // yang kemudian diubah teksnya via moreLinkContent menjadi "N Jadwal"
          dayMaxEvents: 0,
        },
        timeGridWeek: {
          dayMaxEvents: true,
          // Tampilkan nama hari panjang di header kolom
          dayHeaderFormat: { weekday: 'long', day: 'numeric' },
        },
        timeGridDay: {
          dayMaxEvents: true,
        },
        listMonth: {
          // Tampilkan semua event bulan berjalan dalam format list
          listDayFormat:  { weekday: 'long', day: 'numeric', month: 'long' },
          listDaySideFormat: false,
        },
      },

      // ── More Link (Month View Summary) ────────────────────────────────────
      // Mengubah "+N more" menjadi "N Jadwal"
      moreLinkContent: (args) => `${args.num} Jadwal`,

      // ── Event Source ──────────────────────────────────────────────────────
      // FullCalendar otomatis menambahkan ?start=...&end=... ke URL ini
      events: route('backdoor.session-schedule.calendar.events'),

      // ── Event Click ───────────────────────────────────────────────────────
      // Navigasi ke halaman detail booking
      // `url` di event object sudah di-set oleh backend (route list.show)
      eventClick: (info) => {
        if (info.event.url) {
          info.jsEvent.preventDefault(); // cegah buka tab baru default FullCalendar
          window.location.href = info.event.url;
        }
      },

      // ── View Change Tracking ─────────────────────────────────────────────
      viewDidMount: (mountArg) => {
        const labelMap = {
          dayGridMonth: 'Bulan',
          timeGridWeek: 'Minggu',
          timeGridDay:  'Hari',
          listMonth:    'List',
        };
        state.currentViewLabel = labelMap[mountArg.view.type] ?? '';
      },

      // ── Styling ───────────────────────────────────────────────────────────
      // Override rounded-corners bawaan FullCalendar (ThoughtStream: no rounded)
      eventClassNames: () => ['!rounded-none'],

      // ── Misc ──────────────────────────────────────────────────────────────
      height:       'auto',         // tinggi menyesuaikan konten
      navLinks:     true,           // klik nama hari → masuk ke view hari
      nowIndicator: true,           // garis merah penanda waktu sekarang di timeGrid
    });

    calendarInstance.render();
  };

  // ─── Return ──────────────────────────────────────────────────────────────

  return {
    state,
    init,
  };
}
```

---

## 7. Frontend — Blade View

### `resources/views/backdoor/session-schedule/calendar.blade.php`

```blade
@php
  $breadcrumbs = [
    ['label' => 'Dashboard',     'url' => route('backdoor.dashboard')],
    ['label' => 'Jadwal Sesi',   'url' => ''],
    ['label' => 'Kalender Sesi', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Kalender Sesi"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/session-schedule/calendar/Calendar"
>

  <x-slot:content>
    <div
      x-data="Calendar"
      x-init="init($refs)"
      x-cloak
      class="w-full space-y-6"
    >

      {{-- page header --}}
      <x-backdoor.shared.page-header title="Kalender Sesi" />

      {{-- calendar card --}}
      <div class="border border-stone-200 bg-stone-50 p-6">

        {{-- FullCalendar mount point --}}
        {{-- x-ref="calendarEl" diteruskan via init($refs) ke Calendar.js --}}
        <div x-ref="calendarEl" id="fc-calendar"></div>

        {{-- legend --}}
        <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2 border-t border-stone-300 pt-4">

          {{-- Sesi Dikonfirmasi --}}
          <div class="flex items-center gap-2">
            <span class="inline-block h-3 w-3 bg-stone-700"></span>
            <span class="text-xs font-semibold uppercase tracking-wider text-stone-600">
              Sesi Dikonfirmasi
            </span>
          </div>

          {{-- Sesi Selesai --}}
          <div class="flex items-center gap-2">
            <span class="inline-block h-3 w-3 bg-stone-400"></span>
            <span class="text-xs font-semibold uppercase tracking-wider text-stone-600">
              Sesi Selesai
            </span>
          </div>

          {{-- Tersedia --}}
          <div class="flex items-center gap-2">
            <span class="inline-block h-3 w-3 border border-stone-400 bg-transparent"></span>
            <span class="text-xs font-semibold uppercase tracking-wider text-stone-600">
              Tersedia
            </span>
          </div>

          {{-- Hari Libur --}}
          <div class="flex items-center gap-2">
            <span class="inline-block h-3 w-3 border border-pink-200 bg-pink-100"></span>
            <span class="text-xs font-semibold uppercase tracking-wider text-stone-600">
              Hari Libur
            </span>
          </div>

        </div>
        {{-- legend end --}}

      </div>
      {{-- calendar card end --}}

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
```

---

## 8. Modifikasi Sidebar

### `resources/views/components/layouts/backdoor/components/sidebar-links.blade.php`

Aktifkan link **Kalender Sesi** yang sebelumnya masih placeholder tanpa `href` dan `active`:

```diff
  {{-- kalender sesi start --}}
- <x-layouts.backdoor.components.sidebar-collapse-link title='Kalender' />
+ <x-layouts.backdoor.components.sidebar-collapse-link
+   :href="route('backdoor.session-schedule.calendar')"
+   :active="request()->routeIs('backdoor.session-schedule.calendar')"
+   title='Kalender Sesi'
+ />
  {{-- kalender sesi end --}}
```

---

## 9. Catatan Implementasi

### A. Urutan Langkah Implementasi

1. `npm uninstall fullcalendar && npm install fullcalendar@^7` — upgrade package
2. Tambahkan 2 route baru di `routes/backdoor/session-schedule.php`
3. Buat `SessionScheduleCalendarController.php`
4. Buat `resources/js/features/backdoor/session-schedule/calendar/useState.js`
5. Buat `resources/js/features/backdoor/session-schedule/calendar/Calendar.js`
6. Buat `resources/views/backdoor/session-schedule/calendar.blade.php`
7. Update sidebar link kalender
8. Jalankan `php artisan ziggy:generate` agar route baru terdaftar di Ziggy JS
9. Jalankan `npm run dev` / `npm run build`

### B. `moreLinkContent` di Month View

`dayMaxEvents: 0` di view `dayGridMonth` memaksa **semua** event menjadi overflow sehingga muncul sebagai link "+N more". Fungsi `moreLinkContent` kemudian mengganti teks tersebut menjadi `"N Jadwal"`. Klik pada link ini akan menampilkan **popover** daftar event untuk hari tersebut (fitur bawaan FullCalendar).

### C. `eventClassNames: () => ['!rounded-none']`

FullCalendar secara default menambahkan `border-radius` pada event bar. Karena ThoughtStream melarang rounded corners, kita override dengan Tailwind `!rounded-none` (important modifier). CSS FullCalendar dimuat terlebih dahulu sehingga Tailwind class dapat meng-override-nya.

### D. `navLinks: true`

Konfigurasi ini memungkinkan admin mengklik nama kolom hari di view Minggu atau angka tanggal di view Bulan untuk langsung berpindah ke view Hari pada tanggal tersebut — UX shortcut yang berguna.

### E. Ziggy & Route Baru

Setelah route `backdoor.session-schedule.calendar.events` ditambahkan, wajib jalankan:

```bash
php artisan ziggy:generate
```

Ini memperbarui `resources/js/ziggy.js` sehingga fungsi `route()` di `Calendar.js` dapat menemukan route tersebut.

### F. CSS FullCalendar v7

Import CSS FullCalendar dilakukan di dalam file JS (`Calendar.js`) agar di-bundle oleh Vite:

```js
import 'fullcalendar/index.global.css';
```

Jika path tersebut tidak ditemukan setelah install, cek struktur folder `node_modules/fullcalendar/` untuk menemukan file CSS yang tepat.

### G. Dependency `Schedule` Model

Controller menggunakan `App\Domains\MasterData\Models\Schedule` — model ini sudah ada berdasarkan `ScheduleSeeder.php`. Pastikan relasi sudah benar dan tabel `schedules` sudah terisi data.
