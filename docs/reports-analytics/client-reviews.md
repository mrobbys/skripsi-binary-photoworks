# Documentation: System Settings - Client Reviews Functional Specification (Global Testimonials Moderation)

## 1. Komponen Antarmuka Moderasi Ulasan (UI Layout Mapping)

Modul manajemen testimoni ini dibungkus penuh oleh komponen induk `<x-layouts.backdoor>` dan dapat diakses oleh Admin, Owner, maupun Superadmin untuk menjaga transparansi kualitas layanan.

### A. Halaman Utama DataTables Ulasan (`views/backdoor/client-reviews/index.blade.php`)

- **Bilah Penyaring Atas (Advanced Filter)**:
    - **Live Search Box**: Menyaring ulasan berdasarkan nama pelanggan atau isi komentar secara asinkronus dengan penahan laju `.debounce.400ms`.
    - **Dropdown Filter Rating**: Menyaring tampilan tabel khusus untuk rating tertentu (Contoh: Menampilkan ulasan bintang 1-2 saja untuk respons cepat komplain).
- **Struktur Tabel Ulasan Global**:
  Menampilkan grid tabel dinamis yang terhubung dengan tabel `reviews` dan `users` melalui susunan kolom:
    1. **No.**: Penomoran indeks baris berjalan.
    2. **Nama Klien**: Nama lengkap pelanggan yang memberikan umpan balik (Hasil join relasi `user_id` ke tabel `users`).
    3. **Metrik Rating**: Visualisasi jumlah bintang solid berwarna kuning emas sesuai nilai integer di database ($1 \text{ s/d } 5$).
    4. **Komentar Pelanggan**: Teks opini atau masukan tertulis yang diinput oleh klien.
    5. **Tanggal Kirim**: Waktu pengiriman ulasan berbasis format tanggal lokal Indonesia (`created_at`).
    6. **Aksi**: Tombol kendali cepat untuk menghapus ulasan (_Delete_) jika ditemukan adanya komentar yang mengandung unsur spam atau ujaran tidak pantas.

---

## 2. Aturan Bisnis & Formulasi Agregasi Rating (Laravel 13 Core)

### A. Pengamanan Jalur Pengontrol Berbasis Atribut (Controller Level)

Mematuhi standarisasi mutlak **Laravel 13 Attribute Refactor**, rute dan hak keamanan pada pengontrol ulasan ini dideklarasikan secara deklaratif menggunakan atribut PHP 8 tepat di atas nama kelas:

```php
namespace App\Http\Controllers\Backdoor;

use Illuminate\Routing\Controllers\Controller;
use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('auth')]
#[Middleware('role:superadmin|owner|admin')] // Membuka akses untuk seluruh jajaran manajemen internal
class ClientReviewController extends Controller
{
    // Logika bisnis index dan destroy tetap suci dan tidak berubah
}
```

### B. Formula Kalkulasi Kepuasan Pelanggan (Average Rating Metric)

Untuk menyuplai data statistik pada dokumen cetak laporan ulasan mPDF, sistem menggunakan formulir kalkulasi rata-rata hitung (_Mean_) dari seluruh total rating aktif di database:

$$
\text{Rata-rata Rating Global} = \frac{\sum (\text{reviews.rating})}{\text{COUNT}(\text{reviews.id})}
$$

---

## 3. Batasan Pembatasan Sisi Depan (Front-End Constraints - SFC Style)

- **Hukum Anti-Inline HTML Attribute Script**: Proses penghapusan ulasan spam menggunakan tombol aksi maupun interaksi penyaringan dropdown filter rating haram ditulis langsung di dalam atribut tag HTML (Steril total dari instruksi mentah `onclick="..."` atau `onchange="..."`).
- **Mekanisme Pemuatan Bersisian (Co-location Style)**:
    - Struktur HTML tabel utama diletakkan pada berkas Blade indeks ulasan.
    - Seluruh fungsi AJAX pencarian teks dan eksekusi hapus data via Axios diisolasi di file `review-script.blade.php`.
    - Penyatuan komponen diikat menggunakan direktif `@include` lokal di baris terbawah file induk:

```html
<x-layouts.backdoor>
    <div x-data="clientReviewHandler"></div>

    @include('backdoor.client-reviews.partials.review-script')
</x-layouts.backdoor>
```
