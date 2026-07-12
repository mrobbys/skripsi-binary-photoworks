# Spec: Fitur Profil Saya (Update Data Diri + Ganti Password)

**Tanggal:** 2026-07-12
**Branch:** `feat/frontdoor-profile-details`
**Scope:** Frontdoor — Dashboard User

---

## 1. Ringkasan Fitur

Halaman ini memiliki dua fungsi utama:

1. **Update Data Diri** — User dapat memperbarui nama, email, dan nomor telepon melalui form inline di halaman profil.
2. **Ganti Password** — User dapat mengganti password via Drawer yang muncul dari kanan layar. Membutuhkan verifikasi password lama sebelum bisa menetapkan password baru.

---

## 2. File yang Akan Dibuat & Diubah

### Backend (PHP/Laravel)

| Aksi | File |
|---|---|
| **Buat** | `app/Domains/User/Http/Controllers/Frontdoor/ProfileController.php` |
| **Buat** | `app/Domains/User/Http/Requests/UpdateProfileRequest.php` |
| **Buat** | `app/Domains/User/Http/Requests/ChangePasswordRequest.php` |
| **Buat** | `app/Domains/User/DTOs/ProfileData.php` |
| **Buat** | `app/Domains/User/DTOs/ChangePasswordData.php` |
| **Buat** | `app/Domains/User/Services/ProfileService.php` |
| **Update** | `app/Domains/User/Repositories/UserRepository.php` |
| **Update** | `routes/frontdoor/dashboard.php` |

### Frontend (Blade + JS)

| Aksi | File |
|---|---|
| **Update** | `resources/views/frontdoor/dashboard/profil.blade.php` |
| **Buat** | `resources/views/components/frontdoor/dashboard/profil/change-password-drawer.blade.php` |
| **Buat** | `resources/js/features/frontdoor/dashboard/Profil.js` |
| **Buat** | `resources/js/features/frontdoor/dashboard/useUpdateProfile.js` |
| **Buat** | `resources/js/features/frontdoor/dashboard/useChangePassword.js` |

---

## 3. Backend — Kode Lengkap

### 3.1 Routes — `routes/frontdoor/dashboard.php`

Tambahkan dua route baru di bawah route profil yang sudah ada.
Jangan lupa tambahkan `use App\Domains\User\Http\Controllers\Frontdoor\ProfileController;` di atas file.

```php
// update data diri
Route::patch('/dashboard/profil', [ProfileController::class, 'update'])
  ->name('frontdoor.dashboard.profil.update');

// ganti password
Route::post('/dashboard/profil/password', [ProfileController::class, 'changePassword'])
  ->name('frontdoor.dashboard.profil.password');
```

---

### 3.2 DTO — `ProfileData.php`

```php
<?php

namespace App\Domains\User\DTOs;

use Spatie\LaravelData\Data;

class ProfileData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
    ) {}
}
```

---

### 3.3 DTO — `ChangePasswordData.php`

```php
<?php

namespace App\Domains\User\DTOs;

use Spatie\LaravelData\Data;

class ChangePasswordData extends Data
{
    public function __construct(
        public readonly string $old_password,
        public readonly string $password,
    ) {}
}
```

---

### 3.4 Form Request — `UpdateProfileRequest.php`

```php
<?php

namespace App\Domains\User\Http\Requests;

use App\Domains\User\DTOs\ProfileData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'min:3', 'max:100'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                // Ignore unique check untuk email milik user sendiri
                'unique:users,email,' . Auth::id(),
            ],
            'phone' => ['required', 'numeric', 'digits_between:10,14'],
        ];
    }

    public function toDto(): ProfileData
    {
        $validated = $this->validated();

        return new ProfileData(
            name:  trim($validated['name']),
            email: trim($validated['email']),
            phone: $validated['phone'],
        );
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap harus diisi.',
            'name.min'      => 'Nama lengkap minimal 3 karakter.',
            'name.max'      => 'Nama lengkap maksimal 100 karakter.',

            'email.required' => 'Email harus diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Email ini sudah digunakan oleh akun lain.',
            'email.max'      => 'Email maksimal 255 karakter.',

            'phone.required'       => 'No telepon harus diisi.',
            'phone.numeric'        => 'No telepon harus berupa angka.',
            'phone.digits_between' => 'No telepon harus terdiri dari 10-14 digit.',
        ];
    }
}
```

---

### 3.5 Form Request — `ChangePasswordRequest.php`

```php
<?php

namespace App\Domains\User\Http\Requests;

use App\Domains\User\DTOs\ChangePasswordData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'old_password' => ['required', 'string'],
            'password'     => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->max(255)->mixedCase()->numbers(),
            ],
        ];
    }

    public function toDto(): ChangePasswordData
    {
        $validated = $this->validated();

        return new ChangePasswordData(
            old_password: $validated['old_password'],
            password:     $validated['password'],
        );
    }

    public function messages(): array
    {
        return [
            'old_password.required' => 'Password lama harus diisi.',

            'password.required'  => 'Password baru harus diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.max'       => 'Password maksimal 255 karakter.',
            'password.mixed'     => 'Password harus mengandung huruf besar dan kecil.',
            'password.numbers'   => 'Password harus mengandung angka.',
        ];
    }
}
```

---

### 3.6 UserRepository — Tambah 2 Method Baru

```php
/**
 * Cari user berdasarkan ID.
 */
public function findById(int $userId): ?User
{
    return User::find($userId);
}

/**
 * Update data user berdasarkan ID.
 *
 * @param int   $userId
 * @param array $data
 */
public function updateById(int $userId, array $data): void
{
    User::where('id', $userId)->update($data);
}
```

---

### 3.7 Service — `ProfileService.php`

```php
<?php

namespace App\Domains\User\Services;

use App\Domains\User\DTOs\ChangePasswordData;
use App\Domains\User\DTOs\ProfileData;
use App\Domains\User\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class ProfileService
{
    public function __construct(
        private readonly UserRepository $repository,
    ) {}

    /**
     * Update data diri user.
     */
    public function updateProfile(int $userId, ProfileData $data): void
    {
        $this->repository->updateById($userId, [
            'name'  => $data->name,
            'email' => $data->email,
            'phone' => $data->phone,
        ]);
    }

    /**
     * Ganti password user.
     * Verifikasi password lama sebelum menyimpan yang baru.
     */
    public function changePassword(int $userId, ChangePasswordData $data): void
    {
        $user = $this->repository->findById($userId);

        if (! Hash::check($data->old_password, $user->password)) {
            throw new RuntimeException('Password lama tidak sesuai.');
        }

        $this->repository->updateById($userId, [
            'password' => Hash::make($data->password),
        ]);
    }
}
```

---

### 3.8 Controller — `ProfileController.php`

```php
<?php

namespace App\Domains\User\Http\Controllers\Frontdoor;

use App\Domains\User\Http\Requests\ChangePasswordRequest;
use App\Domains\User\Http\Requests\UpdateProfileRequest;
use App\Domains\User\Services\ProfileService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService,
    ) {}

    /**
     * Update data diri user yang sedang login.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $this->profileService->updateProfile(
                userId: Auth::id(),
                data:   $request->toDto(),
            );

            return response()->json([
                'success' => true,
                'message' => 'Data diri berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan. Silahkan coba lagi.',
            ], 500);
        }
    }

    /**
     * Ganti password user yang sedang login.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $this->profileService->changePassword(
                userId: Auth::id(),
                data:   $request->toDto(),
            );

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan. Silahkan coba lagi.',
            ], 500);
        }
    }
}
```

---

## 4. Frontend — Kode Lengkap

### 4.1 `useUpdateProfile.js`

```javascript
/**
 * Composable hook untuk form update data diri.
 *
 * File: resources/js/features/frontdoor/dashboard/useUpdateProfile.js
 */
import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";

export default function useUpdateProfile({ state }) {
  const submitUpdateProfile = async () => {
    state.isUpdatingProfile = true;
    state.profileErrors = {};

    try {
      const res = await window.axios.patch(
        route("frontdoor.dashboard.profil.update"),
        {
          name:  state.name,
          email: state.email,
          phone: state.phone,
        }
      );

      if (!res.data.success) {
        throw new Error(res.data.message || "Gagal memperbarui data.");
      }

      Toast.fire({ icon: "success", title: res.data.message });
    } catch (err) {
      if (err.response?.status === 422) {
        state.profileErrors = err.response.data.errors;
        return;
      }
      const msg = err?.response?.data?.message || err.message;
      Toast.fire({ icon: "error", title: msg || "Terjadi kesalahan." });
    } finally {
      state.isUpdatingProfile = false;
    }
  };

  return { submitUpdateProfile };
}
```

---

### 4.2 `useChangePassword.js`

```javascript
/**
 * Composable hook untuk form ganti password via drawer.
 *
 * File: resources/js/features/frontdoor/dashboard/useChangePassword.js
 */
import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";

export default function useChangePassword({ state }) {
  const openPasswordDrawer = () => {
    state.passwordForm   = { old_password: "", password: "", password_confirmation: "" };
    state.passwordErrors = {};
    state.isPasswordDrawerOpen = true;
  };

  const closePasswordDrawer = () => {
    state.isPasswordDrawerOpen = false;
    setTimeout(() => {
      state.passwordForm   = { old_password: "", password: "", password_confirmation: "" };
      state.passwordErrors = {};
    }, 500);
  };

  const submitChangePassword = async () => {
    state.isChangingPassword = true;
    state.passwordErrors     = {};

    try {
      const res = await window.axios.post(
        route("frontdoor.dashboard.profil.password"),
        {
          old_password:          state.passwordForm.old_password,
          password:              state.passwordForm.password,
          password_confirmation: state.passwordForm.password_confirmation,
        }
      );

      if (!res.data.success) {
        throw new Error(res.data.message || "Gagal mengganti password.");
      }

      Toast.fire({ icon: "success", title: res.data.message });
      closePasswordDrawer();
    } catch (err) {
      if (err.response?.status === 422) {
        state.passwordErrors = err.response.data.errors;
        return;
      }
      const msg = err?.response?.data?.message || err.message;
      Toast.fire({ icon: "error", title: msg || "Terjadi kesalahan." });
    } finally {
      state.isChangingPassword = false;
    }
  };

  return { openPasswordDrawer, closePasswordDrawer, submitChangePassword };
}
```

---

### 4.3 `Profil.js` (Orchestrator)

```javascript
/**
 * Komponen halaman Profil Saya.
 *
 * File: resources/js/features/frontdoor/dashboard/Profil.js
 * Penggunaan di Blade: <div x-data="Profil">...</div>
 *
 * @param {import('alpinejs').Alpine} Alpine
 */
import useUpdateProfile  from "./useUpdateProfile.js";
import useChangePassword from "./useChangePassword.js";

export default function Profil(Alpine) {
  // ---------------------------------------------------------------------------
  // State
  // ---------------------------------------------------------------------------
  const state = Alpine.reactive({
    // data diri (diisi dari Blade via x-init)
    name:  "",
    email: "",
    phone: "",

    // update profil
    isUpdatingProfile: false,
    profileErrors:     {},

    // drawer ganti password
    isPasswordDrawerOpen: false,
    isChangingPassword:   false,
    passwordErrors:       {},
    passwordForm: {
      old_password:          "",
      password:              "",
      password_confirmation: "",
    },
  });

  // ---------------------------------------------------------------------------
  // Composable hooks
  // ---------------------------------------------------------------------------
  const { submitUpdateProfile } = useUpdateProfile({ state });
  const { openPasswordDrawer, closePasswordDrawer, submitChangePassword } =
    useChangePassword({ state });

  // ---------------------------------------------------------------------------
  // Init — isi state dari data yang dikirim Blade
  // ---------------------------------------------------------------------------
  const init = (userData) => {
    state.name  = userData.name;
    state.email = userData.email;
    state.phone = userData.phone ?? "";
  };

  // ---------------------------------------------------------------------------
  // Return
  // ---------------------------------------------------------------------------
  return {
    state,
    init,
    submitUpdateProfile,
    openPasswordDrawer,
    closePasswordDrawer,
    submitChangePassword,
  };
}
```

---

### 4.4 `change-password-drawer.blade.php`

```blade
{{--
  COMPONENT: CHANGE PASSWORD DRAWER
  Terhubung ke Alpine Profil.js via useChangePassword.js.
--}}

<x-shared.drawer
  openState="state.isPasswordDrawerOpen"
  closeAction="closePasswordDrawer()"
  title="Ganti Password"
  maxWidth="max-w-md"
  ariaLabelledBy="change-password-drawer-title">

  <div class="space-y-6">

    {{-- password lama --}}
    <div>
      <label for="old_password" class="block text-sm font-medium text-stone-700 uppercase mb-2">
        Password Lama <span class="text-red-500">*</span>
      </label>
      <input
        type="password"
        id="old_password"
        x-model="state.passwordForm.old_password"
        autocomplete="current-password"
        placeholder="Masukkan password lama"
        class="w-full border border-stone-200 px-4 py-2.5 focus:outline-none focus:border-stone-700 focus:ring-1 focus:ring-stone-700 transition-colors">
      <template x-if="state.passwordErrors.old_password">
        <p class="mt-1.5 text-sm text-red-600" x-text="state.passwordErrors.old_password[0]"></p>
      </template>
    </div>

    <div class="h-px bg-stone-200"></div>

    {{-- password baru --}}
    <div>
      <label for="password" class="block text-sm font-medium text-stone-700 uppercase mb-2">
        Password Baru <span class="text-red-500">*</span>
      </label>
      <input
        type="password"
        id="password"
        x-model="state.passwordForm.password"
        autocomplete="new-password"
        placeholder="Min. 8 karakter, huruf besar & angka"
        class="w-full border border-stone-200 px-4 py-2.5 focus:outline-none focus:border-stone-700 focus:ring-1 focus:ring-stone-700 transition-colors">
      <template x-if="state.passwordErrors.password">
        <p class="mt-1.5 text-sm text-red-600" x-text="state.passwordErrors.password[0]"></p>
      </template>
    </div>

    {{-- konfirmasi password baru --}}
    <div>
      <label for="password_confirmation" class="block text-sm font-medium text-stone-700 uppercase mb-2">
        Konfirmasi Password Baru <span class="text-red-500">*</span>
      </label>
      <input
        type="password"
        id="password_confirmation"
        x-model="state.passwordForm.password_confirmation"
        autocomplete="new-password"
        placeholder="Ulangi password baru"
        class="w-full border border-stone-200 px-4 py-2.5 focus:outline-none focus:border-stone-700 focus:ring-1 focus:ring-stone-700 transition-colors">
    </div>

    {{-- info aturan --}}
    <p class="text-xs text-stone-400">
      <i class="ri-information-line"></i>
      Password minimal 8 karakter, mengandung huruf besar, huruf kecil, dan angka.
    </p>

  </div>

  <x-slot:footer>
    <x-shared.button
      type="button"
      x-on:click="closePasswordDrawer()"
      x-bind:disabled="state.isChangingPassword"
      class="text-stone-600 hover:text-stone-900 transition font-semibold text-sm cursor-pointer disabled:opacity-50"
      value="Batal" />
    <x-shared.button
      type="button"
      x-on:click="submitChangePassword()"
      x-bind:disabled="state.isChangingPassword"
      class="bg-stone-700 text-stone-50 px-4 py-2 border border-stone-700 hover:bg-stone-800 font-semibold text-sm tracking-wide disabled:opacity-50 disabled:pointer-events-none">
      <span x-text="state.isChangingPassword ? 'Menyimpan...' : 'Simpan Password'"></span>
    </x-shared.button>
  </x-slot:footer>

</x-shared.drawer>
```

---

### 4.5 `profil.blade.php` (Versi Lengkap)

```blade
<x-layouts.frontdoor
  title="Profil Saya - Dashboard"
  js-module="frontdoor/dashboard/Profil">

  <x-slot:content>
    <div
      class="w-full min-h-dvh py-12"
      x-data="Profil"
      x-init="init({{ Js::from(['name' => Auth::user()->name, 'email' => Auth::user()->email, 'phone' => Auth::user()->phone]) }})"
      x-cloak>

      <div class="mx-auto flex flex-col md:flex-row gap-8">

        <x-frontdoor.dashboard.sidebar />

        <div class="w-full">
          <section class="border border-stone-300 p-6 sm:p-8 space-y-6">

            {{-- header --}}
            <div class="flex items-center justify-between">
              <h1 class="text-xl font-bold text-stone-900">Profil Saya</h1>
              <x-shared.button
                type="button"
                x-on:click="openPasswordDrawer()"
                class="w-auto px-4 py-1.5 text-xs font-bold bg-transparent text-stone-900 border-[1.5px] border-stone-900 hover:bg-stone-900 hover:text-stone-50 transition-colors">
                <i class="ri-lock-password-line mr-1.5"></i>
                Ganti Password
              </x-shared.button>
            </div>

            <div class="h-px bg-stone-200"></div>

            {{-- form data diri --}}
            <form class="space-y-5 max-w-lg" x-on:submit.prevent="submitUpdateProfile()">

              {{-- nama --}}
              <div>
                <label for="name" class="block text-sm font-medium text-stone-700 uppercase mb-2">
                  Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" x-model="state.name" autocomplete="name"
                  class="w-full border border-stone-200 px-4 py-2.5 focus:outline-none focus:border-stone-700 focus:ring-1 focus:ring-stone-700 transition-colors">
                <template x-if="state.profileErrors.name">
                  <p class="mt-1.5 text-sm text-red-600" x-text="state.profileErrors.name[0]"></p>
                </template>
              </div>

              {{-- email --}}
              <div>
                <label for="email" class="block text-sm font-medium text-stone-700 uppercase mb-2">
                  Email <span class="text-red-500">*</span>
                </label>
                <input type="email" id="email" x-model="state.email" autocomplete="email"
                  class="w-full border border-stone-200 px-4 py-2.5 focus:outline-none focus:border-stone-700 focus:ring-1 focus:ring-stone-700 transition-colors">
                <template x-if="state.profileErrors.email">
                  <p class="mt-1.5 text-sm text-red-600" x-text="state.profileErrors.email[0]"></p>
                </template>
              </div>

              {{-- no telepon --}}
              <div>
                <label for="phone" class="block text-sm font-medium text-stone-700 uppercase mb-2">
                  No Telepon (WhatsApp) <span class="text-red-500">*</span>
                </label>
                <input type="text" id="phone" x-model="state.phone" inputmode="numeric"
                  autocomplete="tel" placeholder="Contoh: 08123456789"
                  class="w-full border border-stone-200 px-4 py-2.5 focus:outline-none focus:border-stone-700 focus:ring-1 focus:ring-stone-700 transition-colors">
                <template x-if="state.profileErrors.phone">
                  <p class="mt-1.5 text-sm text-red-600" x-text="state.profileErrors.phone[0]"></p>
                </template>
              </div>

              {{-- tombol simpan --}}
              <div class="pt-2">
                <x-shared.button
                  type="submit"
                  x-bind:disabled="state.isUpdatingProfile"
                  class="w-full bg-stone-800 text-stone-50 py-3 hover:bg-stone-900 transition-colors disabled:opacity-50 disabled:pointer-events-none">
                  <span x-text="state.isUpdatingProfile ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                </x-shared.button>
              </div>

            </form>

          </section>
        </div>

      </div>

      {{-- drawer ganti password --}}
      <x-frontdoor.dashboard.profil.change-password-drawer />

    </div>
  </x-slot:content>

</x-layouts.frontdoor>
```

---

## 5. Alur Logika

### Update Data Diri
1. User ubah input lalu klik **Simpan Perubahan** → `submitUpdateProfile()` dipanggil.
2. Tombol menjadi *disabled* (`state.isUpdatingProfile = true`).
3. Axios `PATCH /dashboard/profil` → `UpdateProfileRequest` memvalidasi.
4. **Gagal 422** → `state.profileErrors` diisi → pesan merah muncul di bawah field yang salah.
5. **Berhasil** → `Toast.fire({ icon: 'success' })` muncul di pojok kanan atas.

### Ganti Password
1. User klik **Ganti Password** → `openPasswordDrawer()` → drawer muncul dari kanan.
2. User isi ketiga field → klik **Simpan Password** → `submitChangePassword()` dipanggil.
3. Axios `POST /dashboard/profil/password`.
4. **Password lama salah (400)** → `Toast.fire({ icon: 'error', title: 'Password lama tidak sesuai.' })`.
5. **Gagal 422** → `state.passwordErrors` diisi → pesan merah per field.
6. **Berhasil** → `Toast.fire({ icon: 'success' })` → `closePasswordDrawer()`.

---

## 6. Poin Keamanan

- Rule `unique:users,email,{id}` memastikan user bisa simpan emailnya sendiri tanpa error *unique*, tapi tetap terblokir jika email sudah dipakai akun lain.
- `Hash::check()` dijalankan di sisi server — frontend tidak pernah bisa melewati verifikasi password lama.
- Semua endpoint dilindungi middleware `auth` dari grup route frontdoor.
- `Auth::id()` diambil dari sisi server, tidak dari request body frontend.
