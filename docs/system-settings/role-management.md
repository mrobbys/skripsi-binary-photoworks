# Documentation: System Settings - Role Management Functional Specification

## 1. Komponen Antarmuka Manajemen Role (UI Layout Mapping)

Modul konfigurasi hak akses internal tim ini berada di bawah kendali pelindung `<x-layouts.backdoor>` dan hanya diizinkan untuk dioperasikan oleh level otorisasi tertinggi (Superadmin/Owner).

### A. Halaman Utama Indeks (`views/backdoor/system-settings/role/index.blade.php`)

- **Bilah Kontrol Atas**:
    - **Live Search Box**: Kotak input pencarian untuk menyaring nama peran secara asinkronus menggunakan penahan laju (Debounce).
    - **Tombol Aksi**: Tombol `+ Tambah Role` yang bertindak sebagai jangkar pengalihan halaman (_hyperlink redirect_) langsung menuju rute pembuatan khusus `/admin/system-settings/roles/create`.
- **Struktur Tabel Spatie Role**:
  Menampilkan grid tabel dinamis untuk memantau peran internal aktif:
    1. **No.**: Penomoran indeks baris berjalan.
    2. **Nama Role**: String identitas peran (Contoh: `Admin`, `Superadmin`, `Owner`).
    3. **Aksi**: Tombol tiga titik vertikal yang memicu kemunculan menu _dropdown_ untuk mengarahkan Admin ke halaman edit khusus (`/admin/system-settings/roles/{id}/edit`) atau memicu penghapusan data.

### B. Halaman Formulir Khusus Peran (`create.blade.php` & `edit.blade.php`)

Halaman ini memanfaatkan kapasitas lebar layar monitor penuh untuk menyajikan formulir berskala besar:

- **Sektor Sisi Kiri (Spesifikasi Peran)**:
    - Input teks tunggal untuk `nama_role` (Contoh: `Kasir Senior`).

- **Sektor Sisi Kanan / Bawah (Matriks Hak Akses Khusus)**:
    - Menyajikan papan panel raksasa yang membagi kumpulan _checkbox_ izin (`permissions[]`) ke dalam beberapa kartu sub-modul (_Domain Grouping Card_) untuk mempermudah pemindaian mata kasir:
        1. **Kluster Data Master**: Checkbox untuk izin `Akses Kategori`, `Akses Paket`, `Akses Background`, dan `Akses Addon`.
        2. **Kluster Operasional**: Checkbox untuk izin `Lihat Kalender`, `Lihat Jadwal`, dan `Update GDrive Link`.
        3. **Kluster Transaksi & Keuangan**: Checkbox untuk izin `Eksekusi Pelunasan` dan `Cetak Laporan PDF`.

---

## 2. Aturan Bisnis & Integrasi Spatie RBAC Backend (Laravel 13 Core)

### A. Refaktor Atribut Pengontrol (Controller Level)

Mengikuti pedoman **Laravel 13 Attribute Refactor**, rute dan hak keamanan pada pengontrol ini dideklarasikan secara modern menggunakan atribut PHP 8 tepat di atas nama kelas:

```php
namespace App\Http\Controllers\Backdoor;

use Illuminate\Routing\Controllers\Controller;
use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('auth')]
#[Middleware('role:superadmin|owner')]
class RoleManagementController extends Controller
{
    // Seluruh logika inti CRUD Spatie RBAC aman dan terjaga
}
```

### B. Kalkulasi Agregat Izin

Untuk menampilkan ringkasan data, sistem melakukan kalkulasi relasi matematis langsung terhadap tabel pivot bawaan Spatie:

$$
\text{Total Izin Terikat} = \sum (\text{role\_has\_permissions}) \quad \text{where } \text{role\_id} = \text{roles.id}
$$

---

## 3. Batasan Pembatasan Sisi Depan (Front-End Constraints - SFC Style)

- **Hukum Anti-Inline HTML Attribute Script**: Seluruh interaksi penandaan massal kotak centang (_Select All Checkboxes_), ketikan pencarian, maupun pengiriman formulir haram menyentuh atribut mentah tag HTML (Bebas 100% dari instruksi `onclick="..."` atau `onsubmit="..."`).
- **Mekanisme Pemuatan Bersisian (Co-location Style)**:
    - Tata letak elemen input diletakkan pada berkas views form masing-masing.
    - Logika penanganan array _checkbox_ terisolasi secara mandiri di dalam berkas pendukung bersisian `form-script.blade.php`.
    - Proses penguncian menggunakan perintah direktif `@include` lokal di baris terbawah:

```html
<x-layouts.backdoor>
    <div x-data="roleFormHandler"></div>

    @include('backdoor.system-settings.role.form-script')
</x-layouts.backdoor>
```
