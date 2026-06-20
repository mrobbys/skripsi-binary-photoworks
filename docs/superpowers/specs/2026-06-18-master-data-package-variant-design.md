# Spec: Master Data — Paket & Varian

**Tanggal:** 2026-06-18  
**Scope:** Backend (Model, Controller, DTO, Service, Repository, FormRequest, Seeder) + Frontend kerangka dasar (JS + Blade)  
**Stack:** Laravel 13 · PHP 8.3 · Alpine.js · Tailwind CSS v4 · Axios · Zod · Choices.js · `spatie/laravel-data` · `spatie/laravel-sluggable`

---

## Daftar Isi

1. [Struktur Direktori File Baru](#1-struktur-direktori-file-baru)
2. [Route](#2-route)
3. [Model](#3-model-sudah-ada--tidak-perlu-dibuat-ulang)
4. [DTOs (spatie/laravel-data)](#4-dtos-spatielaravel-data)
5. [Repository](#5-repository)
6. [Service](#6-service)
7. [Form Requests](#7-form-requests)
8. [Controller — PackageController](#8-controller--packagecontroller)
9. [Controller — PackageVariantController](#9-controller--packagevariantcontroller)
10. [Database Seeder](#10-database-seeder)
11. [Frontend — JS Modules](#11-frontend--js-modules)
12. [Frontend — Blade Views](#12-frontend--blade-views)
13. [Registrasi Alpine.data & Route](#13-registrasi-alpinedata--route)

---

## 1. Struktur Direktori File Baru

```
app/Domains/MasterData/
├── DTOs/
│   ├── PackageData.php          ← BARU
│   └── PackageVariantData.php   ← BARU
├── Http/
│   ├── Controllers/
│   │   ├── PackageController.php         ← BARU
│   │   └── PackageVariantController.php  ← BARU
│   └── Requests/
│       ├── StorePackageRequest.php       ← BARU
│       ├── UpdatePackageRequest.php      ← BARU
│       ├── StorePackageVariantRequest.php   ← BARU
│       └── UpdatePackageVariantRequest.php  ← BARU
├── Repositories/
│   ├── PackageRepository.php         ← BARU
│   └── PackageVariantRepository.php  ← BARU
└── Services/
    ├── PackageService.php         ← BARU
    └── PackageVariantService.php  ← BARU

routes/backdoor/data-master/
└── package.php   ← BARU (di-require di data-master.php)

database/seeders/
└── PackageSeeder.php  ← BARU

resources/js/features/master-data/package/
├── Package.js      ← BARU
├── useState.js     ← BARU
├── useForm.js      ← BARU
└── useActions.js   ← BARU

resources/views/backdoor/data-master/package/
├── index.blade.php   ← BARU
└── show.blade.php    ← BARU
```

---

## 2. Route

**File:** `routes/backdoor/data-master/package.php`

```php
<?php

use App\Domains\MasterData\Http\Controllers\PackageController;
use App\Domains\MasterData\Http\Controllers\PackageVariantController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('backdoor/data-master/package')
    ->name('backdoor.data-master.package.')
    ->group(function () {
        // --- Package Routes ---
        Route::get('/', [PackageController::class, 'index'])->name('index');
        Route::post('/', [PackageController::class, 'store'])->name('store');
        Route::get('/{package:slug}', [PackageController::class, 'show'])->name('show');
        Route::put('/{package:slug}', [PackageController::class, 'update'])->name('update');
        Route::delete('/{package:slug}', [PackageController::class, 'destroy'])->name('destroy');
        Route::patch('/{package:slug}/toggle', [PackageController::class, 'toggleActive'])->name('toggle');

        // --- PackageVariant Routes (nested under package) ---
        Route::post('/{package:slug}/variants', [PackageVariantController::class, 'store'])->name('variants.store');
        Route::put('/{package:slug}/variants/{variant}', [PackageVariantController::class, 'update'])->name('variants.update');
        Route::delete('/{package:slug}/variants/{variant}', [PackageVariantController::class, 'destroy'])->name('variants.destroy');
        Route::patch('/{package:slug}/variants/{variant}/toggle', [PackageVariantController::class, 'toggleActive'])->name('variants.toggle');
    });
```

> **Tambahkan di `data-master.php`:**
>
> ```php
> require __DIR__ . '/category.php';
> require __DIR__ . '/package.php'; // ← tambahkan ini
> ```

---

## 3. Model (sudah ada — tidak perlu dibuat ulang)

Model `Package` dan `PackageVariant` sudah benar di `app/Domains/MasterData/Models/`. Tidak ada perubahan pada model.

**Ringkasan relasi yang ada:**
- `Package` → `belongsTo(Category)`, `hasMany(PackageVariant)`, `morphMany(Feature, 'featureable')`
- `PackageVariant` → `belongsTo(Package)`, `morphMany(Feature, 'featureable')`, `belongsToMany(Background)`

---

## 4. DTOs (spatie/laravel-data)

### 4.1 `PackageData.php`

**File:** `app/Domains/MasterData/DTOs/PackageData.php`

```php
<?php

namespace App\Domains\MasterData\DTOs;

use Spatie\LaravelData\Data;

class PackageData extends Data
{
    public function __construct(
        public readonly int $category_id,
        public readonly string $name,
        public readonly bool $is_active = true,
        /** @var string[] */
        public readonly array $features = [],
    ) {}
}
```

### 4.2 `PackageVariantData.php`

**File:** `app/Domains/MasterData/DTOs/PackageVariantData.php`

```php
<?php

namespace App\Domains\MasterData\DTOs;

use Spatie\LaravelData\Data;

class PackageVariantData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly int $price,
        public readonly int $duration,
        public readonly bool $is_whatsapp_only = false,
        public readonly bool $is_active = true,
        /** @var string[] */
        public readonly array $features = [],
    ) {}
}
```

---

## 5. Repository

### 5.1 `PackageRepository.php`

**File:** `app/Domains/MasterData/Repositories/PackageRepository.php`

```php
<?php

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Package;

class PackageRepository
{
    public function findBySlug(string $slug): ?Package
    {
        return Package::where('slug', $slug)->first();
    }

    public function create(array $data): Package
    {
        return Package::create($data);
    }

    public function update(Package $package, array $data): Package
    {
        $package->update($data);
        return $package;
    }

    public function delete(Package $package): ?bool
    {
        return $package->delete();
    }
}
```

### 5.2 `PackageVariantRepository.php`

**File:** `app/Domains/MasterData/Repositories/PackageVariantRepository.php`

```php
<?php

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Models\PackageVariant;

class PackageVariantRepository
{
    public function create(Package $package, array $data): PackageVariant
    {
        return $package->variants()->create($data);
    }

    public function update(PackageVariant $variant, array $data): PackageVariant
    {
        $variant->update($data);
        return $variant;
    }

    public function delete(PackageVariant $variant): ?bool
    {
        return $variant->delete();
    }
}
```

---

## 6. Service

### 6.1 `PackageService.php`

**File:** `app/Domains/MasterData/Services/PackageService.php`

```php
<?php

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\DTOs\PackageData;
use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Repositories\PackageRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PackageService
{
    public function __construct(
        protected PackageRepository $packageRepository,
    ) {}

    public function createPackage(PackageData $data): Package
    {
        $package = $this->packageRepository->create([
            'category_id' => $data->category_id,
            'name' => $data->name,
            'is_active' => $data->is_active,
        ]);

        $this->syncFeatures($package, $data->features);

        return $package->load('features', 'category');
    }

    public function updatePackage(string $slug, PackageData $data): Package
    {
        $package = $this->findOrFail($slug);

        $this->packageRepository->update($package, [
            'category_id' => $data->category_id,
            'name' => $data->name,
            'is_active' => $data->is_active,
        ]);

        $this->syncFeatures($package, $data->features);

        return $package->load('features', 'category');
    }

    public function deletePackage(string $slug): bool
    {
        $package = $this->findOrFail($slug);
        return $this->packageRepository->delete($package);
    }

    public function toggleActiveStatus(string $slug): Package
    {
        $package = $this->findOrFail($slug);
        return $this->packageRepository->update($package, [
            'is_active' => ! $package->is_active,
        ]);
    }

    private function findOrFail(string $slug): Package
    {
        $package = $this->packageRepository->findBySlug($slug);

        if (! $package) {
            throw new ModelNotFoundException('Paket tidak ditemukan.');
        }

        return $package;
    }

    /**
     * Sinkronisasi features polimorfik: hapus semua, buat ulang dari array baru.
     *
     * @param string[] $featureDescriptions
     */
    private function syncFeatures(Package $package, array $featureDescriptions): void
    {
        $package->features()->delete();

        $featureData = collect($featureDescriptions)
            ->filter(fn (string $desc) => trim($desc) !== '')
            ->map(fn (string $desc) => ['description' => trim($desc)])
            ->toArray();

        if (! empty($featureData)) {
            $package->features()->createMany($featureData);
        }
    }
}
```

### 6.2 `PackageVariantService.php`

**File:** `app/Domains/MasterData/Services/PackageVariantService.php`

```php
<?php

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\DTOs\PackageVariantData;
use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\MasterData\Repositories\PackageRepository;
use App\Domains\MasterData\Repositories\PackageVariantRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PackageVariantService
{
    public function __construct(
        protected PackageRepository $packageRepository,
        protected PackageVariantRepository $variantRepository,
    ) {}

    public function createVariant(string $packageSlug, PackageVariantData $data): PackageVariant
    {
        $package = $this->findPackageOrFail($packageSlug);

        $variant = $this->variantRepository->create($package, [
            'name' => $data->name,
            'price' => $data->price,
            'duration' => $data->duration,
            'is_whatsapp_only' => $data->is_whatsapp_only,
            'is_active' => $data->is_active,
        ]);

        $this->syncFeatures($variant, $data->features);

        return $variant->load('features');
    }

    public function updateVariant(PackageVariant $variant, PackageVariantData $data): PackageVariant
    {
        $this->variantRepository->update($variant, [
            'name' => $data->name,
            'price' => $data->price,
            'duration' => $data->duration,
            'is_whatsapp_only' => $data->is_whatsapp_only,
            'is_active' => $data->is_active,
        ]);

        $this->syncFeatures($variant, $data->features);

        return $variant->load('features');
    }

    public function deleteVariant(PackageVariant $variant): bool
    {
        return $this->variantRepository->delete($variant);
    }

    public function toggleVariantActiveStatus(PackageVariant $variant): PackageVariant
    {
        return $this->variantRepository->update($variant, [
            'is_active' => ! $variant->is_active,
        ]);
    }

    private function findPackageOrFail(string $slug): Package
    {
        $package = $this->packageRepository->findBySlug($slug);

        if (! $package) {
            throw new ModelNotFoundException('Paket tidak ditemukan.');
        }

        return $package;
    }

    /**
     * @param string[] $featureDescriptions
     */
    private function syncFeatures(PackageVariant $variant, array $featureDescriptions): void
    {
        $variant->features()->delete();

        $featureData = collect($featureDescriptions)
            ->filter(fn (string $desc) => trim($desc) !== '')
            ->map(fn (string $desc) => ['description' => trim($desc)])
            ->toArray();

        if (! empty($featureData)) {
            $variant->features()->createMany($featureData);
        }
    }
}
```

---

## 7. Form Requests

### 7.1 `StorePackageRequest.php`

**File:** `app/Domains/MasterData/Http/Requests/StorePackageRequest.php`

```php
<?php

namespace App\Domains\MasterData\Http\Requests;

use App\Domains\MasterData\DTOs\PackageData;
use Illuminate\Foundation\Http\FormRequest;

class StorePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'min:3', 'max:100', 'unique:packages,name'],
            'is_active' => ['required', 'boolean'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:255'],
        ];
    }

    public function toDto(): PackageData
    {
        return new PackageData(
            category_id: (int) $this->validated('category_id'),
            name: trim($this->validated('name')),
            is_active: (bool) $this->validated('is_active'),
            features: $this->validated('features') ?? [],
        );
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak ditemukan.',
            'name.required' => 'Nama paket wajib diisi.',
            'name.min' => 'Nama paket minimal 3 karakter.',
            'name.max' => 'Nama paket maksimal 100 karakter.',
            'name.unique' => 'Nama paket sudah terdaftar.',
            'is_active.required' => 'Status aktif wajib diisi.',
        ];
    }
}
```

### 7.2 `UpdatePackageRequest.php`

**File:** `app/Domains/MasterData/Http/Requests/UpdatePackageRequest.php`

```php
<?php

namespace App\Domains\MasterData\Http\Requests;

use App\Domains\MasterData\DTOs\PackageData;
use App\Domains\MasterData\Models\Package;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $package = Package::where('slug', $this->route('package'))->first();

        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => [
                'required',
                'string',
                'min:3',
                'max:100',
                Rule::unique('packages', 'name')->ignore($package?->id),
            ],
            'is_active' => ['required', 'boolean'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:255'],
        ];
    }

    public function toDto(): PackageData
    {
        return new PackageData(
            category_id: (int) $this->validated('category_id'),
            name: trim($this->validated('name')),
            is_active: (bool) $this->validated('is_active'),
            features: $this->validated('features') ?? [],
        );
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak ditemukan.',
            'name.required' => 'Nama paket wajib diisi.',
            'name.min' => 'Nama paket minimal 3 karakter.',
            'name.unique' => 'Nama paket sudah terdaftar.',
        ];
    }
}
```

### 7.3 `StorePackageVariantRequest.php`

**File:** `app/Domains/MasterData/Http/Requests/StorePackageVariantRequest.php`

```php
<?php

namespace App\Domains\MasterData\Http\Requests;

use App\Domains\MasterData\DTOs\PackageVariantData;
use Illuminate\Foundation\Http\FormRequest;

class StorePackageVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'price' => ['required', 'integer', 'min:1'],
            'duration' => ['required', 'integer', 'min:1'],
            'is_whatsapp_only' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:255'],
        ];
    }

    public function toDto(): PackageVariantData
    {
        return new PackageVariantData(
            name: trim($this->validated('name')),
            price: (int) $this->validated('price'),
            duration: (int) $this->validated('duration'),
            is_whatsapp_only: (bool) $this->validated('is_whatsapp_only'),
            is_active: (bool) $this->validated('is_active'),
            features: $this->validated('features') ?? [],
        );
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama varian wajib diisi.',
            'name.min' => 'Nama varian minimal 3 karakter.',
            'price.required' => 'Harga wajib diisi.',
            'price.integer' => 'Harga harus berupa angka bulat.',
            'price.min' => 'Harga minimal Rp 1.',
            'duration.required' => 'Durasi wajib diisi.',
            'duration.integer' => 'Durasi harus berupa angka bulat (menit).',
            'duration.min' => 'Durasi minimal 1 menit.',
        ];
    }
}
```

### 7.4 `UpdatePackageVariantRequest.php`

**File:** `app/Domains/MasterData/Http/Requests/UpdatePackageVariantRequest.php`

```php
<?php

namespace App\Domains\MasterData\Http\Requests;

// Aturan validasi sama dengan Store — tidak ada unique constraint di variant
class UpdatePackageVariantRequest extends StorePackageVariantRequest
{
    // Semua rules & toDto() di-inherit dari StorePackageVariantRequest
}
```

---

## 8. Controller — PackageController

**File:** `app/Domains/MasterData/Http/Controllers/PackageController.php`

```php
<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\Http\Requests\StorePackageRequest;
use App\Domains\MasterData\Http\Requests\UpdatePackageRequest;
use App\Domains\MasterData\Models\Category;
use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\MasterData\Services\PackageService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function __construct(
        protected PackageService $packageService,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $totalPackages = Package::count();
        $totalActiveVariants = PackageVariant::where('is_active', true)->count();

        if ($request->wantsJson()) {
            $search = $request->query('search');
            $limit = $request->query('limit', 10);

            $query = Package::with(['category', 'variants'])
                ->withCount('variants')
                ->orderBy('created_at', 'desc');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $term = '%' . strtolower($search) . '%';
                    $q->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereHas('category', function ($cq) use ($term) {
                            $cq->whereRaw('LOWER(name) LIKE ?', [$term]);
                        });
                });
            }

            $packages = $query->paginate($limit);

            $items = collect($packages->items())->map(function (Package $package) {
                $activeVariants = $package->variants->where('is_active', true);
                $priceMin = $activeVariants->min('price');
                $priceMax = $activeVariants->max('price');

                return array_merge($package->toArray(), [
                    'price_min' => $priceMin,
                    'price_max' => $priceMax,
                    'variants_count' => $package->variants_count,
                ]);
            });

            return response()->json([
                'data' => $items,
                'current_page' => $packages->currentPage(),
                'last_page' => $packages->lastPage(),
                'total' => $packages->total(),
                'total_packages' => $totalPackages,
                'total_active_variants' => $totalActiveVariants,
            ]);
        }

        $categories = Category::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('backdoor.data-master.package.index', [
            'totalPackages' => $totalPackages,
            'totalActiveVariants' => $totalActiveVariants,
            'categories' => $categories,
        ]);
    }

    public function show(string $slug): View
    {
        $package = Package::with(['category', 'features', 'variants.features'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('backdoor.data-master.package.show', compact('package'));
    }

    public function store(StorePackageRequest $request): JsonResponse
    {
        $package = $this->packageService->createPackage($request->toDto());

        return response()->json([
            'status' => 'success',
            'message' => 'Paket berhasil ditambahkan.',
            'data' => $package,
        ], 201);
    }

    public function update(UpdatePackageRequest $request, string $slug): JsonResponse
    {
        $package = $this->packageService->updatePackage($slug, $request->toDto());

        return response()->json([
            'status' => 'success',
            'message' => 'Paket berhasil diperbarui.',
            'data' => $package,
        ]);
    }

    public function destroy(string $slug): JsonResponse
    {
        $this->packageService->deletePackage($slug);

        return response()->json([
            'status' => 'success',
            'message' => 'Paket berhasil dihapus.',
        ]);
    }

    public function toggleActive(string $slug): JsonResponse
    {
        $package = $this->packageService->toggleActiveStatus($slug);
        $totalActiveVariants = PackageVariant::where('is_active', true)->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Status paket berhasil diperbarui.',
            'data' => $package,
            'total_active_variants' => $totalActiveVariants,
        ]);
    }
}
```

---

## 9. Controller — PackageVariantController

**File:** `app/Domains/MasterData/Http/Controllers/PackageVariantController.php`

```php
<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\Http\Requests\StorePackageVariantRequest;
use App\Domains\MasterData\Http\Requests\UpdatePackageVariantRequest;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\MasterData\Services\PackageVariantService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PackageVariantController extends Controller
{
    public function __construct(
        protected PackageVariantService $variantService,
    ) {}

    public function store(StorePackageVariantRequest $request, string $packageSlug): JsonResponse
    {
        $variant = $this->variantService->createVariant($packageSlug, $request->toDto());

        return response()->json([
            'status' => 'success',
            'message' => 'Varian berhasil ditambahkan.',
            'data' => $variant,
        ], 201);
    }

    public function update(UpdatePackageVariantRequest $request, string $packageSlug, PackageVariant $variant): JsonResponse
    {
        $variant = $this->variantService->updateVariant($variant, $request->toDto());

        return response()->json([
            'status' => 'success',
            'message' => 'Varian berhasil diperbarui.',
            'data' => $variant,
        ]);
    }

    public function destroy(string $packageSlug, PackageVariant $variant): JsonResponse
    {
        $this->variantService->deleteVariant($variant);

        return response()->json([
            'status' => 'success',
            'message' => 'Varian berhasil dihapus.',
        ]);
    }

    public function toggleActive(string $packageSlug, PackageVariant $variant): JsonResponse
    {
        $updated = $this->variantService->toggleVariantActiveStatus($variant);

        return response()->json([
            'status' => 'success',
            'message' => 'Status varian berhasil diperbarui.',
            'data' => $updated,
        ]);
    }
}
```

---

## 10. Database Seeder

**File:** `database/seeders/PackageSeeder.php`

> Data diambil langsung dari PDF harga resmi Binary Photoworks.

```php
<?php

namespace Database\Seeders;

use App\Domains\MasterData\Models\Category;
use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Models\PackageVariant;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    private array $packages = [
        // ─── PERSONAL STUDIO ──────────────────────────────────────────────
        [
            'category_code' => 'PRS',
            'name' => 'Personal Studio',
            'features' => [
                'Tidak ada foto cetak',
                'File foto dikirim melalui link Google Drive',
                'Klien memilih foto yang akan diedit',
                'Hasil foto edit dikirim melalui link Google Drive',
            ],
            'variants' => [
                [
                    'name' => 'Paket 1',
                    'price' => 200000,
                    'duration' => 20,
                    'is_whatsapp_only' => false,
                    'features' => ['Durasi 20 Menit', '1 Background', '1 Outfit', '10 Foto Edit', 'All Original File (Google Drive)'],
                ],
                [
                    'name' => 'Paket 2',
                    'price' => 300000,
                    'duration' => 30,
                    'is_whatsapp_only' => false,
                    'features' => ['Durasi 30 Menit', '1 Background', '2 Outfit', '20 Foto Edit', 'All Original File (Google Drive)'],
                ],
                [
                    'name' => 'Paket 3',
                    'price' => 400000,
                    'duration' => 45,
                    'is_whatsapp_only' => false,
                    'features' => ['Durasi 45 Menit', '2 Background', '2 Outfit', '30 Foto Edit', 'All Original File (Google Drive)'],
                ],
            ],
        ],
        // ─── CUSTOM PHOTOSHOOT STUDIO ──────────────────────────────────────
        [
            'category_code' => 'CST',
            'name' => 'Custom Photoshoot Studio',
            'features' => [
                'Tidak ada foto cetak',
                'Paket berlaku untuk photoshoot dengan konsep pilihan klien',
                'Harga belum termasuk untuk biaya dekorasi',
                'File foto original dikirim melalui link Google Drive',
                'Klien memilih file foto yang akan di edit',
                'File hasil edit dikirim melalui link Google Drive & Flashdisk',
            ],
            'variants' => [
                [
                    'name' => 'Custom',
                    'price' => 1000000,
                    'duration' => 60,
                    'is_whatsapp_only' => true,
                    'features' => ['Durasi Maksimal 1 Jam', '1 Outfit', '1 Background', '30 Foto Edit', 'All Original File', '1 USB Flashdisk (All Files)'],
                ],
            ],
        ],
        // ─── BIRTHDAY STUDIO ──────────────────────────────────────────────
        [
            'category_code' => 'BTD',
            'name' => 'Birthday Studio',
            'features' => [
                'Tidak ada foto cetak',
                'Harga belum termasuk biaya dekorasi dan kue ulang tahun',
                'Tidak menerima vendor dekorasi selain dari rekanan kami',
                'Konsultasi konsep dekorasi dan kue ulang tahun silahkan hubungi admin',
            ],
            'variants' => [
                [
                    'name' => 'Studio',
                    'price' => 1000000,
                    'duration' => 60,
                    'is_whatsapp_only' => true,
                    'features' => ['Durasi 60 Menit', '30 Foto Edit', 'All Original File (Google Drive)'],
                ],
            ],
        ],
        // ─── BIRTHDAY EVENT ────────────────────────────────────────────────
        [
            'category_code' => 'EVT',
            'name' => 'Birthday Event',
            'features' => [
                'Tidak ada foto cetak',
                'Harga belum termasuk biaya transportasi',
                'File hasil edit dikirim melalui link Google Drive & Flashdisk',
            ],
            'variants' => [
                [
                    'name' => 'Foto',
                    'price' => 2000000,
                    'duration' => 240,
                    'is_whatsapp_only' => true,
                    'features' => ['Durasi 4 Jam', '2 Fotografer', '50 Foto Edit', 'All Original File', '1 USB Flashdisk (All Files)'],
                ],
                [
                    'name' => 'Foto & Video',
                    'price' => 4000000,
                    'duration' => 240,
                    'is_whatsapp_only' => true,
                    'features' => ['Durasi 4 Jam', '2 Fotografer', '50 Foto Edit', '1 Videografer', 'Video 1 Menit', 'All Original File', '1 USB Flashdisk (All Files)'],
                ],
            ],
        ],
        // ─── ENGAGEMENT ────────────────────────────────────────────────────
        [
            'category_code' => 'WED',
            'name' => 'Engagement',
            'features' => [
                'Tidak ada foto cetak',
                'Harga belum termasuk biaya transportasi ke luar kota',
                'File hasil edit dikirim melalui link Google Drive & Flashdisk',
            ],
            'variants' => [
                [
                    'name' => 'Foto',
                    'price' => 3500000,
                    'duration' => 240,
                    'is_whatsapp_only' => true,
                    'features' => ['Durasi Maksimal 4 Jam', '2 Fotografer', '1 Album Magazine + Box', 'All Original File', '1 USB Flashdisk (All Files)'],
                ],
                [
                    'name' => 'Foto & Video',
                    'price' => 5500000,
                    'duration' => 240,
                    'is_whatsapp_only' => true,
                    'features' => ['Durasi Maksimal 4 Jam', '2 Fotografer', '1 Videografer', 'Video 1 Menit', '1 Album Magazine + Box', 'All Original File', '1 USB Flashdisk (All Files)'],
                ],
            ],
        ],
        // ─── TRADITIONAL CEREMONY ──────────────────────────────────────────
        [
            'category_code' => 'WED',
            'name' => 'Traditional Ceremony',
            'features' => [
                'Tidak ada foto cetak',
                'Harga belum termasuk biaya transportasi',
                'File hasil edit dikirim melalui link Google Drive & Flashdisk',
            ],
            'variants' => [
                [
                    'name' => 'Foto',
                    'price' => 3500000,
                    'duration' => 240,
                    'is_whatsapp_only' => true,
                    'features' => ['Durasi Maksimal 4 Jam', '2 Fotografer', '1 Album Magazine + Box', 'All Original File', '1 USB Flashdisk (All Files)'],
                ],
                [
                    'name' => 'Foto & Video',
                    'price' => 5500000,
                    'duration' => 240,
                    'is_whatsapp_only' => true,
                    'features' => ['Durasi Maksimal 4 Jam', '2 Fotografer', '1 Videografer', 'Video 1 Menit', '1 Album Magazine + Box', 'All Original File', '1 USB Flashdisk (All Files)'],
                ],
            ],
        ],
        // ─── SYUKURAN ──────────────────────────────────────────────────────
        [
            'category_code' => 'EVT',
            'name' => 'Syukuran',
            'features' => [
                'Tidak ada foto cetak',
                'Harga belum termasuk biaya transportasi',
                'File hasil edit dikirim melalui link Google Drive & Flashdisk',
            ],
            'variants' => [
                [
                    'name' => 'Foto',
                    'price' => 2000000,
                    'duration' => 240,
                    'is_whatsapp_only' => true,
                    'features' => ['Durasi Maksimal 4 Jam', '2 Fotografer', 'All Original File', '1 USB Flashdisk (All Files)'],
                ],
                [
                    'name' => 'Video',
                    'price' => 2500000,
                    'duration' => 240,
                    'is_whatsapp_only' => true,
                    'features' => ['1 Videografer', 'Video 1 Menit'],
                ],
            ],
        ],
        // ─── EVENT LAUNCHING ───────────────────────────────────────────────
        [
            'category_code' => 'EVT',
            'name' => 'Event Launching / Grand Opening',
            'features' => [
                'Tidak ada foto cetak',
                'Harga belum termasuk biaya transportasi',
                'File hasil edit dikirim melalui link Google Drive & Flashdisk',
            ],
            'variants' => [
                [
                    'name' => 'Foto',
                    'price' => 2000000,
                    'duration' => 240,
                    'is_whatsapp_only' => true,
                    'features' => ['Durasi 4 Jam', '2 Fotografer', 'All Original File', '1 USB Flashdisk (All Files)'],
                ],
                [
                    'name' => 'Video',
                    'price' => 2500000,
                    'duration' => 240,
                    'is_whatsapp_only' => true,
                    'features' => ['1 Videografer', 'Video 1 Menit'],
                ],
            ],
        ],
        // ─── COUPLE SESSION STUDIO ─────────────────────────────────────────
        [
            'category_code' => 'CPL',
            'name' => 'Couple Session Studio',
            'features' => [
                'Tidak ada foto cetak',
                'Paket hanya untuk foto studio (indoor)',
                'Klien memilih background yang tersedia di studio',
                'Harga belum termasuk untuk biaya dekorasi',
                'File foto original dikirim melalui link Google Drive',
                'Klien memilih file foto yang akan di edit',
                'File hasil edit dikirim melalui link Google Drive & Flashdisk',
            ],
            'variants' => [
                [
                    'name' => 'Paket 1',
                    'price' => 1000000,
                    'duration' => 60,
                    'is_whatsapp_only' => false,
                    'features' => ['Durasi 1 Jam', 'Indoor Studio', '1 Background', '1 Outfit', '20 Foto Edit', '1 USB Flashdisk (All Files)'],
                ],
            ],
        ],
        // ─── COUPLE SESSION OUTDOOR ────────────────────────────────────────
        [
            'category_code' => 'CPL',
            'name' => 'Couple Session Outdoor',
            'features' => [
                'Tidak ada foto cetak',
                'Hanya berlaku untuk lokasi atau daerah sekitar Kota Banjarbaru',
                'Harga belum termasuk untuk biaya dekorasi',
                'Harga belum termasuk biaya tambahan yang diperlukan untuk lokasi photoshoot',
                'File foto original dikirim melalui link Google Drive',
                'Klien memilih file foto yang akan di edit',
                'File hasil edit dikirim melalui link Google Drive & Flashdisk',
            ],
            'variants' => [
                [
                    'name' => 'Paket 2',
                    'price' => 2000000,
                    'duration' => 180,
                    'is_whatsapp_only' => true,
                    'features' => ['Durasi 3 Jam', '2 Outfit', 'Indoor Studio / Outdoor', 'Maksimal 2 Lokasi Terdekat', '40 Foto Edit', '1 USB Flashdisk (All Files)'],
                ],
                [
                    'name' => 'Paket 3',
                    'price' => 3500000,
                    'duration' => 480,
                    'is_whatsapp_only' => true,
                    'features' => ['1 Hari, Maksimal 8 Jam', '3 Outfit', 'Indoor Studio / Outdoor', 'Maksimal 3 Lokasi', '60 Foto Edit', '1 USB Flashdisk (All Files)'],
                ],
            ],
        ],
        // ─── MATERNITY STUDIO ──────────────────────────────────────────────
        [
            'category_code' => 'MTN',
            'name' => 'Maternity Studio',
            'features' => [
                'Tidak ada foto cetak',
                'Klien memilih foto yang akan di edit',
                'File hasil edit dikirim melalui link Google Drive & Flashdisk',
            ],
            'variants' => [
                [
                    'name' => 'Studio',
                    'price' => 1000000,
                    'duration' => 60,
                    'is_whatsapp_only' => false,
                    'features' => ['Durasi Maksimal 1 Jam', '1 Background', '2 Outfit', '30 Foto Edit', 'All Original File', '1 USB Flashdisk (All Files)'],
                ],
            ],
        ],
        // ─── MATERNITY OUTDOOR ─────────────────────────────────────────────
        [
            'category_code' => 'MTN',
            'name' => 'Maternity Outdoor / Home Service',
            'features' => [
                'Tidak ada foto cetak',
                'Harga belum termasuk biaya transportasi dan biaya tambahan yang diperlukan untuk lokasi photoshoot',
                'File hasil edit dikirim melalui link Google Drive & Flashdisk',
            ],
            'variants' => [
                [
                    'name' => 'Foto',
                    'price' => 2000000,
                    'duration' => 120,
                    'is_whatsapp_only' => true,
                    'features' => ['Durasi Maksimal 2 Jam', '1 Fotografer', '2 Outfit', '50 Foto Edit', 'All Original File', '1 USB Flashdisk (All Files)'],
                ],
                [
                    'name' => 'Foto & Video',
                    'price' => 4000000,
                    'duration' => 120,
                    'is_whatsapp_only' => true,
                    'features' => ['Durasi Maksimal 2 Jam', '1 Fotografer', '1 Videografer', '2 Outfit', '50 Foto Edit', 'Video 1 Menit', 'All Original File', '1 USB Flashdisk (All Files)'],
                ],
            ],
        ],
        // ─── GROUP STUDIO ──────────────────────────────────────────────────
        [
            'category_code' => 'GRP',
            'name' => 'Group Studio',
            'features' => [
                'Tidak ada foto cetak',
                'Klien memilih background yang tersedia di studio',
                'Klien memilih foto yang akan diedit',
                'Hasil foto edit dikirim melalui link Google Drive',
                'Tambahan Rp25.000 / Orang (maksimal 10 orang)',
            ],
            'variants' => [
                [
                    'name' => 'Paket 1',
                    'price' => 250000,
                    'duration' => 20,
                    'is_whatsapp_only' => false,
                    'features' => ['Durasi 20 Menit', '1 Background', '1 Outfit', 'Maksimal 5 Orang', '10 Foto Edit', 'All Original File (Google Drive)'],
                ],
                [
                    'name' => 'Paket 2',
                    'price' => 350000,
                    'duration' => 30,
                    'is_whatsapp_only' => false,
                    'features' => ['Durasi 30 Menit', '1 Background', '2 Outfit', 'Maksimal 8 Orang', '20 Foto Edit', 'All Original File (Google Drive)'],
                ],
                [
                    'name' => 'Paket 3',
                    'price' => 500000,
                    'duration' => 60,
                    'is_whatsapp_only' => false,
                    'features' => ['Durasi 60 Menit', '2 Background', '2 Outfit', 'Maksimal 10 Orang', '30 Foto Edit', 'All Original File (Google Drive)'],
                ],
            ],
        ],
        // ─── FAMILY STUDIO ─────────────────────────────────────────────────
        [
            'category_code' => 'FAM',
            'name' => 'Family Studio',
            'features' => [
                'Tidak ada foto cetak',
                'Tidak diperbolehkan membawa kue ulang tahun',
                'Klien memilih background yang tersedia di studio',
                'File foto original dikirim melalui link Google Drive',
                'Klien memilih foto yang akan diedit',
                'Hasil foto edit dikirim melalui link Google Drive',
                'Tambahan Rp25.000 / Orang (maksimal 10 orang)',
            ],
            'variants' => [
                [
                    'name' => 'Paket 1',
                    'price' => 250000,
                    'duration' => 20,
                    'is_whatsapp_only' => false,
                    'features' => ['Durasi 20 Menit', '1 Background', '1 Outfit', 'Maksimal 5 Orang', '10 Foto Edit', 'All Original File (Google Drive)'],
                ],
                [
                    'name' => 'Paket 2',
                    'price' => 350000,
                    'duration' => 30,
                    'is_whatsapp_only' => false,
                    'features' => ['Durasi 30 Menit', '1 Background', '2 Outfit', 'Maksimal 8 Orang', '20 Foto Edit', 'All Original File (Google Drive)'],
                ],
                [
                    'name' => 'Paket 3',
                    'price' => 500000,
                    'duration' => 60,
                    'is_whatsapp_only' => false,
                    'features' => ['Durasi 60 Menit', '2 Background', '2 Outfit', 'Maksimal 10 Orang', '30 Foto Edit', 'All Original File (Google Drive)'],
                ],
            ],
        ],
        // ─── OUTDOOR FAMILY & GROUP ────────────────────────────────────────
        [
            'category_code' => 'FAM',
            'name' => 'Family & Group Outdoor / Home Service',
            'features' => [
                'Berlaku untuk satu tempat / lokasi photoshoot',
                'Tidak ada foto cetak',
                'Harga belum termasuk untuk biaya charge yang diperlukan untuk lokasi photoshoot',
                'File foto original dikirim melalui link Google Drive',
                'Klien memilih file foto yang akan di edit',
                'File hasil edit dikirim melalui link Google Drive & Flashdisk',
                'Free 1 Set studio lighting',
                'Free 1 Set Background muat untuk 15 orang (by request)',
                'Biaya tambahan durasi per 60 menit Rp500.000',
                'Biaya tambahan edit per foto Rp15.000',
            ],
            'variants' => [
                [
                    'name' => 'Small',
                    'price' => 1500000,
                    'duration' => 60,
                    'is_whatsapp_only' => true,
                    'features' => ['Durasi 1 Jam', 'Maksimal 5 Orang', '20 Foto Edit'],
                ],
                [
                    'name' => 'Medium',
                    'price' => 2000000,
                    'duration' => 120,
                    'is_whatsapp_only' => true,
                    'features' => ['Durasi 2 Jam', 'Maksimal 15 Orang', '40 Foto Edit'],
                ],
                [
                    'name' => 'Large',
                    'price' => 3000000,
                    'duration' => 180,
                    'is_whatsapp_only' => true,
                    'features' => ['Durasi 3 Jam', 'Lebih dari 15 Orang', '60 Foto Edit'],
                ],
            ],
        ],
        // ─── GRADUATION STUDIO ─────────────────────────────────────────────
        [
            'category_code' => 'GRD',
            'name' => 'Graduation Studio',
            'features' => [
                'Tidak ada foto cetak',
                'Klien memilih background yang tersedia di studio',
                'File foto original dikirim melalui link Google Drive',
                'Klien memilih foto yang akan diedit',
                'Hasil foto edit dikirim melalui link Google Drive',
                'Tambahan Rp25.000 / Orang (maksimal 10 orang) *studio',
            ],
            'variants' => [
                [
                    'name' => 'Paket 1',
                    'price' => 250000,
                    'duration' => 20,
                    'is_whatsapp_only' => false,
                    'features' => ['Durasi 20 Menit', '1 Background', '1 Outfit', 'Maksimal 5 Orang', '10 Foto Edit', 'All Original File (Google Drive)'],
                ],
                [
                    'name' => 'Paket 2',
                    'price' => 350000,
                    'duration' => 30,
                    'is_whatsapp_only' => false,
                    'features' => ['Durasi 30 Menit', '1 Background', '2 Outfit', 'Maksimal 8 Orang', '20 Foto Edit', 'All Original File (Google Drive)'],
                ],
                [
                    'name' => 'Paket 3',
                    'price' => 500000,
                    'duration' => 60,
                    'is_whatsapp_only' => false,
                    'features' => ['Durasi 60 Menit', '2 Background', '2 Outfit', 'Maksimal 10 Orang', '30 Foto Edit', 'All Original File (Google Drive)'],
                ],
            ],
        ],
        // ─── GRADUATION ON THE SPOT ────────────────────────────────────────
        [
            'category_code' => 'GRD',
            'name' => 'Graduation On The Spot',
            'features' => [
                'Tidak ada foto cetak',
                'Klien memilih foto yang akan diedit',
                'Hasil foto edit dikirim melalui link Google Drive',
            ],
            'variants' => [
                [
                    'name' => 'On The Spot',
                    'price' => 500000,
                    'duration' => 30,
                    'is_whatsapp_only' => true,
                    'features' => ['Durasi 30 Menit', '1 Lokasi', '20 Foto Edit', 'All Original File (Google Drive)'],
                ],
            ],
        ],
        // ─── WEDDING ALL IN ONE ────────────────────────────────────────────
        [
            'category_code' => 'WED',
            'name' => 'Wedding All In One',
            'features' => [
                '2 Album Magazine Hard Cover + Box',
                '2 Album Magnetic + 240 Foto Ukuran 4R',
                '3 Foto Cetak ukuran 16RJ + Figura',
                '1 Menit Video Engagement',
                '1 Menit Video Traditional Ceremony',
                '1 Menit & 3 Menit Video Wedding',
                '1 USB Flashdisk (All Files)',
            ],
            'variants' => [
                [
                    'name' => 'All In One',
                    'price' => 21500000,
                    'duration' => 480,
                    'is_whatsapp_only' => true,
                    'features' => [
                        'Engagement: Durasi 4 Jam, 2 Fotografer, 1 Videografer',
                        'Traditional Ceremony: Durasi 4 Jam, 1 Acara, 2 Fotografer, 1 Videografer',
                        'Wedding: 1 Hari Durasi 8 Jam, 1 Lokasi, 3 Fotografer, 2 Videografer',
                        'Couple Session: Durasi 1 Jam, 1 Fotografer, Indoor Studio, 1 Outfit, 20 Foto Edit',
                    ],
                ],
            ],
        ],
        // ─── WEDDING FOTO & VIDEO ──────────────────────────────────────────
        [
            'category_code' => 'WED',
            'name' => 'Wedding Foto & Video',
            'features' => [
                'Tidak ada foto cetak',
                'Harga belum termasuk biaya transportasi ke luar kota',
                'File hasil edit dikirim melalui link Google Drive & Flashdisk',
            ],
            'variants' => [
                [
                    'name' => 'Paket 1',
                    'price' => 5500000,
                    'duration' => 480,
                    'is_whatsapp_only' => true,
                    'features' => ['1 Hari Acara', 'Durasi Maksimal 8 Jam', '1 Lokasi', '2 Fotografer', '1 Videografer', 'Video 1 Menit', '1 Album Magazine + Box', 'All Original File', '1 USB Flashdisk (All Files)'],
                ],
                [
                    'name' => 'Paket 2',
                    'price' => 10000000,
                    'duration' => 480,
                    'is_whatsapp_only' => true,
                    'features' => ['1 Hari Acara', 'Durasi Maksimal 8 Jam', '2 Lokasi', '3 Fotografer', 'Full Dokumentasi', '2 Videografer', 'Video 1 Menit & Video 3 Menit', '1 Album Magazine Hard Cover + Box', '1 Album Magnetic + 120 Foto 4R', '2 Foto Cetak Ukuran 16RJ + Figura', '1 USB Flashdisk (All Files)'],
                ],
            ],
        ],
    ];

    public function run(): void
    {
        foreach ($this->packages as $packageData) {
            $category = Category::where('category_code', $packageData['category_code'])->first();

            if (! $category) {
                $this->command->warn("Kategori '{$packageData['category_code']}' tidak ditemukan. Skip: {$packageData['name']}");
                continue;
            }

            /** @var Package $package */
            $package = Package::create([
                'category_id' => $category->id,
                'name' => $packageData['name'],
                'is_active' => true,
            ]);

            if (! empty($packageData['features'])) {
                $package->features()->createMany(
                    collect($packageData['features'])
                        ->map(fn (string $desc) => ['description' => $desc])
                        ->toArray(),
                );
            }

            foreach ($packageData['variants'] as $variantData) {
                /** @var PackageVariant $variant */
                $variant = $package->variants()->create([
                    'name' => $variantData['name'],
                    'price' => $variantData['price'],
                    'duration' => $variantData['duration'],
                    'is_whatsapp_only' => $variantData['is_whatsapp_only'],
                    'is_active' => true,
                ]);

                if (! empty($variantData['features'])) {
                    $variant->features()->createMany(
                        collect($variantData['features'])
                            ->map(fn (string $desc) => ['description' => $desc])
                            ->toArray(),
                    );
                }
            }
        }
    }
}
```

> **Catatan:** Jalankan `CategorySeeder` terlebih dahulu. Tambahkan di `DatabaseSeeder.php`:
> ```php
> $this->call([
>     CategorySeeder::class,
>     PackageSeeder::class,
> ]);
> ```

---

## 11. Frontend — JS Modules

### 11.1 `useState.js`

**File:** `resources/js/features/master-data/package/useState.js`

```js
export default function useState(Alpine) {
  return Alpine.reactive({
    totalPackages: 0,
    totalActiveVariants: 0,
    isLoading: false,

    // Drawer Package
    isDrawerOpen: false,
    isEdit: false,
    packageId: null,

    // Drawer Variant
    isVariantDrawerOpen: false,
    isVariantEdit: false,
    variantId: null,
    currentPackageSlug: null,

    // Form Paket
    form: {
      category_id: '',
      name: '',
      is_active: true,
      features: [''],
    },

    // Form Varian
    variantForm: {
      name: '',
      price: '',
      duration: '',
      is_whatsapp_only: false,
      is_active: true,
      features: [''],
    },

    errors: {},
    variantErrors: {},
  });
}
```

### 11.2 `useForm.js`

**File:** `resources/js/features/master-data/package/useForm.js`

```js
import route from '../../../lib/route';
import { Modal, Toast } from '../../../lib/sweetalert';
import { z } from 'zod';

const packageSchema = z.object({
  category_id: z.union([z.string().min(1, 'Kategori wajib dipilih.'), z.number().min(1, 'Kategori wajib dipilih.')]),
  name: z.string().min(3, 'Nama paket minimal 3 karakter.').max(100, 'Maksimal 100 karakter.'),
  is_active: z.boolean(),
  features: z.array(z.string()).optional(),
});

const variantSchema = z.object({
  name: z.string().min(3, 'Nama varian minimal 3 karakter.').max(100, 'Maksimal 100 karakter.'),
  price: z.union([z.string(), z.number()]).transform((v) => Number(v)).refine((v) => v >= 1, 'Harga minimal Rp 1.'),
  duration: z.union([z.string(), z.number()]).transform((v) => Number(v)).refine((v) => v >= 1, 'Durasi minimal 1 menit.'),
  is_whatsapp_only: z.boolean(),
  is_active: z.boolean(),
  features: z.array(z.string()).optional(),
});

export default function useForm({ state, table }) {
  const resetPackageForm = () => {
    state.isEdit = false;
    state.packageId = null;
    state.form.category_id = '';
    state.form.name = '';
    state.form.is_active = true;
    state.form.features = [''];
    state.errors = {};
  };

  const resetVariantForm = () => {
    state.isVariantEdit = false;
    state.variantId = null;
    state.variantForm.name = '';
    state.variantForm.price = '';
    state.variantForm.duration = '';
    state.variantForm.is_whatsapp_only = false;
    state.variantForm.is_active = true;
    state.variantForm.features = [''];
    state.variantErrors = {};
  };

  const openDrawer = () => { resetPackageForm(); state.isDrawerOpen = true; };
  const closeDrawer = () => { state.isDrawerOpen = false; resetPackageForm(); };

  const editPackage = (pkg) => {
    resetPackageForm();
    state.isEdit = true;
    state.packageId = pkg.slug;
    state.form.category_id = pkg.category_id;
    state.form.name = pkg.name;
    state.form.is_active = pkg.is_active;
    state.form.features = pkg.features?.map((f) => f.description) ?? [''];
    if (state.form.features.length === 0) state.form.features = [''];
    state.isDrawerOpen = true;
  };

  const addFeature = () => state.form.features.push('');
  const removeFeature = (index) => {
    state.form.features.splice(index, 1);
    if (state.form.features.length === 0) state.form.features.push('');
  };

  const openVariantDrawer = (packageSlug) => {
    resetVariantForm();
    state.currentPackageSlug = packageSlug;
    state.isVariantDrawerOpen = true;
  };
  const closeVariantDrawer = () => { state.isVariantDrawerOpen = false; resetVariantForm(); };

  const editVariant = (variant, packageSlug) => {
    resetVariantForm();
    state.isVariantEdit = true;
    state.variantId = variant.id;
    state.currentPackageSlug = packageSlug;
    state.variantForm.name = variant.name;
    state.variantForm.price = variant.price;
    state.variantForm.duration = variant.duration;
    state.variantForm.is_whatsapp_only = variant.is_whatsapp_only;
    state.variantForm.is_active = variant.is_active;
    state.variantForm.features = variant.features?.map((f) => f.description) ?? [''];
    if (state.variantForm.features.length === 0) state.variantForm.features.push('');
    state.isVariantDrawerOpen = true;
  };

  const addVariantFeature = () => state.variantForm.features.push('');
  const removeVariantFeature = (index) => {
    state.variantForm.features.splice(index, 1);
    if (state.variantForm.features.length === 0) state.variantForm.features.push('');
  };

  const submitPackage = async () => {
    state.isLoading = true;
    state.errors = {};
    const filteredFeatures = state.form.features.filter((f) => f.trim() !== '');
    const validation = packageSchema.safeParse({ ...state.form, features: filteredFeatures });
    if (!validation.success) {
      validation.error.issues.forEach((issue) => {
        if (!state.errors[issue.path[0]]) state.errors[issue.path[0]] = issue.message;
      });
      state.isLoading = false;
      return;
    }
    const payload = { ...state.form, features: filteredFeatures };
    const url = state.isEdit
      ? route('backdoor.data-master.package.update', state.packageId)
      : route('backdoor.data-master.package.store');
    const method = state.isEdit ? 'put' : 'post';
    try {
      const response = await window.axios[method](url, payload);
      closeDrawer();
      table.reload();
      Toast.fire({ icon: 'success', title: response.data.message });
    } catch (error) {
      if (error.response?.status === 422) {
        const errs = error.response.data.errors;
        for (const key in errs) state.errors[key] = errs[key][0];
      } else {
        Modal.fire({ icon: 'error', title: 'Gagal menyimpan paket', text: error.response?.data?.message ?? 'Terjadi kesalahan server.' });
      }
    } finally {
      state.isLoading = false;
    }
  };

  const submitVariant = async () => {
    state.isLoading = true;
    state.variantErrors = {};
    const filteredFeatures = state.variantForm.features.filter((f) => f.trim() !== '');
    const validation = variantSchema.safeParse({ ...state.variantForm, features: filteredFeatures });
    if (!validation.success) {
      validation.error.issues.forEach((issue) => {
        if (!state.variantErrors[issue.path[0]]) state.variantErrors[issue.path[0]] = issue.message;
      });
      state.isLoading = false;
      return;
    }
    const payload = { ...state.variantForm, features: filteredFeatures };
    const url = state.isVariantEdit
      ? route('backdoor.data-master.package.variants.update', { package: state.currentPackageSlug, variant: state.variantId })
      : route('backdoor.data-master.package.variants.store', state.currentPackageSlug);
    const method = state.isVariantEdit ? 'put' : 'post';
    try {
      const response = await window.axios[method](url, payload);
      closeVariantDrawer();
      window.location.reload();
      Toast.fire({ icon: 'success', title: response.data.message });
    } catch (error) {
      if (error.response?.status === 422) {
        const errs = error.response.data.errors;
        for (const key in errs) state.variantErrors[key] = errs[key][0];
      } else {
        Modal.fire({ icon: 'error', title: 'Gagal menyimpan varian', text: error.response?.data?.message ?? 'Terjadi kesalahan server.' });
      }
    } finally {
      state.isLoading = false;
    }
  };

  return {
    openDrawer, closeDrawer, editPackage, addFeature, removeFeature, submitPackage,
    openVariantDrawer, closeVariantDrawer, editVariant, addVariantFeature, removeVariantFeature, submitVariant,
  };
}
```

### 11.3 `useActions.js`

**File:** `resources/js/features/master-data/package/useActions.js`

```js
import route from '../../../lib/route';
import { Modal, Toast, confirmModal } from '../../../lib/sweetalert';

export default function useActions({ state, table }) {
  const togglePackageStatus = async (slug, event) => {
    const checkbox = event.target;
    const originalChecked = !checkbox.checked;
    state.isLoading = true;
    try {
      const response = await window.axios.patch(route('backdoor.data-master.package.toggle', slug));
      if (response.data.total_active_variants !== undefined) {
        state.totalActiveVariants = response.data.total_active_variants;
      }
      const idx = table.data.findIndex((p) => p.slug === slug);
      if (idx !== -1) table.data[idx].is_active = !originalChecked;
      Toast.fire({ icon: 'success', title: response.data.message });
    } catch (error) {
      checkbox.checked = originalChecked;
      Toast.fire({ icon: 'error', title: error.response?.data?.message ?? 'Terjadi kesalahan server.' });
    } finally {
      state.isLoading = false;
    }
  };

  const toggleVariantStatus = async (packageSlug, variantId, event) => {
    const checkbox = event.target;
    const originalChecked = !checkbox.checked;
    state.isLoading = true;
    try {
      const response = await window.axios.patch(
        route('backdoor.data-master.package.variants.toggle', { package: packageSlug, variant: variantId }),
      );
      Toast.fire({ icon: 'success', title: response.data.message });
    } catch (error) {
      checkbox.checked = originalChecked;
      Toast.fire({ icon: 'error', title: error.response?.data?.message ?? 'Terjadi kesalahan server.' });
    } finally {
      state.isLoading = false;
    }
  };

  const destroyPackage = async (pkg) => {
    const result = await confirmModal('Hapus Paket?', `Paket "${pkg.name}" beserta seluruh variannya akan dihapus secara permanen.`, 'warning', 'Ya, Hapus');
    if (!result.isConfirmed) return;
    try {
      const response = await window.axios.delete(route('backdoor.data-master.package.destroy', pkg.slug));
      table.reload();
      Toast.fire({ icon: 'success', title: response.data.message });
    } catch (error) {
      Modal.fire({ icon: 'error', title: 'Gagal menghapus paket', text: error.response?.data?.message ?? 'Terjadi kesalahan server.' });
    }
  };

  const destroyVariant = async (packageSlug, variantId, variantName) => {
    const result = await confirmModal('Hapus Varian?', `Varian "${variantName}" akan dihapus secara permanen.`, 'warning', 'Ya, Hapus');
    if (!result.isConfirmed) return;
    try {
      const response = await window.axios.delete(
        route('backdoor.data-master.package.variants.destroy', { package: packageSlug, variant: variantId }),
      );
      window.location.reload();
      Toast.fire({ icon: 'success', title: response.data.message });
    } catch (error) {
      Modal.fire({ icon: 'error', title: 'Gagal menghapus varian', text: error.response?.data?.message ?? 'Terjadi kesalahan server.' });
    }
  };

  return { togglePackageStatus, toggleVariantStatus, destroyPackage, destroyVariant };
}
```

### 11.4 `Package.js` — Entry Point Alpine.data

**File:** `resources/js/features/master-data/package/Package.js`

```js
import route from '../../../lib/route';
import useDatatable from '../../../lib/useDatatable';
import useForm from './useForm';
import useActions from './useActions';
import useState from './useState';
import { Toast } from '../../../lib/sweetalert';

export default function Package(Alpine) {
  const state = useState(Alpine);

  const { state: tableState, fetch, setSearch, nextPage, prevPage, goToPage, reload, getPages } = useDatatable(
    Alpine,
    route('backdoor.data-master.package.index'),
    {
      onSuccess: (res) => {
        if (res.total_packages !== undefined) state.totalPackages = res.total_packages;
        if (res.total_active_variants !== undefined) state.totalActiveVariants = res.total_active_variants;
      },
      onError: () => Toast.fire({ icon: 'error', title: 'Gagal memuat data tabel.' }),
    },
  );

  tableState.fetch = fetch;
  tableState.setSearch = setSearch;
  tableState.nextPage = nextPage;
  tableState.prevPage = prevPage;
  tableState.goToPage = goToPage;
  tableState.reload = reload;
  tableState.getPages = getPages;

  const table = tableState;

  const init = function () { fetch(); };

  const { openDrawer, closeDrawer, editPackage, addFeature, removeFeature, submitPackage,
    openVariantDrawer, closeVariantDrawer, editVariant, addVariantFeature, removeVariantFeature, submitVariant } =
    useForm({ state, table });

  const { togglePackageStatus, toggleVariantStatus, destroyPackage, destroyVariant } = useActions({ state, table });

  return {
    state, table, init,
    openDrawer, closeDrawer, editPackage, addFeature, removeFeature, submitPackage,
    openVariantDrawer, closeVariantDrawer, editVariant, addVariantFeature, removeVariantFeature, submitVariant,
    togglePackageStatus, toggleVariantStatus, destroyPackage, destroyVariant,
  };
}
```

---

## 12. Frontend — Blade Views

> Blade views (index.blade.php + show.blade.php) mengikuti pola yang sama dengan Category.
> Strukturnya sudah didesain sesuai UI mockup di atas.
> Lengkapnya akan dibuat saat implementasi untuk menyesuaikan komponen layout yang sudah ada.

**Ringkasan:**
- `index.blade.php` → stats card, tabel paket (useDatatable), drawer tambah/edit paket
- `show.blade.php` → info paket, tabel varian, drawer tambah/edit varian

---

## 13. Registrasi Alpine.data & Route

### Daftar Route Name yang Digunakan JS

| Route Name | Method | URL | Keterangan |
|---|---|---|---|
| `backdoor.data-master.package.index` | GET | `/backdoor/data-master/package` | Fetch tabel |
| `backdoor.data-master.package.store` | POST | `/backdoor/data-master/package` | Tambah paket |
| `backdoor.data-master.package.update` | PUT | `/backdoor/data-master/package/{slug}` | Edit paket |
| `backdoor.data-master.package.destroy` | DELETE | `/backdoor/data-master/package/{slug}` | Hapus paket |
| `backdoor.data-master.package.toggle` | PATCH | `/backdoor/data-master/package/{slug}/toggle` | Toggle status |
| `backdoor.data-master.package.show` | GET | `/backdoor/data-master/package/{slug}` | Halaman detail |
| `backdoor.data-master.package.variants.store` | POST | `.../{slug}/variants` | Tambah varian |
| `backdoor.data-master.package.variants.update` | PUT | `.../{slug}/variants/{id}` | Edit varian |
| `backdoor.data-master.package.variants.destroy` | DELETE | `.../{slug}/variants/{id}` | Hapus varian |
| `backdoor.data-master.package.variants.toggle` | PATCH | `.../{slug}/variants/{id}/toggle` | Toggle status varian |

### Urutan Implementasi yang Direkomendasikan

1. Route di `package.php` + require di `data-master.php`
2. DTOs: `PackageData`, `PackageVariantData`
3. Repositories: `PackageRepository`, `PackageVariantRepository`
4. Services: `PackageService`, `PackageVariantService`
5. FormRequests (4 file)
6. Controllers: `PackageController`, `PackageVariantController`
7. Jalankan `php artisan db:seed --class=PackageSeeder`
8. JS modules: `useState.js`, `useForm.js`, `useActions.js`, `Package.js`
9. Daftarkan `Alpine.data` di entry point
10. Blade views: `index.blade.php`, `show.blade.php`
11. `npm run build`

### Catatan Penting

- **Choices.js di Drawer:** Harus di-destroy setiap kali drawer tutup dan dibuat ulang saat buka, untuk menghindari duplikasi DOM.
- **Feature polimorfik:** `features()` pada Package → `featureable_type = Package`. Pada variant → `featureable_type = PackageVariant`.
- **`is_whatsapp_only`:** Hanya ada di `PackageVariant`. Jika `true`, klien tidak bisa booking otomatis via Midtrans — diarahkan ke WhatsApp CS.
- **Seeder category_code:** Pastikan field `category_code` ada di tabel `categories` dan CategorySeeder sudah di-seed.
