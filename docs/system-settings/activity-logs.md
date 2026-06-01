# Documentation: System Settings - Activity Logs Functional Specification (Read-Only Audit Trail)

## 1. Komponen Antarmuka Radar Audit (UI Layout Mapping)

Modul ini bersifat murni **Read-Only** (Hanya Baca) untuk memantau integritas operasi tim internal, dibungkus penuh oleh komponen induk `<x-layouts.backdoor>`.

### A. Halaman Utama Tabel Log (`views/backdoor/system-settings/activity-log/index.blade.php`)

- **Bilah Penyaring Atas (Advanced Filter Controls)**:
    - **Live Search Box**: Menyaring log berdasarkan deskripsi aktivitas secara asinkronus dengan penahan laju `.debounce.400ms`.
    - **Dropdown Filter Peran/User**: Menu pilihan untuk mengerucutkan tampilan log murni pada satu personil tim tertentu saja.
- **Struktur Tabel Audit Trail Spatie**:
  Menampilkan catatan kronologis log aktivitas dari yang paling baru dengan susunan kolom:
    1. **No.**: Penomoran indeks baris berjalan.
    2. **Waktu Sesi**: Tanggal dan jam presisi eksekusi aksi (`created_at` dari Spatie ActivityLog).
    3. **Eksekutator**: Nama personel tim yang melakukan tindakan (Mengambil data relasi `causer_id` ke tabel `users`).
    4. **Modul Domain**: Nama tabel atau entitas objek yang dimanipulasi (`subject_type` seperti Booking, Package, atau Payment).
    5. **Aktivitas**: Deskripsi string logis dari aksi yang terjadi (Contoh: `created`, `updated`, `deleted`).
    6. **Detail Perubahan**: Tombol ikon "Mata" untuk memicu jendela modal peninjau parameter data lama vs data baru.

---

## 2. Aturan Bisnis & Mekanisme Pertahanan Basis Data (Laravel 13 Core)

### A. Penguncian Hak Akses Berbasis Atribut (Controller Level)

Mengikuti standarisasi mutlak **Laravel 13 Attribute Refactor**, rute log audit ini dikunci mati di tingkat atas kelas sehingga kru biasa (seperti fotografer) tidak dapat mengintip sirkuit ini:

```php
namespace App\Http\Controllers\Backdoor;

use Illuminate\Routing\Controllers\Controller;
use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('auth')]
#[Middleware('role:superadmin|owner')] // Hanya kasta tertinggi yang memegang kendali radar audit
class ActivityLogController extends Controller
{
    // Pengontrol ini murni hanya memiliki method index() dan show() untuk modal detail
}
```

### B. Formula Rasio Beban Log harian (Analytics Metric)

Untuk keperluan statistik dasbor analitik utama, sistem menghitung volume log masuk menggunakan agregasi penanggalan Carbon:

$$
\text{Volume Aktivitas Hari Ini} = \sum (\text{activity\_log}) \quad \text{where } \text{created\_at} = \text{Carbon::today()}
$$

---

## 3. Batasan Pembatasan Sisi Depan (Front-End Constraints - Feature-Based Modules)

- **Hukum Anti-Inline HTML Attribute Script**: Proses pembukaan modal detail log data lama/baru haram ditulis langsung di dalam atribut elemen HTML (Bebas total dari kode mentah `onclick="..."`).
- **Mekanisme Pemuatan Bersisian (Co-location Style)**:
    - Struktur HTML modal peninjau detail log diletakkan bersisian di folder komponen activity log.
    - Seluruh status penanganan data asinkronus Axios diisolasi di file `log-script.blade.php`.
    - Penyatuan komponen diikat menggunakan direktif `@include` lokal di baris terbawah file induk:

```html
<x-layouts.backdoor>
    <div x-data="activityLogHandler">@include('backdoor.system-settings.activity-log.partials.modal-detail-log')</div>

    @include('backdoor.system-settings.activity-log.log-script')
</x-layouts.backdoor>
```
