# Spec: Master Data — Background (Latar Belakang)

**Tanggal:** 2026-06-24  
**Scope:** Backend (Model, Controller, DTO, Service, Repository, FormRequest) + Frontend (JS + Blade Views)  
**Stack:** Laravel 13 · PHP 8.3 · Alpine.js · Tailwind CSS v4 · Axios · FilePond · `spatie/laravel-medialibrary` · `spatie/laravel-data` · Supabase Storage (S3-compatible)

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
14. [Catatan Implementasi Penting](#14-catatan-implementasi-penting)

---

## 1. Ikhtisar Fitur

Halaman **Kelola Background** adalah modul admin untuk mengelola aset gambar latar belakang foto studio yang tersedia untuk dipilih klien saat booking. Fitur ini mencakup:

- **CRUD Background**: Tambah, lihat (tabel), edit, dan hapus data background.
- **Upload Gambar via FilePond**: Input gambar yang interaktif (drag-and-drop) menggunakan pustaka FilePond, disimpan ke **Supabase Storage** via Spatie MediaLibrary.
- **Toggle Status Aktif**: Sakelar instan untuk mengaktifkan/menonaktifkan background tanpa reload halaman.
- **Preview Gambar Modal**: Klik thumbnail di tabel untuk membuka preview gambar full-size dalam modal Alpine.js.
- **Statistik Ringkas**: Stats card yang menampilkan total background aktif.

### Alur Data Gambar

```
User pilih file (FilePond UI)
  → state.pendingImageFile disimpan di Alpine
    → Submit form (Axios FormData)
      → BackgroundController@store (validasi server Laravel)
        → BackgroundService@createBackground
          → $background->addMedia($file)->toMediaCollection('background-image')
            → Spatie MediaLibrary upload ke Supabase Storage (S3)
              → URL gambar tersimpan di tabel `media` (polimorfik)
```

---

## 2. Struktur Direktori File Baru

```
app/Domains/MasterData/
├── DTOs/
│   └── BackgroundData.php              ← BARU
├── Http/
│   ├── Controllers/
│   │   └── BackgroundController.php    ← BARU
│   └── Requests/
│       ├── StoreBackgroundRequest.php  ← BARU
│       └── UpdateBackgroundRequest.php ← BARU
├── Repositories/
│   └── BackgroundRepository.php       ← BARU
└── Services/
    └── BackgroundService.php           ← BARU

routes/backdoor/data-master/
└── background.php                      ← BARU

resources/js/features/master-data/background/
├── Background.js                       ← BARU (entry point Alpine)
├── useState.js                         ← BARU
├── useBackgroundForm.js                ← BARU
└── useBackgroundActions.js             ← BARU

resources/views/backdoor/data-master/background/
└── index.blade.php                     ← BARU

resources/views/components/backdoor/data-master/background/
└── background-drawer-form.blade.php    ← BARU
```

---

## 3. Route

**File:** `routes/backdoor/data-master/background.php`

```php
<?php

use App\Domains\MasterData\Http\Controllers\BackgroundController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('backdoor/data-master/background')
    ->name('backdoor.data-master.background.')
    ->group(function () {
        Route::get('/', [BackgroundController::class, 'index'])->name('index');
        Route::post('/', [BackgroundController::class, 'store'])->name('store');
        Route::post('/{background}', [BackgroundController::class, 'update'])->name('update');
        Route::delete('/{background}', [BackgroundController::class, 'destroy'])->name('destroy');
        Route::patch('/{background}/toggle', [BackgroundController::class, 'toggleActive'])->name('toggle');
    });
```

> **Catatan:** Method update menggunakan `POST` (bukan `PUT/PATCH`) karena request menyertakan file upload (`multipart/form-data`). Browser HTML tidak mendukung `PUT` dengan file. Axios dengan `Content-Type: multipart/form-data` menggunakan `POST` lalu server membedakan create vs update dari ada/tidaknya `{background}` di URL.

---

## 4. Model (sudah ada)

Model `Background` sudah tersedia di `app/Domains/MasterData/Models/Background.php` dan **tidak perlu dimodifikasi**. Ringkasan:

```php
#[Fillable('name', 'description', 'is_active')]
class Background extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('background-image')
            ->singleFile(); // 1 gambar per background — lama otomatis tergantikan
    }

    public function packageVariants(): BelongsToMany
    {
        return $this->belongsToMany(PackageVariant::class);
    }
}
```

Koleksi `background-image` dengan `singleFile()` berarti upload gambar baru saat edit otomatis menggantikan gambar lama — tidak perlu hapus manual.

---

## 5. DTO

**File:** `app/Domains/MasterData/DTOs/BackgroundData.php`

```php
<?php

namespace App\Domains\MasterData\DTOs;

use Spatie\LaravelData\Data;

class BackgroundData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly bool $is_active = true,
    ) {}
}
```

> File upload tidak masuk DTO — file `UploadedFile` diteruskan langsung dari controller ke service karena bukan "data bisnis" melainkan binary resource.

---

## 6. Repository

**File:** `app/Domains/MasterData/Repositories/BackgroundRepository.php`

```php
<?php

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Background;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BackgroundRepository
{
    public function getPaginated(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Background::with('media')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $term = '%' . strtolower($search) . '%';
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$term]);
            });
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Background
    {
        return Background::with('media')->find($id);
    }

    public function create(array $data): Background
    {
        return Background::create($data);
    }

    public function update(Background $background, array $data): Background
    {
        $background->update($data);

        return $background;
    }

    public function delete(Background $background): ?bool
    {
        // Spatie MediaLibrary otomatis menghapus file di storage saat model dihapus
        return $background->delete();
    }
}
```

---

## 7. Service

**File:** `app/Domains/MasterData\Services\BackgroundService.php`

```php
<?php

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\DTOs\BackgroundData;
use App\Domains\MasterData\Models\Background;
use App\Domains\MasterData\Repositories\BackgroundRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;

class BackgroundService
{
    public function __construct(
        protected BackgroundRepository $backgroundRepository,
    ) {}

    public function createBackground(BackgroundData $data, ?UploadedFile $image = null): Background
    {
        $background = $this->backgroundRepository->create([
            'name' => $data->name,
            'description' => $data->description,
            'is_active' => $data->is_active,
        ]);

        if ($image) {
            $background
                ->addMedia($image)
                ->toMediaCollection('background-image');
        }

        return $background->load('media');
    }

    public function updateBackground(int $id, BackgroundData $data, ?UploadedFile $image = null): Background
    {
        $background = $this->findOrFail($id);

        $this->backgroundRepository->update($background, [
            'name' => $data->name,
            'description' => $data->description,
            'is_active' => $data->is_active,
        ]);

        if ($image) {
            // singleFile() pada registerMediaCollections() memastikan gambar lama
            // otomatis diganti saat gambar baru di-upload
            $background
                ->addMedia($image)
                ->toMediaCollection('background-image');
        }

        return $background->load('media');
    }

    public function deleteBackground(int $id): bool
    {
        $background = $this->findOrFail($id);

        return $this->backgroundRepository->delete($background);
    }

    public function toggleActiveStatus(int $id): Background
    {
        $background = $this->findOrFail($id);

        return $this->backgroundRepository->update($background, [
            'is_active' => ! $background->is_active,
        ]);
    }

    private function findOrFail(int $id): Background
    {
        $background = $this->backgroundRepository->findById($id);

        if (! $background) {
            throw new ModelNotFoundException('Background tidak ditemukan.');
        }

        return $background;
    }
}
```

---

## 8. Form Requests

### 8.1 `StoreBackgroundRequest.php`

**File:** `app/Domains/MasterData/Http/Requests/StoreBackgroundRequest.php`

```php
<?php

namespace App\Domains\MasterData\Http\Requests;

use App\Domains\MasterData\DTOs\BackgroundData;
use Illuminate\Foundation\Http\FormRequest;

class StoreBackgroundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:backgrounds,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'image' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }

    public function toDto(): BackgroundData
    {
        return new BackgroundData(
            name: trim($this->validated('name')),
            description: $this->validated('description') ? trim($this->validated('description')) : null,
            is_active: (bool) $this->validated('is_active'),
        );
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama background wajib diisi.',
            'name.max' => 'Nama background maksimal 50 karakter.',
            'name.unique' => 'Nama background sudah terdaftar.',
            'description.max' => 'Deskripsi maksimal 255 karakter.',
            'is_active.required' => 'Status aktif wajib diisi.',
            'image.required' => 'Gambar background wajib diunggah.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format gambar harus JPEG, JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran gambar maksimal 2 MB.',
        ];
    }
}
```

### 8.2 `UpdateBackgroundRequest.php`

**File:** `app/Domains/MasterData/Http/Requests/UpdateBackgroundRequest.php`

```php
<?php

namespace App\Domains\MasterData\Http\Requests;

use App\Domains\MasterData\DTOs\BackgroundData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBackgroundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $backgroundId = $this->route('background')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('backgrounds', 'name')->ignore($backgroundId),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            // Opsional saat update — gambar lama tetap jika tidak dikirim
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }

    public function toDto(): BackgroundData
    {
        return new BackgroundData(
            name: trim($this->validated('name')),
            description: $this->validated('description') ? trim($this->validated('description')) : null,
            is_active: (bool) $this->validated('is_active'),
        );
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama background wajib diisi.',
            'name.max' => 'Nama background maksimal 50 karakter.',
            'name.unique' => 'Nama background sudah terdaftar.',
            'description.max' => 'Deskripsi maksimal 255 karakter.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format gambar harus JPEG, JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran gambar maksimal 2 MB.',
        ];
    }
}
```

---

## 9. Controller

**File:** `app/Domains/MasterData/Http/Controllers/BackgroundController.php`

```php
<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\Http\Requests\StoreBackgroundRequest;
use App\Domains\MasterData\Http\Requests\UpdateBackgroundRequest;
use App\Domains\MasterData\Models\Background;
use App\Domains\MasterData\Repositories\BackgroundRepository;
use App\Domains\MasterData\Services\BackgroundService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BackgroundController extends Controller
{
    public function __construct(
        protected BackgroundService $backgroundService,
        protected BackgroundRepository $backgroundRepository,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $totalActiveBackgrounds = Background::where('is_active', true)->count();

        if ($request->wantsJson()) {
            $search = $request->query('search');
            $limit = $request->query('limit', 10);

            $backgrounds = $this->backgroundRepository->getPaginated($search, (int) $limit);

            $items = $backgrounds->through(fn (Background $bg) => [
                'id' => $bg->id,
                'name' => $bg->name,
                'description' => $bg->description,
                'is_active' => $bg->is_active,
                'image_url' => $bg->getFirstMediaUrl('background-image'),
                'created_at' => $bg->created_at,
            ]);

            return response()->json([
                'data' => $items->items(),
                'current_page' => $backgrounds->currentPage(),
                'last_page' => $backgrounds->lastPage(),
                'total' => $backgrounds->total(),
                'total_active_backgrounds' => $totalActiveBackgrounds,
            ]);
        }

        return view('backdoor.data-master.background.index', [
            'totalActiveBackgrounds' => $totalActiveBackgrounds,
        ]);
    }

    public function store(StoreBackgroundRequest $request): JsonResponse
    {
        $background = $this->backgroundService->createBackground(
            $request->toDto(),
            $request->file('image'),
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Background berhasil ditambahkan.',
            'data' => [
                'id' => $background->id,
                'name' => $background->name,
                'description' => $background->description,
                'is_active' => $background->is_active,
                'image_url' => $background->getFirstMediaUrl('background-image'),
            ],
        ], 201);
    }

    public function update(UpdateBackgroundRequest $request, Background $background): JsonResponse
    {
        $updated = $this->backgroundService->updateBackground(
            $background->id,
            $request->toDto(),
            $request->file('image'),
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Background berhasil diperbarui.',
            'data' => [
                'id' => $updated->id,
                'name' => $updated->name,
                'description' => $updated->description,
                'is_active' => $updated->is_active,
                'image_url' => $updated->getFirstMediaUrl('background-image'),
            ],
        ]);
    }

    public function destroy(Background $background): JsonResponse
    {
        $this->backgroundService->deleteBackground($background->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Background berhasil dihapus.',
        ]);
    }

    public function toggleActive(Background $background): JsonResponse
    {
        $updated = $this->backgroundService->toggleActiveStatus($background->id);
        $totalActiveBackgrounds = Background::where('is_active', true)->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Status background berhasil diperbarui.',
            'data' => $updated,
            'total_active_backgrounds' => $totalActiveBackgrounds,
        ]);
    }
}
```

---

## 10. Frontend — JS Modules

Mengikuti pola **React-style** dari `docs/05-dynamic-loader.md`. Semua state terpusat di `useState.js`, logika form di `useBackgroundForm.js`, dan aksi tabel di `useBackgroundActions.js`.

### 10.1 `useState.js`

**File:** `resources/js/features/master-data/background/useState.js`

```js
export default function useState(Alpine) {
  return Alpine.reactive({
    // Page Stats
    totalActiveBackgrounds: 0,

    // General
    isLoading: false,

    // Drawer & Form
    isDrawerOpen: false,
    isEdit: false,
    backgroundId: null,
    form: {
      name: '',
      description: '',
      is_active: true,
    },
    errors: {},

    // FilePond — file yang dipilih user sebelum disubmit
    pendingImageFile: null,

    // Preview Modal
    isPreviewOpen: false,
    previewImageUrl: '',
    previewImageName: '',
  });
}
```

### 10.2 `useBackgroundForm.js`

**File:** `resources/js/features/master-data/background/useBackgroundForm.js`

```js
import route from '../../../lib/route';
import { Modal, Toast } from '../../../lib/sweetalert';

export default function useBackgroundForm({ state, reload }) {
  const resetForm = () => {
    state.isEdit = false;
    state.backgroundId = null;
    state.form.name = '';
    state.form.description = '';
    state.form.is_active = true;
    state.errors = {};
    state.pendingImageFile = null;
    // Kirim event untuk reset FilePond instance di DOM
    document.dispatchEvent(new CustomEvent('background:reset-filepond'));
  };

  const openDrawer = () => {
    resetForm();
    state.isDrawerOpen = true;
  };

  const openEditDrawer = (bg) => {
    resetForm();
    state.isEdit = true;
    state.backgroundId = bg.id;
    state.form.name = bg.name;
    state.form.description = bg.description ?? '';
    state.form.is_active = bg.is_active;
    state.isDrawerOpen = true;
  };

  const closeDrawer = () => {
    state.isDrawerOpen = false;
    setTimeout(() => resetForm(), 500);
  };

  const submitBackground = async () => {
    state.isLoading = true;
    state.errors = {};

    // Validasi sisi klien: gambar wajib saat create
    if (!state.isEdit && !state.pendingImageFile) {
      state.errors.image = 'Gambar background wajib diunggah.';
      state.isLoading = false;
      return;
    }

    // Gunakan FormData karena ada file upload
    const formData = new FormData();
    formData.append('name', state.form.name);
    formData.append('description', state.form.description ?? '');
    formData.append('is_active', state.form.is_active ? '1' : '0');

    if (state.pendingImageFile) {
      formData.append('image', state.pendingImageFile);
    }

    const url = state.isEdit
      ? route('backdoor.data-master.background.update', state.backgroundId)
      : route('backdoor.data-master.background.store');

    try {
      const response = await window.axios.post(url, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      closeDrawer();
      reload();
      Toast.fire({ icon: 'success', title: response.data.message });

      if (response.data.total_active_backgrounds !== undefined) {
        state.totalActiveBackgrounds = response.data.total_active_backgrounds;
      }
    } catch (error) {
      if (error.response?.status === 422) {
        const errs = error.response.data.errors;
        for (const key in errs) state.errors[key] = errs[key][0];
      } else {
        Modal.fire({
          icon: 'error',
          title: 'Gagal menyimpan background',
          text: error.response?.data?.message ?? 'Terjadi kesalahan server.',
        });
      }
    } finally {
      state.isLoading = false;
    }
  };

  return { openDrawer, openEditDrawer, closeDrawer, submitBackground };
}
```

### 10.3 `useBackgroundActions.js`

**File:** `resources/js/features/master-data/background/useBackgroundActions.js`

```js
import route from '../../../lib/route';
import { Modal, Toast, confirmModal } from '../../../lib/sweetalert';

export default function useBackgroundActions({ state, tableState, reload }) {
  const toggleBackgroundStatus = async (id, currentStatus) => {
    state.isLoading = true;

    // Optimistic update
    const item = tableState.data.find((bg) => bg.id === id);
    if (item) item.is_active = !currentStatus;

    try {
      const response = await window.axios.patch(
        route('backdoor.data-master.background.toggle', id),
      );

      if (response.data.total_active_backgrounds !== undefined) {
        state.totalActiveBackgrounds = response.data.total_active_backgrounds;
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

  const destroyBackground = async (id, name) => {
    const result = await confirmModal(
      'Hapus Background?',
      `Background "${name}" beserta gambarnya akan dihapus permanen dari storage.`,
      'warning',
      'Ya, Hapus',
    );

    if (!result.isConfirmed) return;

    state.isLoading = true;

    try {
      const response = await window.axios.delete(
        route('backdoor.data-master.background.destroy', id),
      );
      reload();
      Toast.fire({ icon: 'success', title: response.data.message });
    } catch (error) {
      Modal.fire({
        icon: 'error',
        title: 'Gagal menghapus background',
        text: error.response?.data?.message ?? 'Terjadi kesalahan server.',
      });
    } finally {
      state.isLoading = false;
    }
  };

  const openImagePreview = (imageUrl, name) => {
    state.previewImageUrl = imageUrl;
    state.previewImageName = name;
    state.isPreviewOpen = true;
  };

  const closeImagePreview = () => {
    state.isPreviewOpen = false;
    state.previewImageUrl = '';
    state.previewImageName = '';
  };

  return { toggleBackgroundStatus, destroyBackground, openImagePreview, closeImagePreview };
}
```

### 10.4 `Background.js` — Entry Point

**File:** `resources/js/features/master-data/background/Background.js`

> Sesuai konvensi `docs/05-dynamic-loader.md`:
> - Nama file PascalCase → nama komponen Alpine
> - Di Blade: `x-data="Background"`
> - Loader: `js-module="master-data/background/Background"`

```js
import useDatatable from '../../../lib/useDatatable';
import useState from './useState';
import useBackgroundForm from './useBackgroundForm';
import useBackgroundActions from './useBackgroundActions';
import route from '../../../lib/route';
import { Toast } from '../../../lib/sweetalert';

export default function Background(Alpine) {
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
  } = useDatatable(Alpine, route('backdoor.data-master.background.index'), {
    onSuccess: (res) => {
      if (res.total_active_backgrounds !== undefined) {
        state.totalActiveBackgrounds = res.total_active_backgrounds;
      }
    },
    onError: () => Toast.fire({ icon: 'error', title: 'Gagal memuat data background.' }),
  });

  const init = () => fetch();

  const { openDrawer, openEditDrawer, closeDrawer, submitBackground } = useBackgroundForm({
    state,
    reload,
  });

  const { toggleBackgroundStatus, destroyBackground, openImagePreview, closeImagePreview } =
    useBackgroundActions({ state, tableState, reload });

  return {
    state,
    table: tableState,
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
    submitBackground,

    // Actions
    toggleBackgroundStatus,
    destroyBackground,
    openImagePreview,
    closeImagePreview,
  };
}
```

### 10.5 Setup FilePond di `app.js`

Pastikan baris berikut ada di `resources/js/app.js`:

```js
import * as FilePond from 'filepond';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';

FilePond.registerPlugin(
  FilePondPluginImagePreview,
  FilePondPluginFileValidateType,
  FilePondPluginFileValidateSize,
);

window.FilePond = FilePond; // Ekspor ke global agar bisa diakses di Blade via x-init
```

Install paket jika belum tersedia:

```bash
npm install filepond filepond-plugin-image-preview filepond-plugin-file-validate-type filepond-plugin-file-validate-size
```

---

## 11. Frontend — Blade Views

### Halaman Index

**File:** `resources/views/backdoor/data-master/background/index.blade.php`

```blade
@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard')],
      ['label' => 'Data Master', 'url' => '#'],
      ['label' => 'Background', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Kelola Background"
  :breadcrumbs="$breadcrumbs"
  js-module="master-data/background/Background">

  <x-slot:content>
    <div
      x-data="Background"
      x-init="
        state.totalActiveBackgrounds = {{ $totalActiveBackgrounds }};
        init();
      "
      class="w-full space-y-6">

      {{-- Page Header --}}
      <x-backdoor.shared.page-header title="Kelola Background">
        <x-slot:actions>
          <button
            type="button"
            x-on:click="openDrawer()"
            class="inline-flex items-center gap-2 bg-stone-800 text-stone-50 px-4 py-2 text-sm font-semibold hover:bg-stone-900 transition active:scale-[0.97]">
            <i class="ri-add-line" aria-hidden="true"></i>
            Tambah Background
          </button>
        </x-slot:actions>
      </x-backdoor.shared.page-header>

      {{-- Stats Card --}}
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <x-backdoor.shared.stats-card label="Background Aktif">
          <x-slot:value>
            <span x-text="state.totalActiveBackgrounds">{{ $totalActiveBackgrounds }}</span>
          </x-slot:value>
        </x-backdoor.shared.stats-card>
      </div>

      {{-- Table Section --}}
      <div class="bg-stone-50 border border-stone-200 p-6 space-y-4">

        {{-- Table Controls --}}
        <div class="flex justify-between items-center gap-4">
          <x-backdoor.table.search
            placeholder="Cari nama background..."
            x-on:search="setSearch($event.detail)" />
        </div>

        {{-- Table --}}
        <x-backdoor.table.container>
          <x-slot:head>
            <tr>
              <x-backdoor.table.th>No</x-backdoor.table.th>
              <x-backdoor.table.th>Preview</x-backdoor.table.th>
              <x-backdoor.table.th>Nama Background</x-backdoor.table.th>
              <x-backdoor.table.th>Deskripsi</x-backdoor.table.th>
              <x-backdoor.table.th>Status</x-backdoor.table.th>
              <x-backdoor.table.th class="text-right">Aksi</x-backdoor.table.th>
            </tr>
          </x-slot:head>

          <template x-for="(item, index) in table.data" :key="item.id">
            <tr class="hover:bg-stone-100 border-b border-stone-200 transition">

              {{-- No --}}
              <x-backdoor.table.td
                x-text="(table.pagination.current_page - 1) * table.pagination.per_page + index + 1" />

              {{-- Preview Gambar --}}
              <x-backdoor.table.td>
                <template x-if="item.image_url">
                  <button
                    type="button"
                    x-on:click="openImagePreview(item.image_url, item.name)"
                    class="block w-16 h-16 overflow-hidden border border-stone-200 hover:border-stone-400 transition cursor-zoom-in group"
                    title="Klik untuk preview">
                    <img
                      :src="item.image_url"
                      :alt="'Preview ' + item.name"
                      class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                  </button>
                </template>
                <template x-if="!item.image_url">
                  <div class="w-16 h-16 bg-stone-100 border border-dashed border-stone-300 flex items-center justify-center">
                    <i class="ri-image-line text-stone-400 text-xl" aria-hidden="true"></i>
                  </div>
                </template>
              </x-backdoor.table.td>

              {{-- Nama --}}
              <x-backdoor.table.td>
                <span class="font-semibold text-stone-900 text-sm" x-text="item.name"></span>
              </x-backdoor.table.td>

              {{-- Deskripsi --}}
              <x-backdoor.table.td>
                <span
                  class="text-sm text-stone-600 line-clamp-2 max-w-xs"
                  x-text="item.description || '—'"></span>
              </x-backdoor.table.td>

              {{-- Status Toggle --}}
              <x-backdoor.table.td>
                <x-backdoor.shared.toggle
                  x-bind:checked="item.is_active"
                  x-bind:disabled="state.isLoading"
                  x-on:change="toggleBackgroundStatus(item.id, item.is_active)" />
              </x-backdoor.table.td>

              {{-- Aksi --}}
              <x-backdoor.table.actions>
                <x-backdoor.table.action-item
                  color="text-yellow-600"
                  x-on:click="openEditDrawer(item)"
                  text="Edit" />
                <x-backdoor.table.action-item
                  color="text-red-600"
                  x-bind:disabled="state.isLoading"
                  x-on:click="destroyBackground(item.id, item.name)"
                  text="Hapus" />
              </x-backdoor.table.actions>

            </tr>
          </template>

          {{-- Empty State --}}
          <template x-if="!table.isLoading && table.data.length === 0">
            <tr>
              <td colspan="6" class="py-16 text-center text-stone-400">
                <i class="ri-image-line text-4xl mb-2 block" aria-hidden="true"></i>
                <span class="text-sm">Belum ada data background</span>
              </td>
            </tr>
          </template>

        </x-backdoor.table.container>

        {{-- Pagination --}}
        <x-backdoor.table.pagination />

      </div>

      {{-- Preview Image Modal --}}
      <div
        x-show="state.isPreviewOpen"
        x-on:keydown.escape.window="closeImagePreview()"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/85 p-4"
        x-cloak>
        <div
          x-on:click.self="closeImagePreview()"
          class="relative w-full max-w-3xl">

          <button
            type="button"
            x-on:click="closeImagePreview()"
            class="absolute -top-10 right-0 text-stone-300 hover:text-white transition cursor-pointer"
            aria-label="Tutup preview">
            <i class="ri-close-line text-3xl" aria-hidden="true"></i>
          </button>

          <div class="bg-stone-900 border border-stone-700 overflow-hidden">
            <img
              :src="state.previewImageUrl"
              :alt="'Preview ' + state.previewImageName"
              class="w-full h-auto max-h-[80vh] object-contain">
            <div class="px-4 py-3 text-center border-t border-stone-700">
              <span class="text-stone-300 text-sm font-medium" x-text="state.previewImageName"></span>
            </div>
          </div>

        </div>
      </div>

      {{-- Drawer Form --}}
      <x-backdoor.data-master.background.background-drawer-form />

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
```

---

## 12. Frontend — Komponen Blade Drawer Form

**File:** `resources/views/components/backdoor/data-master/background/background-drawer-form.blade.php`

FilePond diinisialisasi via `x-init` Alpine. Tidak perlu file JS terpisah untuk FilePond karena lifecycle-nya terikat langsung ke elemen DOM di dalam drawer.

```blade
{{--
  * COMPONENT: BACKGROUND DRAWER FORM
  * Drawer sliding panel untuk Create / Edit Background.
  *
  * Terhubung ke Alpine state dari Background.js:
  *   - state.isDrawerOpen, state.isEdit
  *   - state.form.{ name, description, is_active }
  *   - state.errors
  *   - state.pendingImageFile
  *
  * Events yang dipanggil dari luar:
  *   - closeDrawer()
  *   - submitBackground()
  *
  * Custom events yang didengarkan:
  *   - background:reset-filepond (dari useBackgroundForm.js)
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
          aria-labelledby="background-drawer-title"
          x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
          x-transition:enter-start="translate-x-full"
          x-transition:enter-end="translate-x-0"
          x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
          x-transition:leave-start="translate-x-0"
          x-transition:leave-end="translate-x-full"
          class="w-screen max-w-md pointer-events-auto">

          <form
            x-on:submit.prevent="submitBackground"
            class="flex flex-col h-full bg-stone-50 border-l shadow-2xl border-stone-200 overflow-hidden">

            {{-- Header --}}
            <div class="p-4 border-b-2 border-stone-300 bg-stone-200 flex justify-between items-center shrink-0">
              <h2
                class="text-2xl font-bold text-stone-900"
                id="background-drawer-title"
                x-text="state.isEdit ? 'Edit Background' : 'Tambah Background'">
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

              {{-- Input Gambar via FilePond --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-2">
                  Gambar Background
                  <template x-if="!state.isEdit">
                    <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
                  </template>
                </label>

                {{--
                  FilePond diinisialisasi via x-init Alpine.
                  Saat user memilih file → state.pendingImageFile diupdate.
                  Saat drawer di-reset → event 'background:reset-filepond' ditrigger
                  oleh useBackgroundForm.js untuk membersihkan FilePond instance.
                --}}
                <input
                  type="file"
                  accept="image/jpeg,image/jpg,image/png,image/webp"
                  x-init="
                    const pond = window.FilePond.create($el, {
                      allowMultiple: false,
                      acceptedFileTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
                      maxFileSize: '2MB',
                      labelIdle: 'Seret gambar ke sini atau <span class=\'filepond--label-action\'>Pilih File</span>',
                      labelMaxFileSizeExceeded: 'Gambar terlalu besar',
                      labelMaxFileSize: 'Maks. 2 MB',
                      labelFileTypeNotAllowed: 'Format tidak didukung. Gunakan JPEG, PNG, atau WebP.',
                      imagePreviewHeight: 180,
                    });

                    pond.on('addfile', (error, fileItem) => {
                      if (!error) state.pendingImageFile = fileItem.file;
                    });

                    pond.on('removefile', () => {
                      state.pendingImageFile = null;
                    });

                    // Dengarkan event reset dari JS composable
                    document.addEventListener('background:reset-filepond', () => {
                      pond.removeFiles();
                    });
                  ">

                {{-- Hint saat mode edit --}}
                <template x-if="state.isEdit">
                  <p class="text-xs text-stone-500 mt-2 flex items-center gap-1">
                    <i class="ri-information-line shrink-0" aria-hidden="true"></i>
                    Biarkan kosong jika tidak ingin mengganti gambar yang sudah ada.
                  </p>
                </template>

                <small
                  class="text-red-600 text-xs mt-1 block"
                  x-show="state.errors.image"
                  x-text="state.errors.image"></small>
              </div>

              {{-- Nama Background --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-2">
                  Nama Background
                  <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
                </label>
                <input
                  type="text"
                  x-model="state.form.name"
                  placeholder="Contoh: Putih, Hitam, Abstrak Abu"
                  maxlength="50"
                  class="w-full border border-stone-300 bg-white p-3 text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500 text-sm"
                  required>
                <small
                  class="text-red-600 text-xs mt-1 block"
                  x-show="state.errors.name"
                  x-text="state.errors.name"></small>
              </div>

              {{-- Deskripsi --}}
              <div>
                <label class="block text-sm font-semibold text-stone-900 mb-2">Deskripsi</label>
                <textarea
                  x-model="state.form.description"
                  placeholder="Contoh: Latar belakang putih bersih, cocok untuk foto formal atau keluarga..."
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

              {{-- Toggle Status Aktif --}}
              <div class="flex justify-between items-center gap-4">
                <div class="flex flex-col">
                  <span class="text-sm font-semibold text-stone-900">Status Background Aktif</span>
                  <span class="text-xs text-stone-500 mt-1">
                    Jika aktif, background ini bisa dipilih klien saat booking.
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
                    : (state.isEdit ? 'Simpan Perubahan' : 'Simpan Background')"></span>
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

**File:** `routes/backdoor/data-master.php` (atau file aggregator route data master)

```php
require __DIR__ . '/data-master/category.php';
require __DIR__ . '/data-master/package.php';
require __DIR__ . '/data-master/background.php'; // ← tambahkan ini
```

### Sidebar Link

**File:** `resources/views/components/layouts/backdoor/components/sidebar-links.blade.php`

Ganti komentar `{{-- background start --}}` yang sebelumnya tidak punya href:

```blade
{{-- background start --}}
<x-layouts.backdoor.components.sidebar-collapse-link
  :href="route('backdoor.data-master.background.index')"
  :active="request()->routeIs('backdoor.data-master.background.*')"
  title='Background' />
{{-- background end --}}
```

---

## 14. Catatan Implementasi Penting

### FilePond — Tanpa Server-Side Upload

FilePond dikonfigurasi **tanpa server-side chunk upload**. Alasannya sederhana:
- Fitur ini hanya butuh satu upload per submit (bukan multi-part chunk).
- File disimpan sementara di `state.pendingImageFile` (Alpine reactive) sebagai native `File` object.
- Upload file terjadi satu kali saat `submitBackground()` via `FormData`.

### Spatie MediaLibrary & Supabase Storage

Konfigurasi `.env` sudah benar dengan `FILESYSTEM_DISK=s3`. Spatie MediaLibrary otomatis menggunakan disk S3 dari `config/filesystems.php`. Tidak perlu konfigurasi tambahan.

Perilaku `singleFile()` pada koleksi `background-image`:
- Upload gambar baru → gambar lama **otomatis dihapus** dari Supabase dan tabel `media`.
- Delete model Background → **semua file media terkait otomatis dihapus** dari Supabase.

### Validasi Client vs Server

| Validasi | Client (FilePond/JS) | Server (Laravel) |
|---|---|---|
| Tipe file | ✅ FilePond `acceptedFileTypes` | ✅ `mimes:jpeg,jpg,png,webp` |
| Ukuran file (maks 2MB) | ✅ FilePond `maxFileSize` | ✅ `max:2048` |
| Gambar wajib saat create | ✅ `useBackgroundForm.js` | ✅ `required` rule |
| Gambar opsional saat update | ✅ (tidak append jika null) | ✅ `nullable` rule |
| Nama unik | ❌ (tidak perlu) | ✅ `unique:backgrounds,name` |

### Perlu Install npm Package

```bash
npm install filepond \
  filepond-plugin-image-preview \
  filepond-plugin-file-validate-type \
  filepond-plugin-file-validate-size
```

Kemudian jalankan `npm run build` setelah menambahkan route baru (untuk update Ziggy).
