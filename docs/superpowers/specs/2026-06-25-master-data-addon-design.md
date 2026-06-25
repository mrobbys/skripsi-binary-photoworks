# Spec: Master Data — Addon (Layanan Tambahan)

**Tanggal:** 2026-06-25  
**Scope:** Backend (Model, Controller, DTO, Service, Repository, FormRequest) + Frontend (JS + Blade Views) + Seeder  
**Stack:** Laravel 13 · PHP 8.3 · Alpine.js · Tailwind CSS v4 · Axios · Zod · `spatie/laravel-data`

---

## Daftar Isi

1. [Ikhtisar Fitur](#1-ikhtisar-fitur)
2. [Struktur Direktori File Baru](#2-struktur-direktori-file-baru)
3. [Route](#3-route)
4. [Model (sudah ada)](#4-model-sudah-ada)
5. [DTO](#5-dto)
6. [Repository](#6-repository)
7. [Service](#7-service)
8. [Form Requests](#8-form-requests)
9. [Controller](#9-controller)
10. [Frontend — JS Modules](#10-frontend--js-modules)
11. [Frontend — Blade Views](#11-frontend--blade-views)
12. [Frontend — Komponen Blade Drawer Form](#12-frontend--komponen-blade-drawer-form)
13. [Registrasi Route & Sidebar](#13-registrasi-route--sidebar)
14. [Seeder](#14-seeder)
15. [Catatan Implementasi Penting](#15-catatan-implementasi-penting)

---

## 1. Ikhtisar Fitur

Halaman **Kelola Layanan Tambahan (Add-ons)** adalah modul admin untuk mengelola item layanan opsional studio yang bisa dipilih klien saat booking. Tidak seperti Background (ada upload gambar), Addon adalah data murni (no media). Fitur ini mencakup:

- **CRUD Addon**: Tambah, lihat (tabel), edit, dan hapus data add-on.
- **Toggle Status Aktif**: Sakelar instan untuk mengaktifkan/menonaktifkan add-on tanpa reload halaman.
- **Tipe Input (has_quantity)**: Kolom di tabel menampilkan label `Counter (Multi)` jika `has_quantity = true` atau `Checkbox (Single)` jika `false`. Di form drawer, ada pilihan dua tombol card untuk menentukan tipe ini.
- **Statistik Ringkas**: Stats card yang menampilkan total add-on aktif dan total add-on.
- **Harga Rupiah**: Input harga berupa angka integer (dalam Rupiah), ditampilkan di tabel dalam format `Rp 75.000`.

### Perbedaan Utama vs Background & Package

| Aspek | Addon | Background | Package |
|---|---|---|---|
| Upload gambar | ❌ Tidak ada | ✅ FilePond | ❌ Tidak ada |
| Field khusus | `price`, `has_quantity` | `image` | `category_id`, `features` |
| Request method | PUT untuk update | POST (ada file) | PUT untuk update |
| Identifier route | `{addon}` (ID) | `{background}` (ID) | `{slug}` |

---

## 2. Struktur Direktori File Baru

```
app/Domains/MasterData/
├── DTOs/
│   └── AddonData.php                  ← BARU
├── Http/
│   ├── Controllers/
│   │   └── AddonController.php        ← BARU
│   └── Requests/
│       ├── StoreAddonRequest.php      ← BARU
│       └── UpdateAddonRequest.php     ← BARU
├── Repositories/
│   └── AddonRepository.php            ← BARU
└── Services/
    └── AddonService.php               ← BARU

routes/backdoor/data-master/
└── addon.php                          ← BARU

resources/js/features/master-data/addon/
├── Addon.js                           ← BARU (entry point Alpine)
├── useState.js                        ← BARU
├── useAddonForm.js                    ← BARU
└── useAddonActions.js                 ← BARU

resources/views/backdoor/data-master/addon/
└── index.blade.php                    ← BARU

resources/views/components/backdoor/data-master/addon/
└── addon-drawer-form.blade.php        ← BARU

database/seeders/
└── AddonSeeder.php                    ← BARU
```

---

## 3. Route

**File:** `routes/backdoor/data-master/addon.php`

```php
<?php

use App\Domains\MasterData\Http\Controllers\AddonController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('backdoor/data-master/addon')
    ->name('backdoor.data-master.addon.')
    ->group(function () {
        Route::get('/', [AddonController::class, 'index'])->name('index');
        Route::post('/', [AddonController::class, 'store'])->name('store');
        Route::put('/{addon}', [AddonController::class, 'update'])->name('update');
        Route::delete('/{addon}', [AddonController::class, 'destroy'])->name('destroy');
        Route::patch('/{addon}/toggle', [AddonController::class, 'toggleActive'])->name('toggle');
    });
```

> **Catatan:** Update menggunakan `PUT` (bukan `POST`) karena tidak ada file upload — payload JSON murni bisa dikirim via Axios `put()`.

---

## 4. Model (sudah ada)

Model `Addon` sudah tersedia di `app/Domains/MasterData/Models/Addon.php` dan **tidak perlu dimodifikasi**. Ringkasan:

```php
#[Fillable('name', 'price', 'description', 'has_quantity', 'is_active')]
class Addon extends Model
{
    protected function casts(): array
    {
        return [
            'has_quantity' => 'boolean',
            'is_active'    => 'boolean',
        ];
    }
}
```

Tabel `addons` sudah ada dengan kolom:
- `name` (string 100, unique)
- `price` (unsigned integer, nilai Rupiah tanpa desimal)
- `description` (text, wajib)
- `has_quantity` (boolean, default false)
- `is_active` (boolean, default true)

---

## 5. DTO

**File:** `app/Domains/MasterData/DTOs/AddonData.php`

```php
<?php

namespace App\Domains\MasterData\DTOs;

use Spatie\LaravelData\Data;

class AddonData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly int $price,
        public readonly string $description,
        public readonly bool $has_quantity = false,
        public readonly bool $is_active = true,
    ) {}
}
```

> `price` bertipe `int` — disimpan sebagai nilai integer Rupiah (tanpa desimal). Contoh: `75000` = Rp 75.000.

---

## 6. Repository

**File:** `app/Domains/MasterData/Repositories/AddonRepository.php`

```php
<?php

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Addon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AddonRepository
{
    public function getPaginated(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Addon::query()->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $term = '%' . strtolower($search) . '%';
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$term]);
            });
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Addon
    {
        return Addon::find($id);
    }

    public function create(array $data): Addon
    {
        return Addon::create($data);
    }

    public function update(Addon $addon, array $data): Addon
    {
        $addon->update($data);

        return $addon;
    }

    public function delete(Addon $addon): ?bool
    {
        return $addon->delete();
    }
}
```

---

## 7. Service

**File:** `app/Domains/MasterData/Services/AddonService.php`

```php
<?php

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\DTOs\AddonData;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Repositories\AddonRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AddonService
{
    public function __construct(
        protected AddonRepository $addonRepository,
    ) {}

    public function createAddon(AddonData $data): Addon
    {
        return $this->addonRepository->create([
            'name'         => $data->name,
            'price'        => $data->price,
            'description'  => $data->description,
            'has_quantity' => $data->has_quantity,
            'is_active'    => $data->is_active,
        ]);
    }

    public function updateAddon(int $id, AddonData $data): Addon
    {
        $addon = $this->findOrFail($id);

        return $this->addonRepository->update($addon, [
            'name'         => $data->name,
            'price'        => $data->price,
            'description'  => $data->description,
            'has_quantity' => $data->has_quantity,
            'is_active'    => $data->is_active,
        ]);
    }

    public function deleteAddon(int $id): bool
    {
        $addon = $this->findOrFail($id);

        return $this->addonRepository->delete($addon);
    }

    public function toggleActiveStatus(int $id): Addon
    {
        $addon = $this->findOrFail($id);

        return $this->addonRepository->update($addon, [
            'is_active' => ! $addon->is_active,
        ]);
    }

    private function findOrFail(int $id): Addon
    {
        $addon = $this->addonRepository->findById($id);

        if (! $addon) {
            throw new ModelNotFoundException('Add-on tidak ditemukan.');
        }

        return $addon;
    }
}
```

---

## 8. Form Requests

### 8.1 `StoreAddonRequest.php`

**File:** `app/Domains/MasterData/Http/Requests/StoreAddonRequest.php`

```php
<?php

namespace App\Domains\MasterData\Http\Requests;

use App\Domains\MasterData\DTOs\AddonData;
use Illuminate\Foundation\Http\FormRequest;

class StoreAddonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:100', 'unique:addons,name'],
            'price'        => ['required', 'integer', 'min:0'],
            'description'  => ['required', 'string', 'max:255'],
            'has_quantity' => ['required', 'boolean'],
            'is_active'    => ['required', 'boolean'],
        ];
    }

    public function toDto(): AddonData
    {
        return new AddonData(
            name:         trim($this->validated('name')),
            price:        (int) $this->validated('price'),
            description:  trim($this->validated('description')),
            has_quantity: (bool) $this->validated('has_quantity'),
            is_active:    (bool) $this->validated('is_active'),
        );
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Nama add-on wajib diisi.',
            'name.max'              => 'Nama add-on maksimal 100 karakter.',
            'name.unique'           => 'Nama add-on sudah terdaftar.',
            'price.required'        => 'Harga wajib diisi.',
            'price.integer'         => 'Harga harus berupa angka.',
            'price.min'             => 'Harga tidak boleh negatif.',
            'description.required'  => 'Deskripsi wajib diisi.',
            'description.max'       => 'Deskripsi maksimal 255 karakter.',
            'has_quantity.required' => 'Tipe input wajib dipilih.',
            'is_active.required'    => 'Status aktif wajib diisi.',
        ];
    }
}
```

### 8.2 `UpdateAddonRequest.php`

**File:** `app/Domains/MasterData/Http/Requests/UpdateAddonRequest.php`

```php
<?php

namespace App\Domains\MasterData\Http\Requests;

use App\Domains\MasterData\DTOs\AddonData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAddonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $addonId = $this->route('addon')?->id;

        return [
            'name'         => [
                'required',
                'string',
                'max:100',
                Rule::unique('addons', 'name')->ignore($addonId),
            ],
            'price'        => ['required', 'integer', 'min:0'],
            'description'  => ['required', 'string', 'max:255'],
            'has_quantity' => ['required', 'boolean'],
            'is_active'    => ['required', 'boolean'],
        ];
    }

    public function toDto(): AddonData
    {
        return new AddonData(
            name:         trim($this->validated('name')),
            price:        (int) $this->validated('price'),
            description:  trim($this->validated('description')),
            has_quantity: (bool) $this->validated('has_quantity'),
            is_active:    (bool) $this->validated('is_active'),
        );
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Nama add-on wajib diisi.',
            'name.max'              => 'Nama add-on maksimal 100 karakter.',
            'name.unique'           => 'Nama add-on sudah terdaftar.',
            'price.required'        => 'Harga wajib diisi.',
            'price.integer'         => 'Harga harus berupa angka.',
            'price.min'             => 'Harga tidak boleh negatif.',
            'description.required'  => 'Deskripsi wajib diisi.',
            'description.max'       => 'Deskripsi maksimal 255 karakter.',
            'has_quantity.required' => 'Tipe input wajib dipilih.',
            'is_active.required'    => 'Status aktif wajib diisi.',
        ];
    }
}
```

---

## 9. Controller

**File:** `app/Domains/MasterData/Http/Controllers/AddonController.php`

```php
<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\Http\Requests\StoreAddonRequest;
use App\Domains\MasterData\Http\Requests\UpdateAddonRequest;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Repositories\AddonRepository;
use App\Domains\MasterData\Services\AddonService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddonController extends Controller
{
    public function __construct(
        protected AddonService $addonService,
        protected AddonRepository $addonRepository,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $totalAddons       = Addon::count();
        $totalActiveAddons = Addon::where('is_active', true)->count();

        if ($request->wantsJson()) {
            $search = $request->query('search');
            $limit  = max(1, min((int) $request->query('limit', 10), 100));

            $addons = $this->addonRepository->getPaginated($search, $limit);

            $items = $addons->through(fn (Addon $addon) => [
                'id'           => $addon->id,
                'name'         => $addon->name,
                'price'        => $addon->price,
                'description'  => $addon->description,
                'has_quantity' => $addon->has_quantity,
                'is_active'    => $addon->is_active,
                'created_at'   => $addon->created_at,
            ]);

            return response()->json([
                'data'                => $items->items(),
                'current_page'        => $addons->currentPage(),
                'last_page'           => $addons->lastPage(),
                'total'               => $addons->total(),
                'total_addons'        => $totalAddons,
                'total_active_addons' => $totalActiveAddons,
            ]);
        }

        return view('backdoor.data-master.addon.index', [
            'totalAddons'       => $totalAddons,
            'totalActiveAddons' => $totalActiveAddons,
        ]);
    }

    public function store(StoreAddonRequest $request): JsonResponse
    {
        $addon = $this->addonService->createAddon($request->toDto());

        return response()->json([
            'status'  => 'success',
            'message' => 'Add-on berhasil ditambahkan.',
            'data'    => $addon,
        ], 201);
    }

    public function update(UpdateAddonRequest $request, Addon $addon): JsonResponse
    {
        $updated = $this->addonService->updateAddon($addon->id, $request->toDto());

        return response()->json([
            'status'  => 'success',
            'message' => 'Add-on berhasil diperbarui.',
            'data'    => $updated,
        ]);
    }

    public function destroy(Addon $addon): JsonResponse
    {
        $this->addonService->deleteAddon($addon->id);

        return response()->json([
            'status'  => 'success',
            'message' => 'Add-on berhasil dihapus.',
        ]);
    }

    public function toggleActive(Addon $addon): JsonResponse
    {
        $updated           = $this->addonService->toggleActiveStatus($addon->id);
        $totalActiveAddons = Addon::where('is_active', true)->count();

        return response()->json([
            'status'              => 'success',
            'message'             => 'Status add-on berhasil diperbarui.',
            'data'                => $updated,
            'total_active_addons' => $totalActiveAddons,
        ]);
    }
}
```

---

## 10. Frontend — JS Modules

Mengikuti pola **feature-based module** dari `docs/05-dynamic-loader.md`. Semua state terpusat di `useState.js`, logika form di `useAddonForm.js`, dan aksi tabel di `useAddonActions.js`.

### 10.1 `useState.js`

**File:** `resources/js/features/master-data/addon/useState.js`

```js
export default function useState(Alpine) {
  return Alpine.reactive({
    // Page Stats
    totalAddons: 0,
    totalActiveAddons: 0,

    // General
    isLoading: false,

    // Drawer & Form
    isDrawerOpen: false,
    isEdit: false,
    addonId: null,
    form: {
      name: '',
      price: '',
      description: '',
      has_quantity: false,
      is_active: true,
    },
    errors: {},
  });
}
```

### 10.2 `useAddonForm.js`

**File:** `resources/js/features/master-data/addon/useAddonForm.js`

```js
import route from '../../../lib/route';
import { Modal, Toast } from '../../../lib/sweetalert';
import { z } from 'zod';

const addonSchema = z.object({
  name: z
    .string()
    .min(1, 'Nama add-on wajib diisi.')
    .max(100, 'Nama add-on maksimal 100 karakter.'),
  price: z
    .union([z.string(), z.number()])
    .refine(
      (val) => !isNaN(parseInt(val)) && parseInt(val) >= 0,
      'Harga harus berupa angka dan tidak boleh negatif.',
    ),
  description: z
    .string()
    .min(1, 'Deskripsi wajib diisi.')
    .max(255, 'Deskripsi maksimal 255 karakter.'),
  has_quantity: z.boolean(),
  is_active: z.boolean(),
});

export default function useAddonForm({ state, table }) {
  const resetForm = () => {
    state.isEdit = false;
    state.addonId = null;
    state.form.name = '';
    state.form.price = '';
    state.form.description = '';
    state.form.has_quantity = false;
    state.form.is_active = true;
    state.errors = {};
  };

  const openDrawer = () => {
    resetForm();
    state.isDrawerOpen = true;
  };

  const openEditDrawer = (addon) => {
    resetForm();
    state.isEdit = true;
    state.addonId = addon.id;
    state.form.name = addon.name;
    state.form.price = addon.price;
    state.form.description = addon.description ?? '';
    state.form.has_quantity = addon.has_quantity;
    state.form.is_active = addon.is_active;
    state.isDrawerOpen = true;
  };

  const closeDrawer = () => {
    state.isDrawerOpen = false;
    setTimeout(() => resetForm(), 500);
  };

  const submitAddon = async () => {
    state.isLoading = true;
    state.errors = {};

    // Validasi sisi klien dengan Zod
    const validation = addonSchema.safeParse(state.form);
    if (!validation.success) {
      validation.error.issues.forEach((issue) => {
        if (!state.errors[issue.path[0]]) state.errors[issue.path[0]] = issue.message;
      });
      state.isLoading = false;
      return;
    }

    const payload = {
      name:         state.form.name.trim(),
      price:        parseInt(state.form.price, 10),
      description:  state.form.description.trim(),
      has_quantity: state.form.has_quantity,
      is_active:    state.form.is_active,
    };

    const url = state.isEdit
      ? route('backdoor.data-master.addon.update', state.addonId)
      : route('backdoor.data-master.addon.store');

    const method = state.isEdit ? 'put' : 'post';

    try {
      const response = await window.axios[method](url, payload);

      closeDrawer();
      table.reload();
      Toast.fire({ icon: 'success', title: response.data.message });

      if (response.data.total_active_addons !== undefined) {
        state.totalActiveAddons = response.data.total_active_addons;
      }
    } catch (error) {
      if (error.response?.status === 422) {
        const errs = error.response.data.errors;
        for (const key in errs) state.errors[key] = errs[key][0];
      } else {
        Modal.fire({
          icon: 'error',
          title: 'Gagal menyimpan add-on',
          text: error.response?.data?.message ?? 'Terjadi kesalahan server.',
        });
      }
    } finally {
      state.isLoading = false;
    }
  };

  return { openDrawer, openEditDrawer, closeDrawer, submitAddon };
}
```

> **Catatan input price:** `state.form.price` disimpan sebagai `string` di state (agar bisa dipakai di `<input type="number">` Alpine two-way binding), lalu dikonversi ke `parseInt` saat submit.

### 10.3 `useAddonActions.js`

**File:** `resources/js/features/master-data/addon/useAddonActions.js`

```js
import route from '../../../lib/route';
import { Modal, Toast, confirmModal } from '../../../lib/sweetalert';

export default function useAddonActions({ state, table }) {
  const toggleAddonStatus = async (id, currentStatus) => {
    state.isLoading = true;

    // Optimistic update
    const item = table.data.find((a) => a.id === id);
    if (item) item.is_active = !currentStatus;

    try {
      const response = await window.axios.patch(
        route('backdoor.data-master.addon.toggle', id),
      );

      if (response.data.total_active_addons !== undefined) {
        state.totalActiveAddons = response.data.total_active_addons;
      }

      Toast.fire({ icon: 'success', title: response.data.message });
    } catch (error) {
      // Rollback optimistic update
      if (item) item.is_active = currentStatus;

      Toast.fire({
        icon: 'error',
        title: error.response?.data?.message ?? 'Terjadi kesalahan server.',
      });
    } finally {
      state.isLoading = false;
    }
  };

  const destroyAddon = async (id, name) => {
    const result = await confirmModal(
      'Hapus Add-On?',
      `Add-on "${name}" akan dihapus secara permanen.`,
      'warning',
      'Ya, Hapus',
    );

    if (!result.isConfirmed) return;

    state.isLoading = true;

    try {
      const response = await window.axios.delete(
        route('backdoor.data-master.addon.destroy', id),
      );
      table.reload();
      Toast.fire({ icon: 'success', title: response.data.message });
    } catch (error) {
      Modal.fire({
        icon: 'error',
        title: 'Gagal menghapus add-on',
        text: error.response?.data?.message ?? 'Terjadi kesalahan server.',
      });
    } finally {
      state.isLoading = false;
    }
  };

  return { toggleAddonStatus, destroyAddon };
}
```

### 10.4 `Addon.js` — Entry Point

**File:** `resources/js/features/master-data/addon/Addon.js`

> Sesuai konvensi `docs/05-dynamic-loader.md`:
> - Nama file PascalCase → nama komponen Alpine
> - Di Blade: `x-data="Addon"`
> - Loader: `js-module="master-data/addon/Addon"`

```js
import useDatatable from '../../../lib/useDatatable';
import useState from './useState';
import useAddonForm from './useAddonForm';
import useAddonActions from './useAddonActions';
import route from '../../../lib/route';
import { Toast } from '../../../lib/sweetalert';
import formatRupiah from '../../../utils/formatRupiah';

export default function Addon(Alpine) {
  const state = useState(Alpine);

  const {
    state: tableState,
    fetch,
    setSearch,
    nextPage,
    prevPage,
    goToPage,
    reload,
    getPages,
  } = useDatatable(Alpine, route('backdoor.data-master.addon.index'), {
    onSuccess: (res) => {
      if (res.total_addons !== undefined) state.totalAddons = res.total_addons;
      if (res.total_active_addons !== undefined) state.totalActiveAddons = res.total_active_addons;
    },
    onError: () => Toast.fire({ icon: 'error', title: 'Gagal memuat data add-on.' }),
  });

  tableState.fetch = fetch;
  tableState.setSearch = setSearch;
  tableState.nextPage = nextPage;
  tableState.prevPage = prevPage;
  tableState.goToPage = goToPage;
  tableState.reload = reload;
  tableState.getPages = getPages;

  const table = tableState;

  const init = () => fetch();

  const { openDrawer, openEditDrawer, closeDrawer, submitAddon } = useAddonForm({
    state,
    table,
  });

  const { toggleAddonStatus, destroyAddon } = useAddonActions({ state, table });

  return {
    state,
    table,
    init,

    // Datatable
    setSearch,
    nextPage,
    prevPage,
    goToPage,
    getPages,

    // Drawer Form
    openDrawer,
    openEditDrawer,
    closeDrawer,
    submitAddon,

    // Actions
    toggleAddonStatus,
    destroyAddon,
    formatRupiah,
  };
}
```

---

## 11. Frontend — Blade Views

### Halaman Index

**File:** `resources/views/backdoor/data-master/addon/index.blade.php`

```blade
@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard')],
      ['label' => 'Data Master', 'url' => '#'],
      ['label' => 'Layanan Tambahan', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Kelola Layanan Tambahan"
  :breadcrumbs="$breadcrumbs"
  js-module="master-data/addon/Addon">

  <x-slot:content>
    <div
      x-data="Addon"
      x-init="
        state.totalAddons = {{ $totalAddons }};
        state.totalActiveAddons = {{ $totalActiveAddons }};
        init();
      "
      class="w-full space-y-6">

      {{-- Page Header --}}
      <x-backdoor.shared.page-header title="Layanan Tambahan (Add-ons)" />

      {{-- Stats Card --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <x-backdoor.shared.stats-card
          label="Total Add-Ons"
          x-text="state.totalAddons"
          suffix="Item" />
        <x-backdoor.shared.stats-card
          label="Total Add-Ons Aktif"
          x-text="state.totalActiveAddons"
          suffix="Item" />
      </div>

      {{-- Table Card --}}
      <div class="bg-stone-50 border border-stone-200 p-6 relative overflow-visible">

        {{-- Table Header (Search + Add Button) --}}
        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari nama add-on..." />
          </x-slot:left>

          <x-slot:right>
            <x-backdoor.table.add-button
              x-on:click="openDrawer()"
              text="Tambah Add-On" />
          </x-slot:right>
        </x-backdoor.table.header>

        {{-- Table --}}
        <x-backdoor.table.container headers="No,Nama Add-On,Harga,Tipe Input,Deskripsi,Status,Aksi">
          <template
            x-for="(item, index) in table.data"
            x-bind:key="item.id">
            <tr
              class="hover:bg-stone-100 border-b border-stone-200 transition"
              x-show="!table.isLoading"
              x-cloak>

              {{-- No --}}
              <x-backdoor.table.cell
                class="text-stone-600"
                x-text="(table.pagination.current_page - 1) * table.pagination.per_page + index + 1" />

              {{-- Nama Add-On --}}
              <x-backdoor.table.cell
                class="font-semibold text-stone-900"
                x-text="item.name" />

              {{-- Harga --}}
              <x-backdoor.table.cell
                class="text-stone-700 whitespace-nowrap"
                x-text="formatRupiah(item.price)" />

              {{-- Tipe Input --}}
              <x-backdoor.table.cell>
                <span
                  x-text="item.has_quantity ? 'Counter (Multi)' : 'Checkbox (Single)'"
                  x-bind:class="item.has_quantity
                    ? 'bg-blue-100 text-blue-700 border border-blue-200'
                    : 'bg-stone-100 text-stone-600 border border-stone-200'"
                  class="inline-block text-xs font-medium px-2 py-0.5 rounded-sm whitespace-nowrap">
                </span>
              </x-backdoor.table.cell>

              {{-- Deskripsi --}}
              <x-backdoor.table.cell>
                <span
                  x-init="if (item.description) window.tippy($el, { content: item.description, placement: 'top' })"
                  class="text-sm text-stone-600 line-clamp-2 max-w-xs cursor-help"
                  x-text="item.description || '—'">
                </span>
              </x-backdoor.table.cell>

              {{-- Status Toggle --}}
              <x-backdoor.table.cell>
                <x-backdoor.shared.toggle
                  x-bind:checked="item.is_active"
                  x-bind:disabled="state.isLoading"
                  x-on:change="toggleAddonStatus(item.id, item.is_active)" />
              </x-backdoor.table.cell>

              {{-- Aksi --}}
              <x-backdoor.table.actions>
                <x-backdoor.table.action-item
                  color="text-yellow-600"
                  x-on:click="closeDropdown(); openEditDrawer(item)"
                  text="Edit" />
                <x-backdoor.table.action-item
                  color="text-red-600"
                  x-bind:disabled="state.isLoading"
                  x-on:click="closeDropdown(); destroyAddon(item.id, item.name)"
                  text="Hapus" />
              </x-backdoor.table.actions>

            </tr>
          </template>
        </x-backdoor.table.container>

        {{-- Pagination --}}
        <x-backdoor.table.pagination />

      </div>

      {{-- Drawer Form --}}
      <x-backdoor.data-master.addon.addon-drawer-form />

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
```

---

## 12. Frontend — Komponen Blade Drawer Form

**File:** `resources/views/components/backdoor/data-master/addon/addon-drawer-form.blade.php`

Tidak ada FilePond pada form ini karena Addon tidak memiliki gambar. Form ini lebih simpel dari Background.

```blade
{{--
  * COMPONENT: ADDON DRAWER FORM
  * Drawer sliding panel untuk Create / Edit Add-On.
  *
  * Terhubung ke Alpine state dari Addon.js:
  *   - state.isDrawerOpen, state.isEdit
  *   - state.form.{ name, price, description, has_quantity, is_active }
  *   - state.errors
  *
  * Events yang dipanggil dari luar:
  *   - closeDrawer()
  *   - submitAddon()
--}}

<div
  x-show="state.isDrawerOpen"
  x-on:keydown.escape.window="closeDrawer()"
  class="relative z-50"
  x-cloak>

  {{-- Backdrop --}}
  <div
    x-show="state.isDrawerOpen"
    x-transition.opacity.duration.600ms
    x-on:click="closeDrawer()"
    class="fixed inset-0 bg-stone-900/50"
    aria-hidden="true"></div>

  <div class="overflow-hidden fixed inset-0 pointer-events-none">
    <div class="overflow-hidden absolute inset-0">
      <div class="flex fixed inset-y-0 right-0 pl-10 max-w-full">

        {{-- Sliding Panel --}}
        <div
          x-show="state.isDrawerOpen"
          x-on:click.away="closeDrawer()"
          role="dialog"
          aria-modal="true"
          aria-labelledby="addon-drawer-title"
          x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
          x-transition:enter-start="translate-x-full"
          x-transition:enter-end="translate-x-0"
          x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
          x-transition:leave-start="translate-x-0"
          x-transition:leave-end="translate-x-full"
          class="w-screen max-w-md pointer-events-auto">

          <form
            x-on:submit.prevent="submitAddon"
            class="flex flex-col h-full bg-stone-50 border-l shadow-2xl border-stone-200 overflow-hidden">

            {{-- Header --}}
            <div class="p-4 border-b-2 border-stone-300 bg-stone-200 flex justify-between items-center shrink-0">
              <h2
                class="text-2xl font-bold text-stone-900"
                id="addon-drawer-title"
                x-text="state.isEdit ? 'Edit Add-On' : 'Tambah Add-On'">
              </h2>
              <button
                x-on:click="closeDrawer()"
                type="button"
                aria-label="Tutup drawer"
                class="flex items-center px-3 py-1.5 text-stone-600 transition active:scale-[0.97] cursor-pointer hover:text-stone-900">
                <i class="ri-close-line text-2xl" aria-hidden="true"></i>
              </button>
            </div>

            {{-- Body (scrollable) --}}
            <div class="flex-1 overflow-y-auto pt-6 pb-20 px-6 space-y-6">

              {{-- Nama Add-On --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-2">
                  Nama Add-On
                  <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
                </label>
                <input
                  type="text"
                  x-model="state.form.name"
                  placeholder="Contoh: Cetak Foto + Bingkai 10R"
                  maxlength="100"
                  class="w-full border border-stone-300 bg-white p-3 text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 text-sm">
                <small
                  class="text-red-600 text-xs mt-1 block"
                  x-show="state.errors.name"
                  x-text="state.errors.name"></small>
              </div>

              {{-- Harga --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-2">
                  Harga (Rupiah)
                  <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
                </label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-500 text-sm font-medium">Rp</span>
                  <input
                    type="number"
                    x-model="state.form.price"
                    placeholder="75000"
                    min="0"
                    step="1000"
                    class="w-full border border-stone-300 bg-white pl-10 pr-3 py-3 text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 text-sm">
                </div>
                <small class="text-stone-400 text-xs mt-1 block">Masukkan nilai dalam Rupiah, tanpa titik atau koma. Contoh: 75000</small>
                <small
                  class="text-red-600 text-xs mt-1 block"
                  x-show="state.errors.price"
                  x-text="state.errors.price"></small>
              </div>

              {{-- Deskripsi --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-2">
                  Deskripsi
                  <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
                </label>
                <textarea
                  x-model="state.form.description"
                  placeholder="Contoh: Cetak resolusi tinggi termasuk bingkai kayu minimalis..."
                  rows="3"
                  maxlength="255"
                  class="w-full border border-stone-300 bg-white p-3 text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 text-sm resize-none"></textarea>
                <div class="flex justify-between mt-1">
                  <small
                    class="text-red-600 text-xs block"
                    x-show="state.errors.description"
                    x-text="state.errors.description"></small>
                  <small
                    class="text-stone-400 text-xs ml-auto"
                    x-text="(state.form.description?.length ?? 0) + ' / 255'"></small>
                </div>
              </div>

              {{-- Separator --}}
              <div class="h-px bg-stone-200"></div>

              {{-- Tipe Input (has_quantity) --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-3">
                  Tipe Input
                  <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                  {{-- Pilihan: Checkbox (Single) --}}
                  <button
                    type="button"
                    x-on:click="state.form.has_quantity = false"
                    x-bind:class="!state.form.has_quantity
                      ? 'border-stone-700 bg-stone-800 text-stone-50'
                      : 'border-stone-300 bg-white text-stone-700 hover:border-stone-500'"
                    class="flex flex-col items-center gap-2 p-3 border-2 transition cursor-pointer text-left">
                    <i class="ri-checkbox-line text-2xl" aria-hidden="true"></i>
                    <div>
                      <p class="text-xs font-bold">Checkbox (Single)</p>
                      <p class="text-xs opacity-70 mt-0.5">Pilih satu / ya-tidak</p>
                    </div>
                  </button>

                  {{-- Pilihan: Counter (Multi) --}}
                  <button
                    type="button"
                    x-on:click="state.form.has_quantity = true"
                    x-bind:class="state.form.has_quantity
                      ? 'border-stone-700 bg-stone-800 text-stone-50'
                      : 'border-stone-300 bg-white text-stone-700 hover:border-stone-500'"
                    class="flex flex-col items-center gap-2 p-3 border-2 transition cursor-pointer text-left">
                    <i class="ri-add-circle-line text-2xl" aria-hidden="true"></i>
                    <div>
                      <p class="text-xs font-bold">Counter (Multi)</p>
                      <p class="text-xs opacity-70 mt-0.5">Bisa lebih dari satu</p>
                    </div>
                  </button>
                </div>
                <small
                  class="text-red-600 text-xs mt-1 block"
                  x-show="state.errors.has_quantity"
                  x-text="state.errors.has_quantity"></small>
              </div>

              {{-- Separator --}}
              <div class="h-px bg-stone-200"></div>

              {{-- Toggle Status Aktif --}}
              <div class="flex justify-between items-center gap-4">
                <div class="flex flex-col">
                  <span class="text-sm font-semibold text-stone-900">Status Add-On Aktif</span>
                  <span class="text-xs text-stone-500 mt-1">
                    Jika aktif, add-on ini bisa dipilih klien saat booking.
                  </span>
                </div>
                <x-backdoor.shared.toggle
                  class="shrink-0"
                  x-model="state.form.is_active" />
              </div>

            </div>

            {{-- Footer --}}
            <div class="p-4 border-t border-stone-200 bg-stone-100 flex justify-end items-center gap-8 shrink-0">
              <button
                x-on:click="closeDrawer()"
                type="button"
                x-bind:disabled="state.isLoading"
                class="text-stone-600 hover:text-stone-900 transition font-semibold text-sm cursor-pointer disabled:opacity-50">
                Batal
              </button>
              <button
                type="submit"
                x-bind:disabled="state.isLoading"
                class="bg-stone-700 text-stone-50 px-4 py-2 border border-stone-700 hover:bg-stone-800 transition active:scale-[0.97] font-semibold text-sm tracking-wide cursor-pointer disabled:opacity-50 disabled:pointer-events-none max-w-44">
                <span
                  x-text="state.isLoading
                    ? 'Menyimpan...'
                    : (state.isEdit ? 'Simpan Perubahan' : 'Simpan Add-On')"></span>
              </button>
            </div>

          </form>
        </div>

      </div>
    </div>
  </div>
</div>
```

---

## 13. Registrasi Route & Sidebar

### Route Registration

**File:** `routes/backdoor/data-master/data-master.php`

```php
<?php

require __DIR__ . '/category.php';
require __DIR__ . '/package.php';
require __DIR__ . '/background.php';
require __DIR__ . '/addon.php'; // ← tambahkan ini
```

### Sidebar Link

**File:** `resources/views/components/layouts/backdoor/components/sidebar-links.blade.php`

Ganti placeholder sidebar **Layanan Tambahan** dengan href aktif:

```blade
{{-- layanan tambahan start --}}
<x-layouts.backdoor.components.sidebar-collapse-link
  :href="route('backdoor.data-master.addon.index')"
  :active="request()->routeIs('backdoor.data-master.addon.*')"
  title='Layanan Tambahan' />
{{-- layanan tambahan end --}}
```

---

## 14. Seeder

**File:** `database/seeders/AddonSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Domains\MasterData\Models\Addon;
use Illuminate\Database\Seeder;

class AddonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $addons = [
            [
                'name'         => 'Cetak Foto + Bingkai 10R',
                'price'        => 75000,
                'description'  => 'Cetak resolusi tinggi termasuk bingkai kayu minimalis berwarna hitam atau oak. Ukuran 10R (25x30cm).',
                'has_quantity' => true,  // Counter — klien bisa pilih lebih dari 1
                'is_active'    => true,
            ],
            [
                'name'         => 'Extra Time 30 Menit',
                'price'        => 50000,
                'description'  => 'Tambahan waktu pemotretan di dalam studio selama 30 menit untuk eksplorasi gaya lebih bebas.',
                'has_quantity' => false, // Checkbox — hanya sekali saja
                'is_active'    => true,
            ],
            [
                'name'         => 'Sewa Kostum Tambahan',
                'price'        => 35000,
                'description'  => 'Pilihan kostum tematik di luar paket utama (Casual, Traditional, atau Formal). Satu kostum per item.',
                'has_quantity' => true,  // Counter — bisa sewa lebih dari 1 kostum
                'is_active'    => true,
            ],
            [
                'name'         => 'Sewa Studio Lampu Tambahan',
                'price'        => 100000,
                'description'  => 'Penggunaan lighting kit profesional tambahan (Godox/Elinchrom) untuk efek dramatis atau low-key photography.',
                'has_quantity' => false, // Checkbox — set lampu, bukan per unit
                'is_active'    => true,
            ],
            [
                'name'         => 'File Foto Digital',
                'price'        => 0,
                'description'  => 'File foto resolusi tinggi dalam format JPEG dikirim via Google Drive. Termasuk light retouching dasar.',
                'has_quantity' => false, // Checkbox — sudah termasuk atau tidak
                'is_active'    => true,
            ],
        ];

        foreach ($addons as $addon) {
            Addon::create($addon);
        }
    }
}
```

### Daftarkan ke DatabaseSeeder

**File:** `database/seeders/DatabaseSeeder.php` — tambahkan `AddonSeeder::class` ke dalam array `$this->call([...])`:

```php
$this->call([
    PermissionSeeder::class,
    RoleSeeder::class,
    CategorySeeder::class,
    PackageSeeder::class,
    ScheduleSeeder::class,
    BackgroundSeeder::class,
    AddonSeeder::class, // ← tambahkan ini
]);
```

---

## 15. Catatan Implementasi Penting

### Input Price — Handling Integer

`price` disimpan sebagai integer di database. Beberapa hal yang perlu diperhatikan:

| Sisi | Implementasi |
|---|---|
| Form input HTML | `<input type="number" min="0" step="1000">` |
| Alpine state | `state.form.price = ''` (string kosong, bukan null) |
| Submit payload | `parseInt(state.form.price, 10)` — konversi ke integer |
| Display di tabel | `formatRupiah(item.price)` — formatkan ke "Rp 75.000" |
| Server validation | `'price' => ['required', 'integer', 'min:0']` |

### Tipe Input — has_quantity UI

Pilihan tipe input `has_quantity` menggunakan **dua tombol card** (bukan toggle/checkbox tunggal) agar user lebih mudah memahami perbedaan antara `Counter (Multi)` dan `Checkbox (Single)`. Tombol yang aktif diberi styling gelap (stone-800 background), yang tidak aktif diberi border abu.

### Validasi Client vs Server

| Validasi | Client (Zod) | Server (Laravel) |
|---|---|---|
| Nama wajib, maks 100 char | ✅ | ✅ `required\|string\|max:100` |
| Nama unik | ❌ (tidak perlu) | ✅ `unique:addons,name` (ignore saat update) |
| Harga ≥ 0 | ✅ | ✅ `integer\|min:0` |
| Deskripsi wajib, maks 255 | ✅ | ✅ `required\|string\|max:255` |
| has_quantity boolean | ✅ | ✅ `required\|boolean` |

### Tidak Ada FilePond

Addon tidak memiliki upload gambar, sehingga tidak perlu:
- Install/import FilePond
- `state.pendingImageFile`
- `FormData` — cukup kirim JSON payload via `axios.put/post`

Ini membuat implementasi lebih simpel dari Background.

### Optimistic Update pada Toggle

Saat user mengklik toggle status di tabel, state di-update langsung (optimistic) sebelum server merespons. Jika request gagal, state di-rollback ke nilai semula. Pattern ini sama persis dengan Background.
