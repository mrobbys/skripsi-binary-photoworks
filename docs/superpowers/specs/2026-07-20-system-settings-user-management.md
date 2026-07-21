# Spec: Admin Backdoor — Manajemen User (User Management)

**Tanggal:** 2026-07-20
**Branch:** `feat/system-settings-user-management`
**Scope:** Backend (Controller, Service, DTO, FormRequests, Routes) + Frontend (Alpine.js Modules, Blade View)
**Stack:** Laravel 13 · PHP 8.3 · Spatie Laravel Permission · Spatie Activitylog · Alpine.js · Tailwind CSS v4 · Zod · Axios · SweetAlert2 · Choices.js
**Design System:** ThoughtStream (flat, no rounded corners, no shadows, stone palette, border separators)

---

## Daftar Isi

1. [Aturan Bisnis & Logika Domain](#1-aturan-bisnis--logika-domain)
2. [Struktur Direktori](#2-struktur-direktori)
3. [Backend — Routes](#3-backend--routes)
4. [Backend — DTO](#4-backend--dto)
5. [Backend — FormRequests](#5-backend--formrequests)
6. [Backend — Service](#6-backend--service)
7. [Backend — Controller](#7-backend--controller)
8. [Frontend — JS Modules (Alpine.js)](#8-frontend--js-modules-alpinejs)
9. [Frontend — Blade View](#9-frontend--blade-view)
10. [Catatan Modifikasi File Existing](#10-catatan-modifikasi-file-existing)

---

## 1. Aturan Bisnis & Logika Domain

### A. Sumber Data

Tabel `users` yang berelasi dengan tabel `roles` via Spatie Permission (`model_has_roles`).

```
users: id | uuid | name | email | phone | password | google_id | google_token | created_at | updated_at
roles: id | name | guard_name
model_has_roles: role_id | model_type | model_id
```

Relasi di `User.php`: `HasRoles` (Spatie), `LogsActivity` (Spatie Activitylog).

### B. Filter Data Tabel

Query wajib mengecualikan dua kelompok user:

| Role | Alasan |
|---|---|
| `superadmin` | Eksplisit wajib disembunyikan per requirement |

```php
// whereDoesntHave untuk exclude role superadmin
$query = User::whereDoesntHave('roles', fn ($q) =>
    $q->where('name', 'superadmin')
);
```

### C. Tabel Index — Kolom

| No | Kolom | Sumber | Format |
|---|---|---|---|
| 1 | No | — | Nomor urut dengan offset pagination |
| 2 | Nama | `users.name` | Teks |
| 3 | Email | `users.email` | Teks |
| 4 | Nomor HP | `users.phone` | Teks, `-` jika null |
| 5 | Role | `roles.name` via relasi | Badge flat berisi nama role (uppercase) |
| 6 | Aksi | — | Dropdown `<x-backdoor.table.actions>` dengan 3 opsi |

### D. Badge Warna Role

| Role | Kelas Tailwind |
|---|---|
| `admin` | `bg-sky-100 text-sky-700 border border-sky-200` |
| `owner` | `bg-amber-100 text-amber-700 border border-amber-200` |
| Default | `bg-stone-100 text-stone-600 border border-stone-200` |

Fungsi helper di JS: `roleBadgeClass(roleName)`.

### E. Aksi Baris Tabel (Dropdown 3 Opsi)

| Opsi | Aksi |
|---|---|
| **Edit** | Buka Drawer, hydrate form dengan data baris yang diklik |
| **Reset Password** | `confirmModal` SweetAlert2 → `PATCH` ke API → reset ke `Password123` |
| **Hapus** | `confirmModal` SweetAlert2 → `DELETE` ke API → `table.reload()` |

> **Fitur "Detail" ditiadakan sepenuhnya.** Tidak ada halaman show.

### F. Drawer Form — Input Fields

| No | Field | Tipe | Keterangan |
|---|---|---|---|
| 1 | Nama Lengkap | `input[type=text]` | Wajib |
| 2 | Email | `input[type=email]` | Wajib, unique |
| 3 | Nomor HP | `input[type=tel]` | Wajib, unique, angka saja |
| 4 | Role | `select` (Choices.js) | Wajib, single-select. Sumber: roles dari server (exclude superadmin) |
| 5 | Password | `input[type=text]` readonly | Value hardcoded `Password123`. Hanya tampil saat mode **Create** |

Teks bantuan di bawah field Password (mode Create):
> _"Kata sandi default untuk akun baru. Pengguna dapat mengubahnya secara mandiri di halaman profil."_

### G. Self-Harm Prevention (Proteksi Hapus Diri Sendiri)

Di dalam `destroy()`, controller wajib memblokir jika target user ID sama dengan authenticated user ID:

```php
abort_if($user->id === auth()->id(), 403, 'Tidak dapat menghapus akun sendiri.');
```

### H. Activity Log — Pencatatan Eksplisit

Setiap operasi sukses dicatat secara eksplisit melalui Spatie Activitylog di controller. Model `User` sudah dilengkapi `LogsActivity` trait (auto-log create/update/delete), namun untuk **Reset Password** dan **kejelasan audit**, controller tetap mencatat secara eksplisit.

| Operasi | Log Description | Metode |
|---|---|---|
| Create | `created` | Auto via model trait |
| Update | `updated` | Auto via model trait (`logOnlyDirty`) |
| Hapus | `deleted` | Auto via model trait + explicit di controller |
| Reset Password | `reset-password` | Explicit di controller (trait tidak mendeskripsikan konteks ini) |

### I. Default Password — Reset Password

Reset Password mengembalikan sandi ke nilai tetap: **`Password123`** (sesuai requirement). Di-hash via `Hash::make('Password123')` sebelum disimpan.

### J. Validasi Zod (Client-Side)

```javascript
const schema = z.object({
    name:  z.string().min(3, 'Nama minimal 3 karakter.').max(255),
    email: z.string().email('Format email tidak valid.').min(1),
    phone: z.string()
               .regex(/^[0-9]+$/, 'Nomor HP hanya boleh angka.')
               .min(8, 'Nomor HP minimal 8 digit.')
               .max(15, 'Nomor HP maksimal 15 digit.'),
    role:  z.string().min(1, 'Role wajib dipilih.'),
});
```

---

## 2. Struktur Direktori

```
app/Domains/SystemSettings/
├── DTOs/
│   ├── ActivityLogRowData.php              ← existing
│   ├── RoleRowData.php                     ← existing (role management)
│   └── UserRowData.php                     ← BARU
├── Http/
│   ├── Controllers/
│   │   ├── ActivityLogController.php       ← existing
│   │   ├── RoleManagementController.php    ← existing
│   │   └── UserManagementController.php    ← BARU
│   └── Requests/
│       ├── StoreUserRequest.php            ← BARU
│       └── UpdateUserRequest.php           ← BARU
└── Services/
    └── UserManagementService.php           ← BARU

app/Domains/User/
└── Repositories/
    └── UserRepository.php                  ← DIGUNAKAN (sudah ada, tidak dimodifikasi)

routes/backdoor/system-settings/
└── user-management.php                     ← DIISI (sudah ada, masih kosong)

resources/js/features/backdoor/system-settings/user-management/
├── useState.js                             ← BARU
├── useForm.js                              ← BARU
├── useActions.js                           ← BARU
└── Index.js                               ← BARU

resources/views/backdoor/system-settings/user/
└── index.blade.php                         ← BARU
```

---

## 3. Backend — Routes

**File:** `routes/backdoor/system-settings/user-management.php`

```php
<?php

use App\Domains\SystemSettings\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('backdoor/system-settings/users')
    ->name('backdoor.system-settings.users.')
    ->group(function () {

        // Halaman index (SPA — satu halaman untuk semua CRUD)
        Route::get('/', [UserManagementController::class, 'index'])
            ->name('index');

        // JSON endpoint untuk useDatatable
        Route::get('/data', [UserManagementController::class, 'data'])
            ->name('data');

        // Simpan user baru
        Route::post('/', [UserManagementController::class, 'store'])
            ->name('store');

        // Update user yang ada
        Route::put('/{user}', [UserManagementController::class, 'update'])
            ->name('update');

        // Hapus user (hard delete)
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])
            ->name('destroy');

        // Reset password ke Password123
        Route::patch('/{user}/reset-password', [UserManagementController::class, 'resetPassword'])
            ->name('reset-password');
    });
```

---

## 4. Backend — DTO

**File:** `app/Domains/SystemSettings/DTOs/UserRowData.php`

DTO untuk satu baris tabel. `extends Data` (Spatie Laravel Data) karena dikembalikan sebagai JSON collection via `UserRowData::collect()`.

```php
<?php

namespace App\Domains\SystemSettings\DTOs;

use App\Domains\User\Models\User;
use Spatie\LaravelData\Data;

class UserRowData extends Data
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $name,
        public readonly string  $email,
        public readonly ?string $phone,
        public readonly string  $role,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id:    $user->id,
            name:  $user->name,
            email: $user->email,
            phone: $user->phone ?? '-',
            role:  $user->getRoleNames()->first() ?? '-',
        );
    }
}
```

> `getRoleNames()` mengembalikan `Collection` berisi nama-nama role user. Karena satu user hanya 1 role, kita ambil `->first()`.

---

## 5. Backend — FormRequests

### 5A. `StoreUserRequest.php`

**File:** `app/Domains/SystemSettings/Http/Requests/StoreUserRequest.php`

```php
<?php

namespace App\Domains\SystemSettings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:15', 'regex:/^[0-9]+$/', 'unique:users,phone'],
            'role'  => ['required', 'string', 'exists:roles,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Nama lengkap wajib diisi.',
            'name.min'       => 'Nama minimal 3 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Email sudah digunakan.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'phone.regex'    => 'Nomor HP hanya boleh berisi angka.',
            'phone.unique'   => 'Nomor HP sudah digunakan.',
            'role.required'  => 'Role wajib dipilih.',
            'role.exists'    => 'Role tidak valid.',
        ];
    }
}
```

### 5B. `UpdateUserRequest.php`

**File:** `app/Domains/SystemSettings/Http/Requests/UpdateUserRequest.php`

```php
<?php

namespace App\Domains\SystemSettings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Ambil ID user dari route model binding untuk unique ignore
        $userId = $this->route('user')?->id;

        return [
            'name'  => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', "unique:users,email,{$userId}"],
            'phone' => ['required', 'string', 'max:15', 'regex:/^[0-9]+$/', "unique:users,phone,{$userId}"],
            'role'  => ['required', 'string', 'exists:roles,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Nama lengkap wajib diisi.',
            'name.min'       => 'Nama minimal 3 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Email sudah digunakan akun lain.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'phone.regex'    => 'Nomor HP hanya boleh berisi angka.',
            'phone.unique'   => 'Nomor HP sudah digunakan akun lain.',
            'role.required'  => 'Role wajib dipilih.',
            'role.exists'    => 'Role tidak valid.',
        ];
    }
}
```

---

## 6. Backend — Service

**File:** `app/Domains/SystemSettings/Services/UserManagementService.php`

Service ini mengorkestrasi logika bisnis: buat user, update, hapus, dan reset password. Bergantung pada `UserRepository` yang sudah ada.

```php
<?php

namespace App\Domains\SystemSettings\Services;

use App\Domains\User\Models\User;
use App\Domains\User\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    /**
     * Buat user internal baru dengan password default dan assign role.
     */
    public function store(array $data): User
    {
        $user = $this->userRepository->create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'],
            'password' => Hash::make('Password123'),
        ]);

        $user->syncRoles($data['role']);

        return $user;
    }

    /**
     * Update data user dan sinkronisasi role-nya.
     */
    public function update(User $user, array $data): void
    {
        $user->update([
            'name'  => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ]);

        $user->syncRoles($data['role']);
    }

    /**
     * Hard delete user dari database.
     */
    public function destroy(User $user): void
    {
        $user->delete();
    }

    /**
     * Reset password user kembali ke nilai default Password123.
     */
    public function resetPassword(User $user): void
    {
        $user->update([
            'password' => Hash::make('Password123'),
        ]);
    }
}
```

> **Catatan `syncRoles`:** Metode milik Spatie ini sudah menangani detach semua role lama sebelum assign role baru. Lebih aman daripada `assignRole()` untuk kasus edit yang bisa mengganti role.

---

## 7. Backend — Controller

**File:** `app/Domains/SystemSettings/Http/Controllers/UserManagementController.php`

```php
<?php

namespace App\Domains\SystemSettings\Http\Controllers;

use App\Domains\SystemSettings\DTOs\UserRowData;
use App\Domains\SystemSettings\Http\Requests\StoreUserRequest;
use App\Domains\SystemSettings\Http\Requests\UpdateUserRequest;
use App\Domains\SystemSettings\Services\UserManagementService;
use App\Domains\User\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

#[Middleware('auth')]
#[Middleware('role:superadmin|owner')]
class UserManagementController extends Controller
{
    public function __construct(
        private readonly UserManagementService $service,
    ) {}

    /**
     * Halaman utama SPA — satu halaman untuk semua CRUD.
     * Kirim daftar role yang tersedia (exclude superadmin) ke view.
     */
    public function index(): View
    {
        $roles = Role::where('name', '!=', 'superadmin')->get(['id', 'name']);

        return view('backdoor.system-settings.user.index', compact('roles'));
    }

    /**
     * JSON endpoint untuk useDatatable.
     * Filter: superadmin tidak pernah muncul.
     */
    public function data(Request $request): JsonResponse
    {
        $search = $request->input('search', '');
        $limit  = max(1, min($request->integer('limit', 10), 100));

        $query = User::with('roles')
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'superadmin'))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'ilike', "%{$search}%")
                          ->orWhere('email', 'ilike', "%{$search}%")
                          ->orWhere('phone', 'ilike', "%{$search}%");
                });
            })
            ->latest('created_at');

        $paginated = $query->paginate($limit);

        return response()->json([
            'data'         => UserRowData::collect($paginated->items()),
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'total'        => $paginated->total(),
            'per_page'     => $paginated->perPage(),
        ]);
    }

    /**
     * Simpan user baru.
     * Activity log otomatis via model trait (event: created).
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->service->store($request->validated());

        return response()->json([
            'message' => "User \"{$user->name}\" berhasil ditambahkan.",
        ], 201);
    }

    /**
     * Update data user yang ada.
     * Activity log otomatis via model trait (event: updated, hanya field yang berubah).
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->service->update($user, $request->validated());

        return response()->json([
            'message' => "Data user \"{$user->name}\" berhasil diperbarui.",
        ]);
    }

    /**
     * Hard delete user.
     * Proteksi self-harm: tidak bisa menghapus akun sendiri.
     * Activity log eksplisit karena trait log `deleted` event tanpa context yang cukup.
     */
    public function destroy(User $user): JsonResponse
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak dapat menghapus akun Anda sendiri.');

        $name = $user->name;

        $this->service->destroy($user);

        // Explicit activity log untuk delete dengan konteks yang jelas
        activity('user')
            ->causedBy(auth()->user())
            ->withProperties(['deleted_name' => $name])
            ->log('deleted');

        return response()->json([
            'message' => "User \"{$name}\" berhasil dihapus.",
        ]);
    }

    /**
     * Reset password user ke Password123.
     * Activity log eksplisit karena auto-log hanya mencatat hash baru yang tidak bermakna.
     */
    public function resetPassword(User $user): JsonResponse
    {
        $this->service->resetPassword($user);

        activity('user')
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log('reset-password');

        return response()->json([
            'message' => "Password \"{$user->name}\" berhasil direset ke Password123.",
        ]);
    }
}
```

---

## 8. Frontend — JS Modules (Alpine.js)

### 8A. `useState.js`

**File:** `resources/js/features/backdoor/system-settings/user-management/useState.js`

```javascript
export default function useState(Alpine) {
  return Alpine.reactive({
    isDrawerOpen: false,
    isEdit:       false,
    isLoading:    false,
    userId:       null,

    form: {
      name:  '',
      email: '',
      phone: '',
      role:  '',
    },

    errors: {},
  });
}
```

---

### 8B. `useForm.js`

**File:** `resources/js/features/backdoor/system-settings/user-management/useForm.js`

```javascript
import { z } from 'zod';
import { Toast, Modal } from '@/lib/sweetalert';
import route from '@/lib/route';
import axios from '@/lib/axiosInstance';

const schema = z.object({
  name:  z.string().min(3, 'Nama minimal 3 karakter.').max(255),
  email: z.string().email('Format email tidak valid.').min(1, 'Email wajib diisi.'),
  phone: z.string()
           .regex(/^[0-9]+$/, 'Nomor HP hanya boleh berisi angka.')
           .min(8, 'Nomor HP minimal 8 digit.')
           .max(15, 'Nomor HP maksimal 15 digit.'),
  role:  z.string().min(1, 'Role wajib dipilih.'),
});

export default function useForm({ state, table }) {
  const resetForm = () => {
    state.isEdit  = false;
    state.userId  = null;
    state.errors  = {};
    state.form    = { name: '', email: '', phone: '', role: '' };
  };

  const openDrawer = () => {
    resetForm();
    state.isDrawerOpen = true;
  };

  const closeDrawer = () => {
    state.isDrawerOpen = false;
    resetForm();
  };

  /**
   * Hydrate drawer dengan data baris yang diklik (mode Edit).
   * @param {{ id: number, name: string, email: string, phone: string, role: string }} user
   */
  const editUser = (user) => {
    resetForm();
    state.isEdit        = true;
    state.userId        = user.id;
    state.form.name     = user.name;
    state.form.email    = user.email;
    state.form.phone    = user.phone === '-' ? '' : user.phone;
    state.form.role     = user.role;
    state.isDrawerOpen  = true;
  };

  const submitUser = async () => {
    state.errors  = {};
    state.isLoading = true;

    // Validasi Zod client-side
    const parsed = schema.safeParse(state.form);
    if (!parsed.success) {
      parsed.error.issues.forEach(issue => {
        const key = issue.path[0];
        if (!state.errors[key]) state.errors[key] = issue.message;
      });
      state.isLoading = false;
      return;
    }

    const url    = state.isEdit
      ? route('backdoor.system-settings.users.update', state.userId)
      : route('backdoor.system-settings.users.store');
    const method = state.isEdit ? 'put' : 'post';

    try {
      const res = await axios[method](url, state.form);
      closeDrawer();
      table.reload();
      Toast.fire({ icon: 'success', title: res.data.message });
    } catch (error) {
      if (error.response?.status === 422) {
        const errs = error.response.data.errors ?? {};
        Object.keys(errs).forEach(key => { state.errors[key] = errs[key][0]; });
      } else {
        Modal.fire({
          icon: 'error',
          title: 'Gagal menyimpan user',
          text:  error.response?.data?.message ?? 'Terjadi kesalahan server.',
        });
      }
    } finally {
      state.isLoading = false;
    }
  };

  return { openDrawer, closeDrawer, editUser, submitUser };
}
```

---

### 8C. `useActions.js`

**File:** `resources/js/features/backdoor/system-settings/user-management/useActions.js`

```javascript
import { confirmModal, Toast, Modal } from '@/lib/sweetalert';
import route from '@/lib/route';
import axios from '@/lib/axiosInstance';

export default function useActions({ table }) {
  /**
   * Reset password user ke Password123.
   */
  const resetPassword = async (id, name) => {
    const result = await confirmModal(
      `Reset Password "${name}"?`,
      'Password akan dikembalikan ke nilai default: Password123.',
      'warning',
      'Ya, Reset'
    );

    if (!result.isConfirmed) return;

    try {
      const res = await axios.patch(route('backdoor.system-settings.users.reset-password', id));
      Toast.fire({ icon: 'success', title: res.data.message });
    } catch (error) {
      Toast.fire({
        icon: 'error',
        title: error.response?.data?.message ?? 'Gagal mereset password.',
      });
    }
  };

  /**
   * Hard delete user dari database.
   */
  const destroyUser = async (id, name) => {
    const result = await confirmModal(
      `Hapus User "${name}"?`,
      'User yang dihapus tidak dapat dipulihkan.',
      'warning',
      'Ya, Hapus'
    );

    if (!result.isConfirmed) return;

    try {
      const res = await axios.delete(route('backdoor.system-settings.users.destroy', id));
      table.reload();
      Toast.fire({ icon: 'success', title: res.data.message });
    } catch (error) {
      const msg = error.response?.data?.message ?? 'Gagal menghapus user.';
      Modal.fire({ icon: 'error', title: 'Gagal Menghapus', text: msg });
    }
  };

  return { resetPassword, destroyUser };
}
```

---

### 8D. `Index.js`

**File:** `resources/js/features/backdoor/system-settings/user-management/Index.js`

Entry point utama yang mengkomposisi seluruh composable.

```javascript
import useDatatable from '@/lib/useDatatable';
import route from '@/lib/route';
import { Toast } from '@/lib/sweetalert';
import useState from './useState';
import useForm from './useForm';
import useActions from './useActions';

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
  } = useDatatable(Alpine, route('backdoor.system-settings.users.data'), {
    debounceMs: 400,
    onError: () => Toast.fire({ icon: 'error', title: 'Gagal memuat data user.' }),
  });

  Object.assign(table, { fetch, setSearch, nextPage, prevPage, goToPage, reload, getPages });

  // Helper: warna badge role
  const roleBadgeClass = (roleName) => {
    const map = {
      admin: 'bg-sky-100 text-sky-700 border border-sky-200',
      owner: 'bg-amber-100 text-amber-700 border border-amber-200',
    };
    return map[roleName] ?? 'bg-stone-100 text-stone-600 border border-stone-200';
  };

  const { openDrawer, closeDrawer, editUser, submitUser } = useForm({ state, table });
  const { resetPassword, destroyUser } = useActions({ table });

  return {
    state,
    table,
    roleBadgeClass,
    openDrawer,
    closeDrawer,
    editUser,
    submitUser,
    resetPassword,
    destroyUser,
    init() {
      fetch();
    },
  };
}
```

---

## 9. Frontend — Blade View

**File:** `resources/views/backdoor/system-settings/user/index.blade.php`

```blade
@php
  $breadcrumbs = [
    ['label' => 'Dashboard',         'url' => route('backdoor.dashboard')],
    ['label' => 'Pengaturan Sistem', 'url' => '#'],
    ['label' => 'Manajemen User',    'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Manajemen User"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/system-settings/user-management/Index"
>
  <x-slot:content>
    <div x-data="Index" x-cloak class="w-full space-y-6">

      <x-backdoor.shared.page-header title="Manajemen User" />

      {{-- Table Card --}}
      <div class="relative overflow-visible border border-stone-200 bg-stone-50 p-6">

        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari nama, email, nomor hp..." />
          </x-slot:left>
          <x-slot:right>
            <x-backdoor.table.add-button
              x-on:click="openDrawer()"
              text="Tambah User"
            />
          </x-slot:right>
        </x-backdoor.table.header>

        <x-backdoor.table.container headers="No,Nama,Email,Nomor HP,Role,Aksi">
          <template x-for="(user, index) in table.data" :key="user.id">
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

              {{-- Nama --}}
              <x-backdoor.table.cell
                class="font-semibold text-stone-900"
                x-text="user.name"
              />

              {{-- Email --}}
              <x-backdoor.table.cell
                class="text-sm text-stone-600"
                x-text="user.email"
              />

              {{-- Nomor HP --}}
              <x-backdoor.table.cell
                class="text-sm text-stone-600"
                x-text="user.phone"
              />

              {{-- Role Badge --}}
              <x-backdoor.table.cell>
                <span
                  class="inline-block px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wide"
                  :class="roleBadgeClass(user.role)"
                  x-text="user.role"
                ></span>
              </x-backdoor.table.cell>

              {{-- Aksi Dropdown --}}
              <x-backdoor.table.actions>
                {{-- Edit --}}
                <x-backdoor.table.action-item
                  x-on:click="editUser(user); closeDropdown()"
                  color="text-yellow-600"
                  text="Edit"
                />

                {{-- Reset Password --}}
                <x-backdoor.table.action-item
                  x-on:click="resetPassword(user.id, user.name); closeDropdown()"
                  color="text-sky-600"
                  text="Reset Password"
                />

                {{-- Hapus --}}
                <x-backdoor.table.action-item
                  x-on:click="destroyUser(user.id, user.name); closeDropdown()"
                  color="text-red-600"
                  text="Hapus"
                />
              </x-backdoor.table.actions>

            </tr>
          </template>
        </x-backdoor.table.container>

        <x-backdoor.table.pagination />

      </div>

      {{-- ================================================================ --}}
      {{-- DRAWER: Form Tambah / Edit User                                   --}}
      {{-- ================================================================ --}}
      <x-shared.drawer
        openState="state.isDrawerOpen"
        closeAction="closeDrawer()"
        title="Tambah / Edit User"
        ariaLabelledBy="user-drawer-title"
        formAction="submitUser()"
      >
        {{-- ---- Body Drawer ---- --}}
        <div class="space-y-5">

          {{-- Nama Lengkap --}}
          <div class="space-y-1">
            <label class="text-xs font-semibold uppercase tracking-wider text-stone-600">
              Nama Lengkap <span class="text-red-500">*</span>
            </label>
            <p class="text-xs text-red-500" x-show="state.errors.name" x-text="state.errors.name"></p>
            <input
              type="text"
              x-model="state.form.name"
              placeholder="Contoh: Budi Santoso"
              class="w-full border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800 outline-none placeholder:text-stone-400 focus:border-stone-500"
              :class="{ 'border-red-400': state.errors.name }"
            />
          </div>

          {{-- Email --}}
          <div class="space-y-1">
            <label class="text-xs font-semibold uppercase tracking-wider text-stone-600">
              Email <span class="text-red-500">*</span>
            </label>
            <p class="text-xs text-red-500" x-show="state.errors.email" x-text="state.errors.email"></p>
            <input
              type="email"
              x-model="state.form.email"
              placeholder="email@example.com"
              class="w-full border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800 outline-none placeholder:text-stone-400 focus:border-stone-500"
              :class="{ 'border-red-400': state.errors.email }"
            />
          </div>

          {{-- Nomor HP --}}
          <div class="space-y-1">
            <label class="text-xs font-semibold uppercase tracking-wider text-stone-600">
              Nomor HP <span class="text-red-500">*</span>
            </label>
            <p class="text-xs text-red-500" x-show="state.errors.phone" x-text="state.errors.phone"></p>
            <input
              type="tel"
              x-model="state.form.phone"
              placeholder="08123456789"
              class="w-full border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800 outline-none placeholder:text-stone-400 focus:border-stone-500"
              :class="{ 'border-red-400': state.errors.phone }"
            />
          </div>

          {{-- Role (Choices.js) --}}
          <div class="space-y-1">
            <label class="text-xs font-semibold uppercase tracking-wider text-stone-600">
              Role <span class="text-red-500">*</span>
            </label>
            <p class="text-xs text-red-500" x-show="state.errors.role" x-text="state.errors.role"></p>
            <select
              x-model="state.form.role"
              x-init="
                $nextTick(() => {
                  const choices = new Choices($el, {
                    searchEnabled: false,
                    itemSelectText: '',
                    shouldSort: false,
                  });

                  // Sync Choices.js saat state.form.role berubah (mode edit)
                  $watch('state.form.role', (val) => {
                    if (val) choices.setChoiceByValue(val);
                  });
                })
              "
              class="w-full border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800"
              :class="{ 'border-red-400': state.errors.role }"
            >
              <option value="">-- Pilih Role --</option>
              @foreach ($roles as $role)
                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
              @endforeach
            </select>
          </div>

          {{-- Password (Read-only, hanya tampil saat Create) --}}
          <template x-if="!state.isEdit">
            <div class="space-y-1">
              <label class="text-xs font-semibold uppercase tracking-wider text-stone-600">
                Password Default
              </label>
              <input
                type="text"
                value="Password123"
                readonly
                class="w-full cursor-not-allowed border border-stone-200 bg-stone-100 px-3 py-2 text-sm text-stone-500"
              />
              <p class="text-xs text-stone-500">
                <i class="ri-information-line mr-1"></i>
                Kata sandi default untuk akun baru. Pengguna dapat mengubahnya secara mandiri di halaman profil.
              </p>
            </div>
          </template>

        </div>
        {{-- ---- End Body Drawer ---- --}}

        {{-- ---- Footer Drawer ---- --}}
        <x-slot:footer>
          <button
            type="button"
            x-on:click="closeDrawer()"
            class="border border-stone-300 px-5 py-2 text-sm font-semibold text-stone-600 transition hover:bg-stone-200"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="state.isLoading"
            class="bg-stone-800 px-5 py-2 text-sm font-semibold text-white transition hover:bg-stone-900 disabled:cursor-not-allowed disabled:opacity-60"
          >
            <span x-show="!state.isLoading" x-text="state.isEdit ? 'Perbarui' : 'Simpan'"></span>
            <span x-show="state.isLoading" x-cloak>Menyimpan...</span>
          </button>
        </x-slot:footer>
        {{-- ---- End Footer Drawer ---- --}}

      </x-shared.drawer>

    </div>
  </x-slot:content>
</x-layouts.backdoor.index>
```

---

## 10. Catatan Modifikasi File Existing

### A. `routes/backdoor/system-settings/user-management.php`

File **sudah ada namun kosong**. Isi penuh dengan routes dari Bagian 3.

### B. Sidebar Link

Tambahkan di `resources/views/components/layouts/backdoor/components/sidebar-links.blade.php`:

```blade
{{-- user management start --}}
<x-layouts.backdoor.components.sidebar-link-item
  :href="route('backdoor.system-settings.users.index')"
  :active="request()->routeIs('backdoor.system-settings.users.*')"
  icon='ri-team-line'
  title='Manajemen User'
/>
{{-- user management end --}}
```

### C. Registrasi Alpine Component

Daftarkan di titik registrasi Alpine yang sudah ada di proyek:

```javascript
import Index from '@/features/backdoor/system-settings/user-management/Index';
Alpine.data('Index', Index);
```

---

## Catatan Implementasi

### A. `whereDoesntHave` vs `whereHas`

Query menggunakan `whereDoesntHave('roles', fn($q) => $q->whereIn('name', [...]))` untuk mengecualikan user yang memiliki role tertentu. Ini lebih efisien daripada dua `whereDoesntHave` terpisah karena hanya satu subquery.

### B. Unique Ignore pada Update

`UpdateUserRequest` menggunakan pola `"unique:users,email,{$userId}"` untuk mengizinkan user menyimpan email/phone milik dirinya sendiri tanpa konflik unique constraint.

### C. Choices.js Sync di Mode Edit

Saat `editUser()` dipanggil, `state.form.role` diisi dengan nilai baru. `$watch` pada element Choices.js memastikan UI dropdown tersinkronisasi karena Choices.js tidak reaktif secara default terhadap perubahan nilai `<select>` yang di-manage Alpine.

### D. Activity Log — Auto vs Explicit

| Event | Auto (model trait) | Explicit (controller) |
|---|---|---|
| Create | ✅ (`created`) | — |
| Update | ✅ (`updated`, dirty only) | — |
| Delete | ✅ (`deleted`) | ✅ tambahan context `deleted_name` |
| Reset Password | ✅ (`updated`, hash baru) | ✅ log `reset-password` yang bermakna |

### E. Self-Harm — `abort_if` pada destroy

`abort_if($user->id === auth()->id(), 403, ...)` menggunakan `id` (integer PK) bukan `uuid` untuk perbandingan langsung yang efisien tanpa query tambahan.

### F. `ilike` di Postgres

Search menggunakan `ilike` (case-insensitive Postgres native). Konsisten dengan pola di seluruh controller lain dalam proyek ini.

### G. Drawer Title Dinamis

Teks judul drawer (`Tambah User` / `Edit User`) bisa dibuat dinamis menggunakan Alpine expression di attribute `:title` pada komponen drawer, namun komponen `<x-shared.drawer>` menggunakan Blade props (server-side). Alternatif: gunakan `<h2>` di dalam body drawer yang dikontrol Alpine, atau gunakan string statis `"Tambah / Edit User"` sebagai judul generik (pendekatan yang digunakan di spec ini — lebih simpel).
