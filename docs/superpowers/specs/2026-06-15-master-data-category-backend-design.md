# Spesifikasi Desain & Panduan Kode: Backend Master Data - Kategori Foto

## 1. Ikhtisar
Dokumen ini dirancang untuk panduan implementasi mandiri oleh pengembang. Ini merinci arsitektur, aliran data, dan contoh kode backend untuk fitur Master Data "Kategori" (CRUD) dengan pendekatan *Domain-Driven Design* (DDD) di bawah direktori `app/Domains/MasterData`.

---

## 2. Arsitektur & Struktur File

Fitur ini menggunakan struktur berlapis untuk memisahkan tanggung jawab secara tegas (*Separation of Concerns*):

- **Repository**: `app/Domains/MasterData/Repositories/CategoryRepository.php`
  - Bertanggung jawab penuh terhadap interaksi database PostgreSQL (query pencarian, paginasi, insert, update, delete).
- **DTO (Data Transfer Object)**: `app/Domains/MasterData/DTOs/CategoryData.php`
  - Berfungsi sebagai penampung data bertipe pasti (*type-safe*) yang dialirkan dari form request menuju service.
- **Service**: `app/Domains/MasterData/Services/CategoryService.php`
  - Berisi logika bisnis (misal: memproses input `category_code` agar tersimpan huruf besar semua, logika toggle status, dan validasi keberadaan entitas sebelum diolah).
- **Form Requests**:
  - `app/Domains/MasterData/Http/Requests/StoreCategoryRequest.php`
  - `app/Domains/MasterData/Http/Requests/UpdateCategoryRequest.php`
  - Berfungsi menampung aturan validasi request dan menerjemahkan pesan kesalahan ke Bahasa Indonesia.
- **Controller**: `app/Domains/MasterData/Http/Controllers/Backdoor/CategoryController.php`
  - Berfungsi sebagai jembatan yang menentukan apakah data harus dikembalikan dalam bentuk JSON (jika dipicu oleh AJAX Axios) atau halaman Blade HTML.

---

## 3. Rute (Routes)

Daftarkan rute CRUD Kategori pada file:
`routes/backdoor/data-master/category.php`

```php
<?php

use App\Domains\MasterData\Http\Controllers\Backdoor\CategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/backdoor/data-master/category', [CategoryController::class, 'index'])->name('backdoor.data-master.category');
    Route::post('/backdoor/data-master/category', [CategoryController::class, 'store'])->name('backdoor.data-master.category.store');
    Route::put('/backdoor/data-master/category/{category}', [CategoryController::class, 'update'])->name('backdoor.data-master.category.update');
    Route::delete('/backdoor/data-master/category/{category}', [CategoryController::class, 'destroy'])->name('backdoor.data-master.category.destroy');
    Route::patch('/backdoor/data-master/category/{category}/toggle', [CategoryController::class, 'toggleActive'])->name('backdoor.data-master.category.toggle');
});
```

---

## 4. Cetak Biru Kode (Code Boilerplate)

### A. Repository
Letak File: `app/Domains/MasterData/Repositories/CategoryRepository.php`

```php
<?php

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryRepository
{
    /**
     * Mengambil data kategori dengan paginasi dan filter pencarian.
     */
    public function getPaginatedCategories(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Category::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('category_code', 'like', '%' . $search . '%');
            });
        }

        return $query->latest('id')->paginate($perPage);
    }

    /**
     * Mencari kategori berdasarkan ID.
     */
    public function findById(int $id): ?Category
    {
        return Category::find($id);
    }

    /**
     * Membuat kategori baru.
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * Memperbarui data kategori.
     */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);
        return $category;
    }

    /**
     * Menghapus kategori.
     */
    public function delete(Category $category): ?bool
    {
        return $category->delete();
    }
}
```

### B. DTO (Data Transfer Object)
Letak File: `app/Domains/MasterData/DTOs/CategoryData.php`

```php
<?php

namespace App\Domains\MasterData\DTOs;

use Spatie\LaravelData\Data;

class CategoryData extends Data
{
    public function __construct(
        public readonly string $category_code,
        public readonly string $name,
        public readonly bool $is_active = true
    ) {}
}
```

### C. Service
Letak File: `app/Domains/MasterData/Services/CategoryService.php`

```php
<?php

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\DTOs\CategoryData;
use App\Domains\MasterData\Models\Category;
use App\Domains\MasterData\Repositories\CategoryRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryService
{
    public function __construct(
        protected CategoryRepository $categoryRepository
    ) {}

    /**
     * Logika bisnis simpan kategori baru (otomatis mengubah kode menjadi HURUF BESAR).
     */
    public function createCategory(CategoryData $data): Category
    {
        return $this->categoryRepository->create([
            'category_code' => strtoupper($data->category_code),
            'name' => $data->name,
            'is_active' => $data->is_active,
        ]);
    }

    /**
     * Logika bisnis perbarui kategori.
     */
    public function updateCategory(int $id, CategoryData $data): Category
    {
        $category = $this->categoryRepository->findById($id);

        if (!$category) {
            throw new ModelNotFoundException("Kategori tidak ditemukan.");
        }

        return $this->categoryRepository->update($category, [
            'category_code' => strtoupper($data->category_code),
            'name' => $data->name,
            'is_active' => $data->is_active,
        ]);
    }

    /**
     * Logika bisnis hapus kategori.
     */
    public function deleteCategory(int $id): bool
    {
        $category = $this->categoryRepository->findById($id);

        if (!$category) {
            throw new ModelNotFoundException("Kategori tidak ditemukan.");
        }

        return $this->categoryRepository->delete($category);
    }

    /**
     * Logika toggle status aktif / is_active.
     */
    public function toggleCategoryActiveStatus(int $id): Category
    {
        $category = $this->categoryRepository->findById($id);

        if (!$category) {
            throw new ModelNotFoundException("Kategori tidak ditemukan.");
        }

        return $this->categoryRepository->update($category, [
            'is_active' => !$category->is_active,
        ]);
    }
}
```

### D. Form Requests

#### 1. Store Request (Simpan Baru)
Letak File: `app/Domains/MasterData/Http/Requests/StoreCategoryRequest.php`

```php
<?php

namespace App\Domains\MasterData\Http\Requests;

use App\Domains\MasterData\DTOs\CategoryData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_code' => [
                'required',
                'string',
                'max:3',
                'unique:categories,category_code',
            ],
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:categories,name',
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function toDto(): CategoryData
    {
        return new CategoryData(
            category_code: trim($this->validated('category_code')),
            name: trim($this->validated('name')),
            is_active: (bool) $this->validated('is_active')
        );
    }

    public function messages(): array
    {
        return [
            'category_code.required' => 'Kode kategori wajib diisi.',
            'category_code.max' => 'Kode kategori maksimal 3 karakter.',
            'category_code.unique' => 'Kode kategori sudah terdaftar.',
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'name.unique' => 'Nama kategori sudah terdaftar.',
            'is_active.required' => 'Status aktif wajib diisi.',
            'is_active.boolean' => 'Status aktif harus berupa boolean.',
        ];
    }
}
```

#### 2. Update Request (Perbarui)
Letak File: `app/Domains/MasterData/Http/Requests/UpdateCategoryRequest.php`

```php
<?php

namespace App\Domains\MasterData\Http\Requests;

use App\Domains\MasterData\DTOs\CategoryData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category');

        return [
            'category_code' => [
                'required',
                'string',
                'max:3',
                'unique:categories,category_code,' . $categoryId,
            ],
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:categories,name,' . $categoryId,
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function toDto(): CategoryData
    {
        return new CategoryData(
            category_code: trim($this->validated('category_code')),
            name: trim($this->validated('name')),
            is_active: (bool) $this->validated('is_active')
        );
    }

    public function messages(): array
    {
        return [
            'category_code.required' => 'Kode kategori wajib diisi.',
            'category_code.max' => 'Kode kategori maksimal 3 karakter.',
            'category_code.unique' => 'Kode kategori sudah terdaftar.',
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'name.unique' => 'Nama kategori sudah terdaftar.',
            'is_active.required' => 'Status aktif wajib diisi.',
            'is_active.boolean' => 'Status aktif harus berupa boolean.',
        ];
    }
}
```

### E. Controller
Letak File: `app/Domains/MasterData/Http/Controllers/Backdoor/CategoryController.php`

```php
<?php

namespace App\Domains\MasterData\Http\Controllers\Backdoor;

use App\Http\Controllers\Controller;
use App\Domains\MasterData\Http\Requests\StoreCategoryRequest;
use App\Domains\MasterData\Http\Requests\UpdateCategoryRequest;
use App\Domains\MasterData\Repositories\CategoryRepository;
use App\Domains\MasterData\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected CategoryService $categoryService
    ) {}

    /**
     * Menampilkan daftar kategori (HTML) atau data JSON jika diminta oleh AJAX (useDatatable.js).
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->wantsJson()) {
            $search = $request->query('search');
            $perPage = $request->query('limit', 10);
            
            $categories = $this->categoryRepository->getPaginatedCategories($search, (int)$perPage);
            
            return response()->json($categories);
        }

        return view('backdoor.data-master.category.pages.index');
    }

    /**
     * Menyimpan kategori baru ke database.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->createCategory($request->toDto());

        return response()->json([
            'status' => 'success',
            'message' => 'Kategori berhasil ditambahkan.',
            'data' => $category,
        ], 201);
    }

    /**
     * Memperbarui kategori yang ditentukan.
     */
    public function update(UpdateCategoryRequest $request, string $id): JsonResponse
    {
        $category = $this->categoryService->updateCategory((int)$id, $request->toDto());

        return response()->json([
            'status' => 'success',
            'message' => 'Kategori berhasil diperbarui.',
            'data' => $category,
        ]);
    }

    /**
     * Menghapus kategori yang ditentukan.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->categoryService->deleteCategory((int)$id);

        return response()->json([
            'status' => 'success',
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }

    /**
     * Mengubah status aktif (toggle is_active) secara cepat.
     */
    public function toggleActive(string $id): JsonResponse
    {
        $category = $this->categoryService->toggleCategoryActiveStatus((int)$id);

        return response()->json([
            'status' => 'success',
            'message' => 'Status aktif kategori berhasil diperbarui.',
            'data' => $category,
        ]);
    }
}
```

---

## 5. Cetak Biru Kode (Code Boilerplate): Frontend View (Blade + Native Alpine.js Table)

Letak File: `resources/views/backdoor/data-master/category/pages/index.blade.php`

Ini adalah referensi antarmuka (View) menggunakan **Alpine.js** untuk state modal & pemanggilan AJAX (Axios), serta custom hook **useDatatable.js** untuk render tabel data. (Desain modular dan berfokus agar CRUD berfungsi penuh).

```html
@extends('backdoor.layouts.app')

@section('title', 'Master Data Kategori')

@section('content')
<div x-data="categoryApp()" x-init="initGrid()" class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Kategori Foto</h1>
        <button @click="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded">
            Tambah Kategori
        </button>
    </div>

    <!-- Tabel Data Native Alpine.js -->
    <div id="category-grid" class="bg-white rounded shadow p-4"></div>

    <!-- Modal Form (Create / Update) -->
    <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6" @click.away="closeModal()">
            <h2 class="text-xl font-bold mb-4" x-text="isEdit ? 'Edit Kategori' : 'Tambah Kategori'"></h2>
            
            <form @submit.prevent="submitForm">
                <!-- Input: category_code -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Kode Kategori (Max 3 Karakter)</label>
                    <input type="text" x-model="form.category_code" maxlength="3" class="w-full border rounded p-2 uppercase" required>
                    <span class="text-red-500 text-xs" x-show="errors.category_code" x-text="errors.category_code"></span>
                </div>

                <!-- Input: name -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Nama Kategori</label>
                    <input type="text" x-model="form.name" class="w-full border rounded p-2" required>
                    <span class="text-red-500 text-xs" x-show="errors.name" x-text="errors.name"></span>
                </div>

                <!-- Input: is_active -->
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" x-model="form.is_active" class="mr-2 rounded">
                        <span class="text-sm font-medium">Aktif</span>
                    </label>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" @click="closeModal()" class="px-4 py-2 text-gray-600 bg-gray-200 rounded">Batal</button>
                    <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded" :disabled="isLoading">
                        <span x-text="isLoading ? 'Menyimpan...' : 'Simpan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('categoryApp', () => ({
            grid: null,
            isModalOpen: false,
            isEdit: false,
            isLoading: false,
            categoryId: null, // Berisi slug kategori
            form: { category_code: '', name: '', is_active: true },
            errors: {},

            initGrid() {
                this.grid = new gridjs.Grid({
                    columns: [
                        { id: 'category_code', name: 'Kode' },
                        { id: 'name', name: 'Nama' },
                        { 
                            id: 'is_active', 
                            name: 'Status',
                            formatter: (cell) => gridjs.html(cell ? '<span class="text-green-600">Aktif</span>' : '<span class="text-red-600">Nonaktif</span>')
                        },
                        { id: 'slug', name: 'Slug', hidden: true },
                        {
                            name: 'Aksi',
                            formatter: (cell, row) => {
                                // Menggunakan Alpine context jika memungkinkan, atau lempar ke window function
                                return gridjs.html(`
                                    <button onclick="window.editCategory('${row.cells[3].data}', '${row.cells[0].data}', '${row.cells[1].data}', ${row.cells[2].data})" class="text-blue-500 mr-2 hover:underline">Edit</button>
                                    <button onclick="window.deleteCategory('${row.cells[3].data}')" class="text-red-500 hover:underline">Hapus</button>
                                `);
                            }
                        }
                    ],
                    server: {
                        url: '/backdoor/data-master/category',
                        then: data => data.data.map(item => [item.category_code, item.name, item.is_active, item.slug])
                    },
                    search: { server: { url: (prev, keyword) => `${prev}?search=${keyword}` } },
                    pagination: { server: { url: (prev, page, limit) => `${prev}&page=${page + 1}&limit=${limit}` } }
                }).render(document.getElementById("category-grid"));

                // Expose fungsi ke scope untuk digunakan dari dalam row tabel
                window.editCategory = (slug, code, name, isActive) => this.openEditModal(slug, code, name, isActive);
                window.deleteCategory = (slug) => this.destroyCategory(slug);
            },

            openModal() {
                this.resetForm();
                this.isModalOpen = true;
            },

            openEditModal(slug, code, name, isActive) {
                this.resetForm();
                this.isEdit = true;
                this.categoryId = slug; // Bind rute update via slug
                this.form.category_code = code;
                this.form.name = name;
                this.form.is_active = isActive;
                this.isModalOpen = true;
            },

            closeModal() {
                this.isModalOpen = false;
                this.resetForm();
            },

            resetForm() {
                this.isEdit = false;
                this.categoryId = null;
                this.form = { category_code: '', name: '', is_active: true };
                this.errors = {};
            },

            async submitForm() {
                this.isLoading = true;
                this.errors = {};
                
                let url = '/backdoor/data-master/category';
                let method = 'post';

                if (this.isEdit) {
                    url += `/${this.categoryId}`; // Gunakan slug untuk edit
                    method = 'put';
                }

                try {
                    await axios[method](url, this.form);
                    this.closeModal();
                    this.table.reload(); // Reload tabel tanpa refresh halaman
                } catch (error) {
                    if (error.response && error.response.status === 422) {
                        let errs = error.response.data.errors;
                        for (let key in errs) {
                            this.errors[key] = errs[key][0];
                        }
                    } else {
                        alert('Terjadi kesalahan server.');
                    }
                } finally {
                    this.isLoading = false;
                }
            },

            async destroyCategory(slug) {
                if(confirm('Yakin ingin menghapus kategori ini?')) {
                    try {
                        await axios.delete(`/backdoor/data-master/category/${slug}`);
                        this.grid.forceRender();
                    } catch (error) {
                        alert('Gagal menghapus kategori.');
                    }
                }
            }
        }));
    });
</script>
@endpush
```
