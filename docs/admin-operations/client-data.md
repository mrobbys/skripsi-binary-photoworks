# Documentation: Admin Operations - Client Data Management Functional Specification

## 1. Komponen Antarmuka Data Klien (UI Layout Mapping)

Halaman pengelolaan data pelanggan ini berada di bawah kendali komponen pelindung `<x-layouts.backdoor>` dan berfokus pada penyajian informasi profil konsumen secara massal.

### A. Komponen Kartu Ringkasan Klien (Top Widget)

- **Komponen Tampilan (`Data Klien.png`)**: Menampilkan 1 buah kartu indikator kuantitas volume pengguna aktif:
    - **Total Klien Terdaftar**: Menampilkan jumlah total akun konsumen yang telah melakukan registrasi mandiri di dalam sistem aplikasi studio foto (Contoh: `94 Klien`).

### B. Komponen Bilah Kontrol Pencarian

- **Komponen Tampilan (`Data Klien.png`)**:
    - Menyediakan satu kotak input pencarian (_Live Search Box_) di sisi kiri atas tabel dengan placeholder teks `Cari klien...` untuk menyaring baris data secara asinkronus via Alpine.js.

### C. Komponen Struktur DataTables Klien (Grid.js Integration)

- **Komponen Tampilan (`Data Klien.png`)**: Menyajikan struktur tabel dinamis yang memetakan data kolom sebagai berikut:
    1. **No.**: Penomoran indeks urutan baris data pada halaman berjalan.
    2. **Nama Klien**: Nama lengkap sesuai profil akun konsumen (Contoh: `Robby Setiawan`).
    3. **Email**: Alamat surat elektronik aktif milik konsumen (Contoh: `robby@gmail.com`).
    4. **Nomor HP**: Nomor kontak WhatsApp aktif konsumen untuk jalur transmisi gateway Fonnte (Contoh: `081243215364`).
    5. **Total Booking**: Akumulasi kuantitas sesi foto yang pernah dipesan oleh klien terkait di studio, baik status lunas maupun DP (Contoh: `8 Sesi`).
    6. **Aksi**: Tombol kendali tiga titik vertikal untuk memicu kemunculan menu drop-down.

---

## 2. Aturan Bisnis & Algoritma Agregasi Data (Backend Logic Rules)

### A. Formula Penghitungan Total Klien Terdaftar

Sistem menghitung total klien secara riil dari tabel `users` dengan mengecualikan akun yang memiliki keterikatan peran internal (Staff/Admin) di dalam sistem Spatie RBAC, dirumuskan secara matematis sebagai berikut:

$$
\text{Total Klien Terdaftar} = \sum (\text{users}) \quad \text{where not exists} \ (\text{model\_has\_roles})
$$

### B. Kalkulasi Kolom Kumulatif "Total Booking"

Nilai kuantitas pemesanan pada kolom tabel dihitung secara dinamis melalui mekanisme _Eloquent Relationship Counting_ (`withCount('bookings')`) pada model `User.php` dengan kriteria transaksi valid:

$$
\text{Total Booking} = \sum (\text{bookings}) \quad \text{where } \text{bookings.user\_id} = \text{users.id} \land \text{status} \neq \text{'Batal'}
$$

---

## 3. Batasan Pembatasan Sisi Depan (Front-End Constraints - Feature-Based Modules)

- **Hukum Anti-Inline HTML Attribute Script**: Segala bentuk penanganan interaksi pengetikan kata kunci pencarian, pembukaan menu drop-down aksi, maupun perpindahan halaman pagination haram dituliskan langsung di dalam atribut bawaan tag HTML (Bebas penuh dari instruksi mentah `onkeyup="..."`).
- **Enkapsulasi Berkas Bersisian (Co-location Style via @include)**:
    - Struktur tata letak tabel dan widget diletakkan pada berkas views utama bernama `index.blade.php`.
    - Seluruh konfigurasi pustaka Grid.js, penanganan request asinkronus Axios, dan fungsi pemfilteran diisolasi penuh di dalam berkas pendukung bernama `client-script.blade.php`.
    - Proses penyatuan berkas dikunci menggunakan direktif `@include` lokal pada bagian baris paling bawah berkas view induk:

```html
<x-layouts.backdoor>
    <div x-data="clientDataManagementHandler"></div>

    @include('backdoor.client-data.client-script')
</x-layouts.backdoor>
```

- **Optimasi Request Payload (Debounce Control)**: Fitur input pencarian kata kunci pada _Live Search Box_ wajib dikawal oleh fungsi pengaman Debounce minimal sebesar `400ms` guna membatasi lonjakan beban kueri ke server database PostgreSQL Supabase.
