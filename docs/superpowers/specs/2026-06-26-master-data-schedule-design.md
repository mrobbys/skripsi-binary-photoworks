# Spec: Master Data — Schedule (Jadwal Operasional Studio)

**Tanggal:** 2026-06-26
**Scope:** Backend (Controller, DTO, Service, Repository, FormRequest) + Frontend (JS + Blade Views)
**Stack:** Laravel 13 · PHP 8.3 · Alpine.js · Tailwind CSS v4 · Axios · Zod · Flatpickr · `spatie/laravel-data`

---

## Daftar Isi

1. [Ikhtisar Fitur](#1-ikhtisar-fitur)
2. [Struktur Direktori File Baru](#2-struktur-direktori-file-baru)
3. [Route](#3-route)
4. [Model & Enum (sudah ada)](#4-model--enum-sudah-ada)
5. [DTO](#5-dto)
6. [Repository](#6-repository)
7. [Service](#7-service)
8. [Form Request](#8-form-request)
9. [Controller](#9-controller)
10. [Frontend — JS Modules](#10-frontend--js-modules)
11. [Frontend — Blade View Index](#11-frontend--blade-view-index)
12. [Registrasi Route & Sidebar](#12-registrasi-route--sidebar)
13. [Seeder (sudah ada)](#13-seeder-sudah-ada)
14. [Catatan Implementasi Penting](#14-catatan-implementasi-penting)

---

## 1. Ikhtisar Fitur

Halaman **Jadwal Operasional Studio** adalah modul admin untuk mengelola jam operasional studio setiap hari dalam seminggu. Berbeda dengan modul lain (Addon, Background), modul ini **tidak memiliki Drawer/Modal Form** karena:

- Data schedule bersifat **statis 7 baris** (Senin–Minggu), bukan data yang bisa ditambah atau dihapus.
- Semua perubahan dilakukan **langsung di baris tabel** (*inline editing*) via Flatpickr time picker.
- Setiap perubahan (blur pada time input, atau toggle status) langsung **auto-save** ke server via Axios `PATCH` tanpa reload halaman.

### Fitur yang dicakup

- **Tabel Inline 7 Baris**: Menampilkan jadwal statis Senin–Minggu. Tidak ada pagination, search, atau tombol tambah.
- **Inline Time Picker (Flatpickr)**: Kolom Jam Buka dan Jam Tutup menggunakan Flatpickr mode `noCalendar: true` agar konsisten di semua browser/device.
- **Auto-Save on Close**: Setiap kali user selesai memilih jam dan menutup picker, request `PATCH` langsung dikirim ke server.
- **Toggle Status Aktif**: Sakelar instan per baris — saat digeser, `PATCH` langsung dikirim.
- **Validasi Bisnis**: `end_time` harus lebih besar dari `start_time` di sisi server maupun klien (Zod).
- **Tidak ada Tombol Delete atau Tambah**: 7 baris schedule sudah fixed dari seeder.

### Perbedaan Utama vs Modul Lain

| Aspek | Schedule | Addon | Background |
|---|---|---|---|
| Form Drawer/Modal | Tidak ada | Ada | Ada |
| Pagination | Tidak ada | Ada | Ada |
| Search | Tidak ada | Ada | Ada |
| useDatatable | Tidak pakai | Pakai | Pakai |
| Tambah data | Tidak bisa | Bisa | Bisa |
| Hapus data | Tidak bisa | Bisa | Bisa |
| Time Picker | Flatpickr | N/A | N/A |
| Save pattern | Auto-save on close | Submit form | Submit form |

---

## 2. Struktur Direktori File Baru

```
app/Domains/MasterData/
├── DTOs/
│   └── ScheduleData.php                    <- BARU
├── Http/
│   ├── Controllers/
│   │   └── ScheduleController.php          <- BARU
│   └── Requests/
│       └── UpdateScheduleRequest.php       <- BARU
├── Repositories/
│   └── ScheduleRepository.php              <- BARU
└── Services/
    └── ScheduleService.php                 <- BARU

routes/backdoor/data-master/
└── schedule.php                            <- BARU

resources/js/features/master-data/schedule/
├── Schedule.js                             <- BARU (entry point Alpine)
├── useState.js                             <- BARU
└── useScheduleActions.js                   <- BARU

resources/views/backdoor/data-master/schedule/
└── index.blade.php                         <- BARU
```

Tidak ada drawer-form.blade.php karena tidak ada modal/drawer form.

---

## 3. Route

**File:** `routes/backdoor/data-master/schedule.php`

```php
<?php

use App\Domains\MasterData\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('backdoor/data-master/schedule')
    ->name('backdoor.data-master.schedule.')
    ->group(function () {
        Route::get('/', [ScheduleController::class, 'index'])->name('index');
        Route::patch('/{schedule}', [ScheduleController::class, 'update'])->name('update');
        Route::patch('/{schedule}/toggle', [ScheduleController::class, 'toggleActive'])->name('toggle');
    });
```

Catatan:
- Tidak ada `POST /` (store) karena data schedule tidak bisa ditambah.
- Tidak ada `DELETE /{id}` karena data schedule tidak bisa dihapus.
- Menggunakan `PATCH` untuk update parsial.

---

## 4. Model & Enum (sudah ada)

### 4.1 Model Schedule

File: `app/Domains/MasterData/Models/Schedule.php` — tidak perlu dimodifikasi.

```php
#[Fillable('day', 'start_time', 'end_time', 'is_active')]
class Schedule extends Model
{
    protected function casts(): array
    {
        return [
            'day'       => DayOfWeek::class,
            'is_active' => 'boolean',
        ];
    }
}
```

Kolom tabel `schedules`:
- `day` (int, cast ke enum DayOfWeek, unique 1-7)
- `start_time` (string HH:MM, contoh: "09:00")
- `end_time` (string HH:MM, contoh: "21:00")
- `is_active` (boolean)

### 4.2 Enum DayOfWeek

File: `app/Domains/MasterData/Enums/DayOfWeek.php` — tidak perlu dimodifikasi. Sudah menyediakan method `label()` yang mengembalikan nama hari dalam Bahasa Indonesia.

---

## 5. DTO

**File:** `app/Domains/MasterData/DTOs/ScheduleData.php`

```php
<?php

namespace App\Domains\MasterData\DTOs;

use Spatie\LaravelData\Data;

class ScheduleData extends Data
{
    public function __construct(
        public readonly string $start_time,
        public readonly string $end_time,
        public readonly bool   $is_active,
    ) {}
}
```

`day` tidak masuk DTO karena hari tidak bisa diubah — hanya jam dan status yang bisa di-update.

---

## 6. Repository

**File:** `app/Domains/MasterData/Repositories/ScheduleRepository.php`

```php
<?php

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Schedule;
use Illuminate\Database\Eloquent\Collection;

class ScheduleRepository
{
    public function getAll(): Collection
    {
        return Schedule::query()->orderBy('day')->get();
    }

    public function findById(int $id): ?Schedule
    {
        return Schedule::find($id);
    }

    public function update(Schedule $schedule, array $data): Schedule
    {
        $schedule->update($data);

        return $schedule;
    }
}
```

Tidak menggunakan `getPaginated()` karena data schedule cukup 7 baris — selalu diambil semuanya sekaligus.

---

## 7. Service

**File:** `app/Domains/MasterData/Services/ScheduleService.php`

```php
<?php

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\DTOs\ScheduleData;
use App\Domains\MasterData\Models\Schedule;
use App\Domains\MasterData\Repositories\ScheduleRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ScheduleService
{
    public function __construct(
        protected ScheduleRepository $scheduleRepository,
    ) {}

    public function updateSchedule(int $id, ScheduleData $data): Schedule
    {
        $schedule = $this->findOrFail($id);

        return $this->scheduleRepository->update($schedule, [
            'start_time' => $data->start_time,
            'end_time'   => $data->end_time,
            'is_active'  => $data->is_active,
        ]);
    }

    public function toggleActiveStatus(int $id): Schedule
    {
        $schedule = $this->findOrFail($id);

        return $this->scheduleRepository->update($schedule, [
            'is_active' => ! $schedule->is_active,
        ]);
    }

    private function findOrFail(int $id): Schedule
    {
        $schedule = $this->scheduleRepository->findById($id);

        if (! $schedule) {
            throw new ModelNotFoundException('Jadwal tidak ditemukan.');
        }

        return $schedule;
    }
}
```

---

## 8. Form Request

**File:** `app/Domains/MasterData/Http/Requests/UpdateScheduleRequest.php`

```php
<?php

namespace App\Domains\MasterData\Http\Requests;

use App\Domains\MasterData\DTOs\ScheduleData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
            'is_active'  => ['required', 'boolean'],
        ];
    }

    public function toDto(): ScheduleData
    {
        return new ScheduleData(
            start_time: $this->validated('start_time'),
            end_time:   $this->validated('end_time'),
            is_active:  (bool) $this->validated('is_active'),
        );
    }

    public function messages(): array
    {
        return [
            'start_time.required'    => 'Jam buka wajib diisi.',
            'start_time.date_format' => 'Format jam buka tidak valid (HH:MM).',
            'end_time.required'      => 'Jam tutup wajib diisi.',
            'end_time.date_format'   => 'Format jam tutup tidak valid (HH:MM).',
            'end_time.after'         => 'Jam tutup harus lebih besar dari jam buka.',
            'is_active.required'     => 'Status aktif wajib diisi.',
        ];
    }
}
```

Catatan: Laravel rule `after:start_time` membandingkan dua nilai waktu string. Ini bekerja dengan benar untuk format `H:i` (mis. "09:00" vs "21:00").

---

## 9. Controller

**File:** `app/Domains/MasterData/Http/Controllers/ScheduleController.php`

```php
<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\Http\Requests\UpdateScheduleRequest;
use App\Domains\MasterData\Models\Schedule;
use App\Domains\MasterData\Repositories\ScheduleRepository;
use App\Domains\MasterData\Services\ScheduleService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function __construct(
        protected ScheduleService    $scheduleService,
        protected ScheduleRepository $scheduleRepository,
    ) {}

    public function index(): View|JsonResponse
    {
        if (request()->wantsJson()) {
            $schedules = $this->scheduleRepository->getAll();

            $items = $schedules->map(fn (Schedule $s) => [
                'id'         => $s->id,
                'day'        => $s->day->value,
                'day_label'  => $s->day->label(),
                'start_time' => $s->start_time,
                'end_time'   => $s->end_time,
                'is_active'  => $s->is_active,
            ]);

            return response()->json(['data' => $items]);
        }

        return view('backdoor.data-master.schedule.index');
    }

    public function update(UpdateScheduleRequest $request, Schedule $schedule): JsonResponse
    {
        $updated = $this->scheduleService->updateSchedule($schedule->id, $request->toDto());

        return response()->json([
            'status'  => 'success',
            'message' => 'Jadwal berhasil diperbarui.',
            'data'    => [
                'id'         => $updated->id,
                'start_time' => $updated->start_time,
                'end_time'   => $updated->end_time,
                'is_active'  => $updated->is_active,
            ],
        ]);
    }

    public function toggleActive(Schedule $schedule): JsonResponse
    {
        $updated = $this->scheduleService->toggleActiveStatus($schedule->id);

        return response()->json([
            'status'  => 'success',
            'message' => 'Status jadwal berhasil diperbarui.',
            'data'    => [
                'id'        => $updated->id,
                'is_active' => $updated->is_active,
            ],
        ]);
    }
}
```

Catatan penting:
- Tidak ada method `store()` atau `destroy()`.
- Response JSON `index()` tidak punya `current_page`, `last_page`, atau `total` — data langsung `{ data: [...7 items] }`.
- `day` di-expose sebagai `value` (integer) DAN `label` (string) sekaligus.

---

## 10. Frontend — JS Modules

Karena tidak ada Drawer/Modal Form, tidak ada `useScheduleForm.js`. Hanya 3 file: `Schedule.js`, `useState.js`, dan `useScheduleActions.js`.

### 10.1 `useState.js`

**File:** `resources/js/features/master-data/schedule/useState.js`

```js
export default function useState(Alpine) {
  return Alpine.reactive({
    schedules: [],
    isLoading: false,
    // Set untuk tracking baris mana yang sedang disaving
    savingIds: new Set(),
  });
}
```

`savingIds` menggunakan `Set` untuk pengecekan O(1). Namun karena Alpine hanya reaktif terhadap object re-assignment, setiap mutasi harus membuat instance `Set` baru.

### 10.2 `useScheduleActions.js`

**File:** `resources/js/features/master-data/schedule/useScheduleActions.js`

```js
import route from '../../../lib/route';
import { Toast } from '../../../lib/sweetalert';
import { z } from 'zod';

const scheduleTimeSchema = z
  .object({
    start_time: z.string().regex(/^\d{2}:\d{2}$/, 'Format jam tidak valid.'),
    end_time: z.string().regex(/^\d{2}:\d{2}$/, 'Format jam tidak valid.'),
  })
  .refine((data) => data.end_time > data.start_time, {
    message: 'Jam tutup harus lebih besar dari jam buka.',
    path: ['end_time'],
  });

export default function useScheduleActions({ state }) {
  /**
   * Auto-save saat Flatpickr onClose dipanggil.
   */
  const saveScheduleTime = async (scheduleId, startTime, endTime) => {
    const result = scheduleTimeSchema.safeParse({ start_time: startTime, end_time: endTime });

    if (!result.success) {
      const message = result.error.issues[0]?.message ?? 'Format waktu tidak valid.';
      Toast.fire({ icon: 'error', title: message });
      return;
    }

    state.savingIds = new Set([...state.savingIds, scheduleId]);

    try {
      const item = state.schedules.find((s) => s.id === scheduleId);

      await window.axios.patch(route('backdoor.data-master.schedule.update', scheduleId), {
        start_time: startTime,
        end_time: endTime,
        is_active: item?.is_active ?? true,
      });

      Toast.fire({ icon: 'success', title: 'Jadwal berhasil disimpan.' });
    } catch (error) {
      if (error.response?.status === 422) {
        const firstError = Object.values(error.response.data.errors)[0]?.[0];
        Toast.fire({ icon: 'error', title: firstError ?? 'Validasi gagal.' });
      } else {
        Toast.fire({
          icon: 'error',
          title: error.response?.data?.message ?? 'Terjadi kesalahan server.',
        });
      }
    } finally {
      const next = new Set(state.savingIds);
      next.delete(scheduleId);
      state.savingIds = next;
    }
  };

  /**
   * Toggle status aktif dengan optimistic update.
   */
  const toggleScheduleStatus = async (scheduleId, currentStatus) => {
    const item = state.schedules.find((s) => s.id === scheduleId);
    if (item) item.is_active = !currentStatus;

    state.savingIds = new Set([...state.savingIds, scheduleId]);

    try {
      const response = await window.axios.patch(
        route('backdoor.data-master.schedule.toggle', scheduleId),
      );
      Toast.fire({ icon: 'success', title: response.data.message });
    } catch (error) {
      if (item) item.is_active = currentStatus; // rollback
      Toast.fire({
        icon: 'error',
        title: error.response?.data?.message ?? 'Terjadi kesalahan server.',
      });
    } finally {
      const next = new Set(state.savingIds);
      next.delete(scheduleId);
      state.savingIds = next;
    }
  };

  return { saveScheduleTime, toggleScheduleStatus };
}
```

### 10.3 `Schedule.js` — Entry Point

**File:** `resources/js/features/master-data/schedule/Schedule.js`

Konvensi: nama file PascalCase → nama komponen Alpine. Di Blade: `x-data="Schedule"`. Loader: `js-module="master-data/schedule/Schedule"`.

```js
import axios from 'axios';
import route from '../../../lib/route';
import { Toast } from '../../../lib/sweetalert';
import useState from './useState';
import useScheduleActions from './useScheduleActions';

export default function Schedule(Alpine) {
  const state = useState(Alpine);

  const fetchSchedules = async () => {
    state.isLoading = true;
    try {
      const response = await axios.get(route('backdoor.data-master.schedule.index'));
      state.schedules = response.data.data;
    } catch {
      Toast.fire({ icon: 'error', title: 'Gagal memuat jadwal.' });
    } finally {
      state.isLoading = false;
    }
  };

  const init = () => fetchSchedules();

  const { saveScheduleTime, toggleScheduleStatus } = useScheduleActions({ state });

  return {
    state,
    init,
    saveScheduleTime,
    toggleScheduleStatus,
  };
}
```

---

## 11. Frontend — Blade View Index

**File:** `resources/views/backdoor/data-master/schedule/index.blade.php`

```blade
@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard')],
      ['label' => 'Data Master', 'url' => '#'],
      ['label' => 'Jadwal Operasional', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Jadwal Operasional Studio"
  :breadcrumbs="$breadcrumbs"
  js-module="master-data/schedule/Schedule">

  <x-slot:content>
    <div
      x-data="Schedule"
      class="w-full space-y-6">

      {{-- Page Header --}}
      <x-backdoor.shared.page-header title="Jadwal Operasional Studio" />

      {{-- Table Card --}}
      <div class="bg-stone-50 border border-stone-200 p-6">

        {{-- Loading Skeleton --}}
        <template x-if="state.isLoading">
          <div class="space-y-3">
            <template x-for="i in 7" :key="i">
              <div class="h-14 bg-stone-100 animate-pulse w-full"></div>
            </template>
          </div>
        </template>

        {{-- Table --}}
        <div x-show="!state.isLoading" x-cloak>
          <table class="w-full text-sm border-collapse">
            <thead>
              <tr class="border-b-2 border-stone-200 bg-stone-100">
                <th class="text-left text-xs font-semibold text-stone-500 uppercase tracking-wider px-4 py-3 w-12">No</th>
                <th class="text-left text-xs font-semibold text-stone-500 uppercase tracking-wider px-4 py-3">Hari</th>
                <th class="text-left text-xs font-semibold text-stone-500 uppercase tracking-wider px-4 py-3">Jam Buka</th>
                <th class="text-left text-xs font-semibold text-stone-500 uppercase tracking-wider px-4 py-3">Jam Tutup</th>
                <th class="text-left text-xs font-semibold text-stone-500 uppercase tracking-wider px-4 py-3">Status</th>
              </tr>
            </thead>
            <tbody>
              <template x-for="(item, index) in state.schedules" :key="item.id">
                <tr
                  class="border-b border-stone-200 transition"
                  x-bind:class="state.savingIds.has(item.id) ? 'opacity-60' : 'hover:bg-stone-100'">

                  {{-- No --}}
                  <td class="px-4 py-4 text-stone-500 text-xs"
                      x-text="String(index + 1).padStart(2, '0')"></td>

                  {{-- Hari --}}
                  <td class="px-4 py-4">
                    <span class="font-semibold text-stone-900" x-text="item.day_label"></span>
                  </td>

                  {{-- Jam Buka (Flatpickr) --}}
                  <td class="px-4 py-4">
                    <input
                      type="text"
                      x-bind:id="'start-time-' + item.id"
                      x-bind:value="item.start_time"
                      x-bind:disabled="state.savingIds.has(item.id)"
                      x-init="
                        window.flatpickr($el, {
                          enableTime: true,
                          noCalendar: true,
                          dateFormat: 'H:i',
                          time_24hr: true,
                          defaultDate: item.start_time,
                          onClose(selectedDates, dateStr) {
                            if (dateStr && dateStr !== item.start_time) {
                              item.start_time = dateStr;
                              saveScheduleTime(item.id, item.start_time, item.end_time);
                            }
                          }
                        });
                      "
                      class="w-24 border border-stone-300 bg-white px-3 py-1.5 text-stone-900 text-sm focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 disabled:opacity-50 disabled:cursor-not-allowed" />
                  </td>

                  {{-- Jam Tutup (Flatpickr) --}}
                  <td class="px-4 py-4">
                    <input
                      type="text"
                      x-bind:id="'end-time-' + item.id"
                      x-bind:value="item.end_time"
                      x-bind:disabled="state.savingIds.has(item.id)"
                      x-init="
                        window.flatpickr($el, {
                          enableTime: true,
                          noCalendar: true,
                          dateFormat: 'H:i',
                          time_24hr: true,
                          defaultDate: item.end_time,
                          onClose(selectedDates, dateStr) {
                            if (dateStr && dateStr !== item.end_time) {
                              item.end_time = dateStr;
                              saveScheduleTime(item.id, item.start_time, item.end_time);
                            }
                          }
                        });
                      "
                      class="w-24 border border-stone-300 bg-white px-3 py-1.5 text-stone-900 text-sm focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 disabled:opacity-50 disabled:cursor-not-allowed" />
                  </td>

                  {{-- Status Toggle --}}
                  <td class="px-4 py-4">
                    <x-backdoor.shared.toggle
                      x-bind:checked="item.is_active"
                      x-bind:disabled="state.savingIds.has(item.id)"
                      x-on:change="toggleScheduleStatus(item.id, item.is_active)" />
                  </td>

                </tr>
              </template>
            </tbody>
          </table>
        </div>

      </div>

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
```

Catatan Blade:
- Flatpickr diinisialisasi via `x-init` per elemen input menggunakan `window.flatpickr()`.
- `x-init` inline hanya memanggil `window.flatpickr()` (inisialisasi library) — bukan logika bisnis. Logika bisnis (save, toggle) tetap di file JS terpisah.
- `onClose` digunakan (bukan `onChange`) karena hanya perlu auto-save setelah user selesai memilih dan menutup panel picker.
- Loading skeleton menggunakan 7 baris abu animasi sebelum data datang.

---

## 12. Registrasi Route & Sidebar

### 12.1 Daftarkan Route ke `data-master.php`

**File:** `routes/backdoor/data-master/data-master.php`

```php
<?php

require __DIR__ . '/category.php';
require __DIR__ . '/package.php';
require __DIR__ . '/background.php';
require __DIR__ . '/addon.php';
require __DIR__ . '/schedule.php'; // tambahkan baris ini
```

### 12.2 Tambahkan Link Sidebar

**File:** `resources/views/components/layouts/backdoor/components/sidebar-links.blade.php`

```blade
<x-layouts.backdoor.components.sidebar-collapse-link
  :href="route('backdoor.data-master.schedule.index')"
  :active="request()->routeIs('backdoor.data-master.schedule.*')"
  title='Jadwal Operasional' />
```

### 12.3 Regenerasi Ziggy

Setelah route ditambahkan, wajib menjalankan:

```bash
php artisan ziggy:generate
# atau langsung:
npm run dev
```

---

## 13. Seeder (sudah ada)

File `database/seeders/ScheduleSeeder.php` sudah ada dan tidak perlu dimodifikasi. Seeder ini sudah mengisi 7 baris data untuk hari Senin-Minggu menggunakan `updateOrCreate` (idempotent). Data default: Senin-Sabtu aktif (09:00-21:00), Minggu libur (is_active: false).

---

## 14. Catatan Implementasi Penting

### A. Kenapa Tidak Pakai `useDatatable`?

`useDatatable` dirancang untuk tabel dengan pagination, search, dan fetch dinamis. Schedule hanya memiliki 7 baris statis — menggunakan `useDatatable` justru menambah kompleksitas yang tidak diperlukan. Fetch manual sederhana di `Schedule.js` lebih tepat.

### B. Flatpickr — Import Global vs Per-Komponen

Flatpickr sudah terdaftar di `package.json`. Cara inisialisasi yang benar adalah melalui `window.flatpickr($el, config)` langsung di `x-init`.

Perlu dicek: Pastikan Flatpickr sudah di-import dan diekspos ke `window` di file entry point JS proyek (`resources/js/app.js`). Jika belum, tambahkan:

```js
import flatpickr from 'flatpickr';
window.flatpickr = flatpickr;
```

### C. Auto-Save Pattern: `onClose` vs `onChange`

- `onChange` di Flatpickr terpicu setiap kali user mengklik angka di picker — bisa terpicu berkali-kali sebelum user selesai memilih.
- `onClose` hanya terpicu sekali saat user menutup panel picker (klik di luar atau tekan Enter) — inilah yang kita inginkan untuk auto-save.

### D. Validasi Waktu: Server vs Klien

- Klien (Zod): Memvalidasi format `HH:MM` (regex) dan memastikan `end_time > start_time` (string comparison yang valid untuk format `HH:MM`).
- Server (Laravel): Menggunakan `date_format:H:i` dan `after:start_time`.

### E. `savingIds` Menggunakan `Set`

`Set` JavaScript lebih efisien daripada array untuk pengecekan apakah suatu ID ada. Namun, karena Alpine.js reactive hanya memantau objek/array biasa (bukan `Set`), setiap kali `savingIds` berubah, kita harus meng-assign ulang instance `Set` baru:

```js
state.savingIds = new Set([...state.savingIds, id]); // reaktif
state.savingIds.add(id); // TIDAK reaktif di Alpine
```

### F. Tidak Ada Stats Card

Berbeda dengan modul Addon dan Background yang menampilkan statistik, modul Schedule tidak memerlukan stats card karena jumlahnya selalu 7 dan tidak ada operasi tambah/hapus.
