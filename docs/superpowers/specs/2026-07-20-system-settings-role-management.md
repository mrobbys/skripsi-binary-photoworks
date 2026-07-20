# Spec: Admin Backdoor — Manajemen Role (Role Management)

**Tanggal:** 2026-07-20
**Branch:** `feat/system-settings-role-management`
**Scope:** Backend (Controller, DTO, Routes) + Frontend (Alpine.js Modules, Blade Views)
**Stack:** Laravel 13 · PHP 8.3 · Spatie Laravel Permission · Alpine.js · Tailwind CSS v4 · Zod · Axios · SweetAlert2
**Design System:** ThoughtStream (flat, no rounded corners, no shadows, stone palette, border separators)

---

## Daftar Isi

1. [Aturan Bisnis & Logika Domain](#1-aturan-bisnis--logika-domain)
2. [Struktur Direktori](#2-struktur-direktori)
3. [Backend — Routes](#3-backend--routes)
4. [Backend — DTO](#4-backend--dto)
5. [Backend — Controller](#5-backend--controller)
6. [Frontend — JS Module Index (Alpine.js)](#6-frontend--js-module-index-alpinejs)
7. [Frontend — JS Module Form (Alpine.js + Zod)](#7-frontend--js-module-form-alpinejs--zod)
8. [Frontend — Blade Views](#8-frontend--blade-views)
9. [Catatan Modifikasi File Existing](#9-catatan-modifikasi-file-existing)

---

## 1. Aturan Bisnis & Logika Domain

### A. Sumber Data

Tabel `roles` dan `permissions` dari **Spatie Laravel Permission** dengan relasi pivot `role_has_permissions`.

```
roles:              id | name | guard_name | created_at | updated_at
permissions:        id | name | guard_name | created_at | updated_at
role_has_permissions: permission_id | role_id
```

Relasi:
- `Role` `hasMany` `Permissions` via pivot `role_has_permissions`
- Aggregate: `withCount('permissions')` untuk kolom "Jumlah Izin" di tabel index

### B. God Mode — Superadmin Filter

Role dengan `name = 'superadmin'` (dicocokkan dengan `strtolower`) **wajib disembunyikan** dari seluruh hasil query di `data()`. Role ini tidak pernah muncul di tabel, tidak bisa diedit, dan tidak bisa dihapus.

Proteksi hardcode di `update()` dan `destroy()`:
```php
abort_if(strtolower($role->name) === 'superadmin', 403, 'Role Superadmin tidak dapat dimodifikasi.');
```

### C. Tabel Index — Kolom

| No | Kolom | Sumber | Format |
|---|---|---|---|
| 1 | No | — | Nomor urut dengan offset pagination |
| 2 | Nama Role | `roles.name` | Teks plain |
| 3 | Jumlah Izin | `withCount('permissions')` | Format `"X Izin"` |
| 4 | Aksi | — | **3 tombol ikon inline** — BUKAN `<x-backdoor.table.actions>` dropdown |

### D. Aksi Baris Tabel (Inline — 3 Tombol Berjejer)

| Tombol | Ikon Remix | Warna | Aksi |
|---|---|---|---|
| Detail | `ri-eye-line` | `text-stone-600` | Anchor `href` ke `role.show_url` |
| Edit | `ri-pencil-line` | `text-yellow-600` | Anchor `href` ke `role.edit_url` |
| Hapus | `ri-delete-bin-line` | `text-red-600` | `confirmModal` SweetAlert2 → `DELETE` via Axios → `table.reload()` |

> URL `show_url` dan `edit_url` dikembalikan langsung dari DTO agar tidak ada logika URL di frontend.

### E. Halaman Show — Static Blade

Halaman **100% Blade server-side** (zero Alpine state, zero Axios). Menampilkan:
1. Heading utama: Nama Role
2. Sub-heading: jumlah total permission
3. Permission dikelompokkan per domain → masing-masing dalam kartu terpisah → badge `<span>` flat stone palette

### F. Halaman Create & Edit — Form Layout

Layout 2 kolom `lg:grid-cols-[1fr_2fr]`:

- **Panel Kiri — "Identitas Role"**: Satu input teks `name`.
- **Panel Kanan — "Isi Permission"**: Grid Domain Grouping Card; setiap card punya tombol "Pilih Semua" dan "Hapus Pilihan".

**Logika Pengelompokan Permission (Backend → View):**
```php
$groupedPermissions = Permission::all()->groupBy(
    fn ($permission) => explode('-', $permission->name)[0]
    // 'booking-view' → grup 'booking'
    // 'packageVariant-create' → grup 'packageVariant'
);
```

### G. Validasi Zod (Client-Side)

```javascript
const schema = z.object({
    name: z.string()
        .min(1, 'Nama role wajib diisi.')
        .max(50, 'Nama role maksimal 50 karakter.'),
    permissions: z.array(z.string())
        .min(1, 'Pilih minimal 1 izin untuk role ini.'),
});
```

**Posisi Error:**
- `name` error → teks merah kecil di **bawah label "Nama Role"**, di atas `<input>`.
- `permissions` error → teks merah kecil di **bawah heading "Isi Permission"**, tepat di atas grid card checkbox.

### H. Submit Flow

| Mode | Method | URL | Response sukses |
|---|---|---|---|
| Create | `POST` | `/backdoor/system-settings/roles` | `201` + `{ message, redirect }` |
| Edit | `PUT` | `/backdoor/system-settings/roles/{id}` | `200` + `{ message, redirect }` |

Setelah sukses: `Toast.fire({ icon: 'success' })` → `window.location.href = res.data.redirect`.
Error 422 (validasi server): Map `error.response.data.errors` ke `this.errors`.

### I. Search

Live search debounce 400ms terhadap `roles.name`. Filter superadmin tetap aktif saat search.

---

## 2. Struktur Direktori

```
app/Domains/SystemSettings/
├── DTOs/
│   ├── ActivityLogRowData.php               ← existing
│   └── RoleRowData.php                      ← BARU
└── Http/
    └── Controllers/
        ├── ActivityLogController.php         ← existing
        └── RoleManagementController.php      ← BARU

routes/backdoor/system-settings/
├── system-settings.php                      ← existing (require diisi)
├── activity-logs.php                        ← existing
├── user-management.php                      ← existing
└── role-management.php                      ← DIISI (sudah ada, masih kosong)

resources/js/features/backdoor/system-settings/
├── activity-logs/                           ← existing
└── role-management/
    ├── index/
    │   └── Index.js                         ← BARU
    └── form/
        └── Form.js                          ← BARU

resources/views/backdoor/system-settings/
├── activity-logs/                           ← existing
└── role/
    ├── index.blade.php                      ← BARU
    ├── show.blade.php                       ← BARU
    ├── create.blade.php                     ← BARU
    └── edit.blade.php                       ← BARU
```

---

## 3. Backend — Routes

**File:** `routes/backdoor/system-settings/role-management.php`

```php
<?php

use App\Domains\SystemSettings\Http\Controllers\RoleManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('backdoor/system-settings/roles')
    ->name('backdoor.system-settings.roles.')
    ->group(function () {

        // Halaman index (view)
        Route::get('/', [RoleManagementController::class, 'index'])
            ->name('index');

        // JSON endpoint untuk useDatatable
        Route::get('/data', [RoleManagementController::class, 'data'])
            ->name('data');

        // Form create (view)
        Route::get('/create', [RoleManagementController::class, 'create'])
            ->name('create');

        // Store role baru
        Route::post('/', [RoleManagementController::class, 'store'])
            ->name('store');

        // Halaman detail (view)
        Route::get('/{role}', [RoleManagementController::class, 'show'])
            ->name('show');

        // Form edit (view)
        Route::get('/{role}/edit', [RoleManagementController::class, 'edit'])
            ->name('edit');

        // Update role
        Route::put('/{role}', [RoleManagementController::class, 'update'])
            ->name('update');

        // Hapus role
        Route::delete('/{role}', [RoleManagementController::class, 'destroy'])
            ->name('destroy');
    });
```

> **Perhatian urutan route:** `/create` harus didaftarkan **sebelum** `/{role}` agar Laravel tidak menginterpretasikan string `"create"` sebagai `{role}` ID.

---

## 4. Backend — DTO

**File:** `app/Domains/SystemSettings/DTOs/RoleRowData.php`

DTO untuk satu baris tabel index. `extends Data` (Spatie Laravel Data) karena dikembalikan sebagai JSON collection.

```php
<?php

namespace App\Domains\SystemSettings\DTOs;

use Spatie\LaravelData\Data;
use Spatie\Permission\Models\Role;

class RoleRowData extends Data
{
    public function __construct(
        public readonly int    $id,
        public readonly string $name,
        public readonly int    $permissions_count,
        public readonly string $show_url,
        public readonly string $edit_url,
    ) {}

    public static function fromModel(Role $role): self
    {
        return new self(
            id:                $role->id,
            name:              $role->name,
            permissions_count: $role->permissions_count ?? 0,
            show_url:          route('backdoor.system-settings.roles.show', $role->id),
            edit_url:          route('backdoor.system-settings.roles.edit', $role->id),
        );
    }
}
```

> `permissions_count` menggunakan `?? 0` sebagai fallback untuk kondisi di mana `withCount` tidak dipanggil (defensive).

---

## 5. Backend — Controller

**File:** `app/Domains/SystemSettings/Http/Controllers/RoleManagementController.php`

```php
<?php

namespace App\Domains\SystemSettings\Http\Controllers;

use App\Domains\SystemSettings\DTOs\RoleRowData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Middleware('auth')]
#[Middleware('role:superadmin|owner')]
class RoleManagementController extends Controller
{
    /**
     * Halaman daftar role (view).
     */
    public function index(): View
    {
        return view('backdoor.system-settings.role.index');
    }

    /**
     * JSON endpoint untuk useDatatable.
     * Filter: role superadmin tidak pernah muncul.
     */
    public function data(Request $request): JsonResponse
    {
        $search = $request->input('search', '');
        $limit  = max(1, min($request->integer('limit', 10), 100));

        $query = Role::withCount('permissions')
            ->where('name', '!=', 'superadmin')
            ->when($search, fn ($q) => $q->where('name', 'ilike', "%{$search}%"))
            ->latest('created_at');

        $paginated = $query->paginate($limit);

        return response()->json([
            'data'         => RoleRowData::collect($paginated->items()),
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'total'        => $paginated->total(),
            'per_page'     => $paginated->perPage(),
        ]);
    }

    /**
     * Form tambah role (view).
     * Mengirim semua permission yang sudah dikelompokkan ke view.
     */
    public function create(): View
    {
        $groupedPermissions = Permission::all()->groupBy(
            fn ($permission) => explode('-', $permission->name)[0]
        );

        return view('backdoor.system-settings.role.create', compact('groupedPermissions'));
    }

    /**
     * Simpan role baru ke database.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:50', 'unique:roles,name'],
            'permissions'   => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions']);

        return response()->json([
            'message'  => "Role \"{$role->name}\" berhasil dibuat.",
            'redirect' => route('backdoor.system-settings.roles.index'),
        ], 201);
    }

    /**
     * Halaman detail role (view — 100% Blade statis).
     */
    public function show(Role $role): View
    {
        $role->load('permissions');

        $groupedPermissions = $role->permissions->groupBy(
            fn ($permission) => explode('-', $permission->name)[0]
        );

        return view('backdoor.system-settings.role.show', compact('role', 'groupedPermissions'));
    }

    /**
     * Form edit role (view).
     * Proteksi: superadmin tidak bisa diedit.
     */
    public function edit(Role $role): View
    {
        abort_if(strtolower($role->name) === 'superadmin', 403);

        $role->load('permissions');

        $groupedPermissions = Permission::all()->groupBy(
            fn ($permission) => explode('-', $permission->name)[0]
        );

        $activePermissions = $role->permissions->pluck('name')->toArray();

        return view('backdoor.system-settings.role.edit', compact('role', 'groupedPermissions', 'activePermissions'));
    }

    /**
     * Update role yang ada.
     * Proteksi hardcode: superadmin tidak bisa dimodifikasi.
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        abort_if(strtolower($role->name) === 'superadmin', 403, 'Role Superadmin tidak dapat dimodifikasi.');

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:50', "unique:roles,name,{$role->id}"],
            'permissions'   => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions']);

        return response()->json([
            'message'  => "Role \"{$role->name}\" berhasil diperbarui.",
            'redirect' => route('backdoor.system-settings.roles.index'),
        ]);
    }

    /**
     * Hapus role.
     * Proteksi hardcode: superadmin tidak bisa dihapus.
     */
    public function destroy(Role $role): JsonResponse
    {
        abort_if(strtolower($role->name) === 'superadmin', 403, 'Role Superadmin tidak dapat dihapus.');

        $name = $role->name;
        $role->delete();

        return response()->json(['message' => "Role \"{$name}\" berhasil dihapus."]);
    }
}
```

---

## 6. Frontend — JS Module Index (Alpine.js)

**File:** `resources/js/features/backdoor/system-settings/role-management/index/Index.js`

```javascript
import useDatatable from "@/lib/useDatatable";
import { confirmModal, Toast } from "@/lib/sweetalert";
import route from "@/lib/route";
import axios from "@/lib/axiosInstance";

export default function Index(Alpine) {
  const { state: table, ...methods } = useDatatable(
    Alpine,
    route("backdoor.system-settings.roles.data"),
    { debounceMs: 400 }
  );
  Object.assign(table, methods);

  const deleteRole = async (id, name) => {
    const result = await confirmModal(
      `Hapus Role "${name}"?`,
      'Role yang dihapus tidak dapat dikembalikan dan seluruh pengguna yang memiliki role ini akan kehilangan aksesnya.',
      'warning',
      'Ya, Hapus'
    );

    if (!result.isConfirmed) return;

    try {
      await axios.delete(route("backdoor.system-settings.roles.destroy", id));
      Toast.fire({ icon: 'success', title: `Role "${name}" berhasil dihapus.` });
      table.reload();
    } catch (error) {
      const msg = error.response?.data?.message ?? 'Gagal menghapus role.';
      Toast.fire({ icon: 'error', title: msg });
    }
  };

  return {
    table,
    deleteRole,
    init() {
      table.fetch();
    },
  };
}
```

---

## 7. Frontend — JS Module Form (Alpine.js + Zod)

**File:** `resources/js/features/backdoor/system-settings/role-management/form/Form.js`

Digunakan bersama oleh `create.blade.php` dan `edit.blade.php` (keduanya `x-data="Form"`).
Mode dan ID role ditentukan saat pemanggilan `submit(mode, roleId)` dari Blade.

```javascript
import { z } from 'zod';
import { Toast } from "@/lib/sweetalert";
import route from "@/lib/route";
import axios from "@/lib/axiosInstance";

const schema = z.object({
    name: z.string()
        .min(1, 'Nama role wajib diisi.')
        .max(50, 'Nama role maksimal 50 karakter.'),
    permissions: z.array(z.string())
        .min(1, 'Pilih minimal 1 izin untuk role ini.'),
});

export default function Form(Alpine) {
  return {
    name: '',
    permissions: [],
    errors: {},
    isLoading: false,

    /**
     * Dipanggil dari edit.blade.php via x-init untuk pre-populate data.
     * @param {{ name: string, permissions: string[] }} data
     */
    setInitialData(data) {
      this.name        = data.name ?? '';
      this.permissions = data.permissions ?? [];
    },

    togglePermission(permName) {
      const idx = this.permissions.indexOf(permName);
      if (idx === -1) {
        this.permissions.push(permName);
      } else {
        this.permissions.splice(idx, 1);
      }
    },

    isChecked(permName) {
      return this.permissions.includes(permName);
    },

    selectAll(permNames) {
      permNames.forEach(name => {
        if (!this.permissions.includes(name)) this.permissions.push(name);
      });
    },

    deselectAll(permNames) {
      this.permissions = this.permissions.filter(p => !permNames.includes(p));
    },

    /**
     * Validasi Zod → Submit via Axios.
     * @param {'create'|'edit'} mode
     * @param {number|null} roleId - Hanya diperlukan saat mode 'edit'
     */
    async submit(mode, roleId = null) {
      this.errors = {};

      const parsed = schema.safeParse({
        name:        this.name,
        permissions: this.permissions,
      });

      if (!parsed.success) {
        parsed.error.issues.forEach(issue => {
          const key = issue.path[0];
          if (!this.errors[key]) this.errors[key] = issue.message;
        });
        return;
      }

      this.isLoading = true;

      try {
        const url    = mode === 'edit'
          ? route('backdoor.system-settings.roles.update', roleId)
          : route('backdoor.system-settings.roles.store');
        const method = mode === 'edit' ? 'put' : 'post';

        const res = await axios[method](url, {
          name:        this.name,
          permissions: this.permissions,
        });

        Toast.fire({ icon: 'success', title: res.data.message });
        window.location.href = res.data.redirect;
      } catch (error) {
        if (error.response?.status === 422) {
          const serverErrors = error.response.data.errors ?? {};
          Object.keys(serverErrors).forEach(key => {
            this.errors[key] = serverErrors[key][0];
          });
        } else {
          const msg = error.response?.data?.message ?? 'Terjadi kesalahan server.';
          Toast.fire({ icon: 'error', title: msg });
        }
      } finally {
        this.isLoading = false;
      }
    },
  };
}
```

---

## 8. Frontend — Blade Views

### 8A. `index.blade.php`

**File:** `resources/views/backdoor/system-settings/role/index.blade.php`

```blade
@php
  $breadcrumbs = [
    ['label' => 'Dashboard',         'url' => route('backdoor.dashboard')],
    ['label' => 'Pengaturan Sistem', 'url' => '#'],
    ['label' => 'Manajemen Role',    'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Manajemen Role"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/system-settings/role-management/index/Index"
>
  <x-slot:content>
    <div x-data="Index" x-cloak class="w-full space-y-6">

      <x-backdoor.shared.page-header title="Manajemen Role" />

      {{-- Table Card --}}
      <div class="relative overflow-visible border border-stone-200 bg-stone-50 p-6">

        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari nama role..." />
          </x-slot:left>
          <x-slot:right>
            <a
              href="{{ route('backdoor.system-settings.roles.create') }}"
              class="inline-flex items-center gap-2 bg-stone-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-stone-900"
            >
              <i class="ri-add-line text-base"></i>
              Tambah Role
            </a>
          </x-slot:right>
        </x-backdoor.table.header>

        <x-backdoor.table.container headers="No,Nama Role,Jumlah Izin,Aksi">
          <template x-for="(role, index) in table.data" :key="role.id">
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

              {{-- Nama Role --}}
              <x-backdoor.table.cell
                class="font-semibold text-stone-900"
                x-text="role.name"
              />

              {{-- Jumlah Izin --}}
              <x-backdoor.table.cell
                class="text-sm text-stone-600"
                x-text="role.permissions_count + ' Izin'"
              />

              {{-- Aksi: 3 tombol ikon inline (BUKAN dropdown) --}}
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">

                  {{-- Detail --}}
                  <a
                    :href="role.show_url"
                    class="text-stone-600 transition hover:text-stone-900"
                    title="Lihat Detail"
                  >
                    <i class="ri-eye-line text-lg"></i>
                  </a>

                  {{-- Edit --}}
                  <a
                    :href="role.edit_url"
                    class="text-yellow-600 transition hover:text-yellow-800"
                    title="Edit Role"
                  >
                    <i class="ri-pencil-line text-lg"></i>
                  </a>

                  {{-- Hapus --}}
                  <button
                    type="button"
                    x-on:click="deleteRole(role.id, role.name)"
                    class="text-red-600 transition hover:text-red-800"
                    title="Hapus Role"
                  >
                    <i class="ri-delete-bin-line text-lg"></i>
                  </button>

                </div>
              </td>

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

### 8B. `show.blade.php`

**File:** `resources/views/backdoor/system-settings/role/show.blade.php`

Halaman **100% Blade statis** — tanpa `x-data`, tanpa Alpine state, tanpa Axios.

```blade
@php
  $breadcrumbs = [
    ['label' => 'Dashboard',         'url' => route('backdoor.dashboard')],
    ['label' => 'Pengaturan Sistem', 'url' => '#'],
    ['label' => 'Manajemen Role',    'url' => route('backdoor.system-settings.roles.index')],
    ['label' => $role->name,         'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Detail Role — {{ $role->name }}"
  :breadcrumbs="$breadcrumbs"
>
  <x-slot:content>
    <div class="w-full space-y-6">

      {{-- Header + Back Button --}}
      <div class="flex items-center gap-4">
        <a
          href="{{ route('backdoor.system-settings.roles.index') }}"
          class="flex h-8 w-8 items-center justify-center border border-stone-300 text-stone-500 transition hover:bg-stone-200"
        >
          <i class="ri-arrow-left-line text-base"></i>
        </a>
        <div>
          <h1 class="text-lg font-bold tracking-tight text-stone-900">{{ $role->name }}</h1>
          <p class="text-sm text-stone-500">{{ $role->permissions->count() }} izin terdaftar</p>
        </div>
      </div>

      {{-- Permission Badges per Domain Group --}}
      <div class="space-y-4">
        @forelse ($groupedPermissions as $group => $permissions)
          <div class="border border-stone-200 bg-stone-50 p-5">
            <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-stone-500">
              {{ ucfirst($group) }}
            </h2>
            <div class="flex flex-wrap gap-2">
              @foreach ($permissions as $permission)
                <span class="border border-stone-300 bg-white px-3 py-1 text-xs font-medium text-stone-700">
                  {{ $permission->name }}
                </span>
              @endforeach
            </div>
          </div>
        @empty
          <div class="border border-stone-200 bg-stone-50 p-5">
            <p class="text-sm text-stone-500">Role ini belum memiliki izin apapun.</p>
          </div>
        @endforelse
      </div>

    </div>
  </x-slot:content>
</x-layouts.backdoor.index>
```

---

### 8C. `create.blade.php`

**File:** `resources/views/backdoor/system-settings/role/create.blade.php`

```blade
@php
  $breadcrumbs = [
    ['label' => 'Dashboard',         'url' => route('backdoor.dashboard')],
    ['label' => 'Pengaturan Sistem', 'url' => '#'],
    ['label' => 'Manajemen Role',    'url' => route('backdoor.system-settings.roles.index')],
    ['label' => 'Tambah Role',       'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Tambah Role"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/system-settings/role-management/form/Form"
>
  <x-slot:content>
    <div x-data="Form" x-cloak class="w-full space-y-6">

      {{-- Header + Back --}}
      <div class="flex items-center gap-4">
        <a
          href="{{ route('backdoor.system-settings.roles.index') }}"
          class="flex h-8 w-8 items-center justify-center border border-stone-300 text-stone-500 transition hover:bg-stone-200"
        >
          <i class="ri-arrow-left-line text-base"></i>
        </a>
        <div>
          <h1 class="text-lg font-bold tracking-tight text-stone-900">Tambah Role Baru</h1>
          <p class="text-sm text-stone-500">Buat role dengan kumpulan permission yang dikonfigurasi.</p>
        </div>
      </div>

      {{-- Form Body --}}
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_2fr]">

        {{-- Panel Kiri: Identitas Role --}}
        <div class="border border-stone-200 bg-stone-50 p-6">
          <div class="mb-4 border-b border-stone-200 pb-3">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-stone-700">Identitas Role</h2>
          </div>

          <div class="space-y-1">
            <label class="text-xs font-semibold uppercase tracking-wider text-stone-600">
              Nama Role
            </label>
            {{-- Error name: di bawah label, di atas input --}}
            <p
              class="text-xs text-red-500"
              x-show="errors.name"
              x-text="errors.name"
            ></p>
            <input
              type="text"
              x-model="name"
              placeholder="Contoh: Kasir Senior"
              class="w-full border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800 outline-none placeholder:text-stone-400 focus:border-stone-500"
              :class="{ 'border-red-400 focus:border-red-400': errors.name }"
            />
          </div>
        </div>

        {{-- Panel Kanan: Matriks Permission --}}
        <div class="border border-stone-200 bg-stone-50 p-6">
          <div class="mb-1 border-b border-stone-200 pb-3">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-stone-700">Isi Permission</h2>
          </div>
          {{-- Error permissions: di bawah heading "Isi Permission", di atas grid card --}}
          <p
            class="mb-4 text-xs text-red-500"
            x-show="errors.permissions"
            x-text="errors.permissions"
          ></p>

          <div class="space-y-4">
            @foreach ($groupedPermissions as $group => $permissions)
              @php $permNames = $permissions->pluck('name')->toArray(); @endphp
              <div class="border border-stone-200 bg-white p-4">
                <div class="mb-3 flex items-center justify-between">
                  <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-500">
                    {{ ucfirst($group) }}
                  </h3>
                  <div class="flex gap-3">
                    <button
                      type="button"
                      x-on:click="selectAll({{ json_encode($permNames) }})"
                      class="text-xs text-stone-500 underline transition hover:text-stone-800"
                    >Pilih Semua</button>
                    <button
                      type="button"
                      x-on:click="deselectAll({{ json_encode($permNames) }})"
                      class="text-xs text-stone-400 underline transition hover:text-stone-700"
                    >Hapus Pilihan</button>
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-y-2 gap-x-3 sm:grid-cols-3">
                  @foreach ($permissions as $permission)
                    <label class="flex cursor-pointer items-center gap-2 text-sm text-stone-700">
                      <input
                        type="checkbox"
                        :checked="isChecked('{{ $permission->name }}')"
                        x-on:change="togglePermission('{{ $permission->name }}')"
                        class="h-4 w-4 border-stone-300 accent-stone-800"
                      />
                      <span class="truncate">{{ $permission->name }}</span>
                    </label>
                  @endforeach
                </div>
              </div>
            @endforeach
          </div>
        </div>

      </div>

      {{-- Submit & Cancel --}}
      <div class="flex items-center gap-3">
        <button
          type="button"
          x-on:click="submit('create')"
          :disabled="isLoading"
          class="bg-stone-800 px-6 py-2 text-sm font-semibold text-white transition hover:bg-stone-900 disabled:cursor-not-allowed disabled:opacity-60"
        >
          <span x-show="!isLoading">Simpan Role</span>
          <span x-show="isLoading" x-cloak>Menyimpan...</span>
        </button>
        <a
          href="{{ route('backdoor.system-settings.roles.index') }}"
          class="border border-stone-300 px-6 py-2 text-sm font-semibold text-stone-600 transition hover:bg-stone-100"
        >
          Batal
        </a>
      </div>

    </div>
  </x-slot:content>
</x-layouts.backdoor.index>
```

---

### 8D. `edit.blade.php`

**File:** `resources/views/backdoor/system-settings/role/edit.blade.php`

Identik secara struktural dengan `create.blade.php`. Perbedaan utama:
1. `x-init` untuk pre-populate `name` dan `permissions` via `setInitialData()`
2. Judul halaman menyertakan nama role
3. Tombol submit memanggil `submit('edit', roleId)`

```blade
@php
  $breadcrumbs = [
    ['label' => 'Dashboard',               'url' => route('backdoor.dashboard')],
    ['label' => 'Pengaturan Sistem',       'url' => '#'],
    ['label' => 'Manajemen Role',          'url' => route('backdoor.system-settings.roles.index')],
    ['label' => 'Edit: ' . $role->name,   'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Edit Role — {{ $role->name }}"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/system-settings/role-management/form/Form"
>
  <x-slot:content>
    <div
      x-data="Form"
      x-init="setInitialData({ name: '{{ addslashes($role->name) }}', permissions: {{ json_encode($activePermissions) }} })"
      x-cloak
      class="w-full space-y-6"
    >

      {{-- Header + Back --}}
      <div class="flex items-center gap-4">
        <a
          href="{{ route('backdoor.system-settings.roles.index') }}"
          class="flex h-8 w-8 items-center justify-center border border-stone-300 text-stone-500 transition hover:bg-stone-200"
        >
          <i class="ri-arrow-left-line text-base"></i>
        </a>
        <div>
          <h1 class="text-lg font-bold tracking-tight text-stone-900">Edit Role: {{ $role->name }}</h1>
          <p class="text-sm text-stone-500">Perbarui nama dan konfigurasi permission role ini.</p>
        </div>
      </div>

      {{-- Form Body (struktur panel identik dengan create.blade.php) --}}
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_2fr]">

        {{-- Panel Kiri: Identitas Role --}}
        <div class="border border-stone-200 bg-stone-50 p-6">
          <div class="mb-4 border-b border-stone-200 pb-3">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-stone-700">Identitas Role</h2>
          </div>
          <div class="space-y-1">
            <label class="text-xs font-semibold uppercase tracking-wider text-stone-600">Nama Role</label>
            <p class="text-xs text-red-500" x-show="errors.name" x-text="errors.name"></p>
            <input
              type="text"
              x-model="name"
              class="w-full border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800 outline-none placeholder:text-stone-400 focus:border-stone-500"
              :class="{ 'border-red-400 focus:border-red-400': errors.name }"
            />
          </div>
        </div>

        {{-- Panel Kanan: Matriks Permission --}}
        <div class="border border-stone-200 bg-stone-50 p-6">
          <div class="mb-1 border-b border-stone-200 pb-3">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-stone-700">Isi Permission</h2>
          </div>
          <p class="mb-4 text-xs text-red-500" x-show="errors.permissions" x-text="errors.permissions"></p>

          <div class="space-y-4">
            @foreach ($groupedPermissions as $group => $permissions)
              @php $permNames = $permissions->pluck('name')->toArray(); @endphp
              <div class="border border-stone-200 bg-white p-4">
                <div class="mb-3 flex items-center justify-between">
                  <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-500">
                    {{ ucfirst($group) }}
                  </h3>
                  <div class="flex gap-3">
                    <button type="button" x-on:click="selectAll({{ json_encode($permNames) }})" class="text-xs text-stone-500 underline transition hover:text-stone-800">Pilih Semua</button>
                    <button type="button" x-on:click="deselectAll({{ json_encode($permNames) }})" class="text-xs text-stone-400 underline transition hover:text-stone-700">Hapus Pilihan</button>
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-y-2 gap-x-3 sm:grid-cols-3">
                  @foreach ($permissions as $permission)
                    <label class="flex cursor-pointer items-center gap-2 text-sm text-stone-700">
                      <input
                        type="checkbox"
                        :checked="isChecked('{{ $permission->name }}')"
                        x-on:change="togglePermission('{{ $permission->name }}')"
                        class="h-4 w-4 border-stone-300 accent-stone-800"
                      />
                      <span class="truncate">{{ $permission->name }}</span>
                    </label>
                  @endforeach
                </div>
              </div>
            @endforeach
          </div>
        </div>

      </div>

      {{-- Submit & Cancel --}}
      <div class="flex items-center gap-3">
        <button
          type="button"
          x-on:click="submit('edit', {{ $role->id }})"
          :disabled="isLoading"
          class="bg-stone-800 px-6 py-2 text-sm font-semibold text-white transition hover:bg-stone-900 disabled:cursor-not-allowed disabled:opacity-60"
        >
          <span x-show="!isLoading">Perbarui Role</span>
          <span x-show="isLoading" x-cloak>Memperbarui...</span>
        </button>
        <a
          href="{{ route('backdoor.system-settings.roles.index') }}"
          class="border border-stone-300 px-6 py-2 text-sm font-semibold text-stone-600 transition hover:bg-stone-100"
        >
          Batal
        </a>
      </div>

    </div>
  </x-slot:content>
</x-layouts.backdoor.index>
```

---

## 9. Catatan Modifikasi File Existing

### A. `routes/backdoor/system-settings/role-management.php`

File **sudah ada namun kosong**. Isi penuh dengan routes dari Bagian 3.

### B. Sidebar Link

Tambahkan di `resources/views/components/layouts/backdoor/components/sidebar-links.blade.php`:

```blade
{{-- role management start --}}
<x-layouts.backdoor.components.sidebar-link-item
  :href="route('backdoor.system-settings.roles.index')"
  :active="request()->routeIs('backdoor.system-settings.roles.*')"
  icon='ri-shield-user-line'
  title='Manajemen Role'
/>
{{-- role management end --}}
```

### C. Registrasi Alpine Component

Daftarkan di titik registrasi Alpine yang sudah ada di proyek (biasanya `resources/js/app.js` atau bootstrap Alpine):

```javascript
import Index from '@/features/backdoor/system-settings/role-management/index/Index';
import Form  from '@/features/backdoor/system-settings/role-management/form/Form';

Alpine.data('Index', Index);
Alpine.data('Form', Form);
```

### D. `form-script.blade.php`

**Tidak diperlukan.** Seluruh logika Alpine (Zod, toggle checkbox, submit) sudah dipisahkan ke `Form.js` yang di-load via `jsModule`. Menambah `form-script.blade.php` hanya akan menduplikasi logika.

---

## Catatan Implementasi

### A. Urutan Route `/create` vs `/{role}`

Route `/create` **wajib didaftarkan sebelum** `/{role}`. Jika dibalik, Laravel akan mencoba me-resolve string `"create"` sebagai model binding dan memunculkan `ModelNotFoundException`.

### B. `ilike` vs `LIKE` di Postgres

Query search menggunakan `ilike` (case-insensitive, Postgres-native). Konsisten dengan pola di seluruh controller lain dalam proyek ini.

### C. `withCount` Mencegah N+1

Kolom "Jumlah Izin" di tabel index menggunakan `Role::withCount('permissions')` satu query, bukan `$role->permissions()->count()` per baris.

### D. `addslashes()` di `x-init` Edit

`addslashes($role->name)` pada `x-init` di `edit.blade.php` mencegah JS syntax error jika nama role mengandung tanda petik (misalnya: `O'Brien`).

### E. Superadmin: Filter vs Proteksi

- **Filter di `data()`** → Superadmin tidak pernah tampil di UI (UX layer)
- **`abort_if` di `edit()`, `update()`, `destroy()`** → Proteksi level server bahkan jika request ditembak langsung via Postman/Curl (security layer)

Kedua lapisan ini **saling melengkapi**, bukan redundan.
