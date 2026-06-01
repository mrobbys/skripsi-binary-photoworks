# Documentation: System Settings - User Management Functional Specification

## 1. Komponen Antarmuka Pengelolaan Tim (UI Layout Mapping)

Modul ini bertindak sebagai pusat kendali akun internal kru studio (Superadmin, Admin, Fotografer) yang dikawal oleh komponen induk `<x-layouts.backdoor>`.

### A. Halaman Utama Tabel Pengguna (`views/backdoor/system-settings/user/index.blade.php`)

- **Bilah Kendali Atas (`Screen Shot 2026-05-31 at 11.04.47.png`)**:
    - **Omni Search Box**: Kotak input pencarian dengan placeholder spesifik `Cari nama, email, nomor hp...` untuk menyaring data tim secara langsung.
    - **Tombol Pemicu**: Tombol `+ Tambah User` di sisi kanan untuk mengaktifkan jendela modal form.
- **Struktur Tabel Akun Internal (`Screen Shot 2026-05-31 at 11.04.47.png`)**:
  Menampilkan matriks data tim aktif dengan susunan kolom:
    1. **No.**: Penomoran indeks baris berjalan.
    2. **Nama**: String nama lengkap personel tim (Contoh: `Robby Setiawan`).
    3. **Email**: Alamat email resmi untuk otentikasi masuk sistem.
    4. **Nomor HP**: String kontak seluler personil untuk keperluan koordinasi.
    5. **Role**: Badge visual solid penanda kasta hak akses Spatie (Contoh: `SUPERADMIN`).
    6. **Aksi**: Tombol tiga titik vertikal berisi menu _dropdown_ untuk memicu daftar kendali aksi khusus.

### B. Daftar Item Menu Aksi (Dropdown Actions)

Tombol aksi tiga titik wajib memuat 4 opsi interaksi operasional berikut:

1. **Detail**: Menampilkan ringkasan data profil lengkap user di dalam modal pop-up peninjau.
2. **Edit**: Membuka modal form dengan status data terisi (_hydrated data_) untuk mengubah informasi user dan role Spatie RBAC.
3. **Reset Password**: Memicu fungsi AJAX Axios instan untuk memaksa kata sandi pengguna kembali ke setelan standar pabrik.
4. **Delete**: Menghapus baris data pengguna dari sistem jika terjadi salah input data personel.

### C. Komponen Jendela Modal Form (`partials/modal-user-form.blade.php`)

Jendela pop-up ringan yang dikendalikan oleh Alpine.js dengan elemen input berupa:

- **Input Text (`name`)**: Nama lengkap personil.
- **Input Email (`email`)**: Email unik (Wajib divalidasi agar tidak kembar di database Supabase).
- **Input Phone (`phone`)**: Nomor WhatsApp aktif tim.
- **Select Dropdown (`role`)**: Pilihan peran internal yang bersumber dari tabel `roles` Spatie RBAC.
- **Informasi Papan Sandi (Read-Only/Notice)**: Menampilkan teks informatif: _"Kata sandi default untuk pengguna baru dikunci otomatis pada nilai: Password123. Pengguna dapat mengubahnya secara mandiri pada dasbor profil mereka."_

---

## 2. Aturan Bisnis & Integrasi Sistem Backend (Laravel 13 Core)

### A. Proteksi Keamanan Tingkat Pengontrol (Controller Level)

Mematuhi standarisasi **Laravel 13 Attribute Refactor**, proteksi rute diisolasi penuh di tingkat atas deklarasi kelas menggunakan atribut PHP 8:

```php
namespace App\Http\Controllers\Backdoor;

use Illuminate\Routing\Controllers\Controller;
use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('auth')]
#[Middleware('role:superadmin|owner')] // Menutup gerbang akses dari kru biasa
class UserManagementController extends Controller
{
    // Logika pengkodean CRUD murni tetap suci dan terjaga
}
```

### B. Aturan Pengisian Data & Enkripsi Kata Sandi

Saat Admin menekan tombol simpan, backend Laravel secara otomatis menyuntikkan string kata sandi bawaan melalui algoritma hashing Bcrypt sebelum masuk ke dalam tabel `users`:

```php
$validated = $request->validated();

// Penyuntikan kata sandi default otomatis
$validated['password'] = Hash::make('Password123');

$user = User::create($validated);
$user->assignRole($request->input('role')); // Sinkronisasi otomatis Spatie RBAC
```

---

## 3. Batasan Pembatasan Sisi Depan (Front-End Constraints - Feature-Based Modules)

- **Hukum Anti-Inline HTML Attribute Script**: Proses pembukaan modal, pengisian data objek, dan interaksi penembakan AJAX dilarang keras ditulis di dalam atribut tag HTML (Steril total dari instruksi mentah `onclick="..."`).
- **Mekanisme Pemuatan Bersisian (Co-location Style)**:
    - Struktur HTML modal diletakkan bersisian di folder komponen user.
    - Seluruh status penanganan modal (`isOpen: false`), pembersihan input form, dan manipulasi data Axios diisolasi di file `user-script.blade.php`.
    - Integrasi disatukan menggunakan direktif `@include` lokal pada bagian terbawah file induk:

```html
<x-layouts.backdoor>
    <div x-data="userManagementHandler">@include('backdoor.system-settings.user.partials.modal-user-form')</div>

    @include('backdoor.system-settings.user.user-script')
</x-layouts.backdoor>
```

- **Optimasi Beban Sinyal (Debounce)**: Sektor input pencarian `Cari nama, email, nomor hp...` wajib dibekali pengaman `.debounce.400ms` pada ikatan variabel Alpine.js agar tidak membebani kueri performa database PostgreSQL.
