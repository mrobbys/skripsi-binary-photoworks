# Documentation: Data Master - Category Functional Specification (CRUD Modal)

## 1. Komponen Antarmuka Utama (Main Dashboard View)

Halaman utama manajemen kategori mengadopsi tata letak dasbor admin dengan komponen visual berikut:

### A. Widget Metrik Ringkasan

- **Total Kategori Aktif Card**: Menampilkan total akumulasi kategori foto dengan status aktif di database (Contoh visual: `5 Kategori`).

### B. Baris Kendali & Navigasi (Control Bar)

- **Input Pencarian**:
    - Komponen input teks dengan placeholder `Cari nama kategori...`.
    - Sifat: Bersifat dinamis memfilter baris tabel di bawahnya secara asinkronus.
- **Tombol "+ Tambah Kategori"**: Tombol aksi utama (CTA) berwarna terang yang memicu kemunculan jendela melayang (_Modal Form_) untuk membuat kategori baru.

### C. Tabel Data Kategori (Data Table Layout)

Tabel menampilkan daftar kategori terdaftar dengan kolom sebagai berikut:

1. **No.**: Penomoran baris berurut.
2. **Nama Kategori**: Teks tebal penanda entitas (Contoh: `Wisuda & Group`, `Personal Portrait`, `Pre-Wedding Luxury`, `Maternity & New Born`, `Product & Branding`).
3. **Status**: Sakelar geser (_Toggle Switch_) penanda visibilitas operasional (`is_active`).
4. **Aksi**: Tombol menu vertikal tiga titik (`⋮`) untuk memicu menu turunan seperti aksi _Edit_ dan _Hapus_.

---

## 2. Komponen Formulir Jendela Melayang (Modal Form View)

Jendela modal muncul di tengah layar dengan latar belakang gelap transparan (_backdrop overlay_) saat tombol tambah atau aksi edit dipicu.

### A. Komponen Input & Atribut Form

- **Judul Modal**: Berubah dinamis antara `Tambah Kategori` atau `Edit Kategori` lengkap dengan tombol silang (`X`) di sudut kanan untuk menutup jendela.
- **Input Nama Kategori**:
    - Tipe: Teks String.
    - Label: `Nama Kategori`.
    - Placeholder: `Masukkan nama kategori (ex: Graduation, Birthday)`.
    - Sifat: Wajib diisi (_Required_).
- **Sakelar Status Kategori Aktif**:
    - Tipe: Checkbox bergaya Toggle Switch (`is_active`).
    - Keterangan Bantuan di Bawah Input: `Jika aktif, kategori ini akan langsung muncul di halaman booking klien.`.

### B. Tombol Aksi Kaki Modal (Footer Buttons)

- **Tombol "Batal"**: Tombol sekunder polos untuk menutup modal tanpa menyimpan perubahan data.
- **Tombol "Simpan Kategori"**: Tombol utama berwarna gelap untuk mengeksekusi penyimpanan data ke database server.

---

## 3. Aturan Validasi Bisnis & Pemetaan Database (Backend Layer)

### A. Aturan Validasi Form Request (Server-Side)

- `name`: Wajib terisi (_required_), tipe string, maksimal 100 karakter, dan harus unik pada tabel `categories` (kecuali pada aksi edit data milik sendiri).
- `is_active`: Wajib diisi (_required_), bertipe data boolean (true/false).

### B. Otomatisasi Generator Slug (Spatie Sluggable)

- Pengembang tidak perlu menyediakan kolom input slug pada antarmuka.
- Server secara otomatis memicu paket `spatie/laravel-sluggable` untuk merakit teks slug aman URL berdasarkan nilai input `name` sebelum baris data disimpan ke database Supabase.

---

## 4. Batasan Implementasi Front-End & Kontrol Aliran Data

- **Aturan Mutlak Anti-Inline Attribute Script**: Dilarang keras menuliskan baris penanganan logika buka-tutup modal atau pembacaan nilai toggle langsung di dalam atribut tag HTML komponen Blade view (seperti menghindari penggunaan atribut fungsional mentah `onclick="..."` atau `onchange="..."`).
- **Enkapsulasi Reaktif Alpine.js (SFC Style)**: Manajemen status keterbukaan modal (`isOpen: false`), penyimpanan data id kategori terpilih untuk diedit, dan manipulasi visual tombol wajib diisolasi rapi menggunakan standardisasi pola `document.addEventListener('alpine:init')` dan dipicu melalui direktif `Alpine.data()` di dalam tag `<script>` khusus yang diletakkan di bagian bawah berkas `.blade.php` yang sama.
- **Optimasi AJAX & Debounce Pencarian**:
    - Proses pemfilteran data tabel melalui input pencarian nama kategori wajib dikirim menggunakan pustaka **Axios**.
    - Aksi pengetikan kata kunci wajib dikawal oleh fungsi pengaman **Debounce minimal 400ms** untuk mencegah penembakan query database secara berlebihan (_spamming queries_) setiap kali pengguna menekan tombol papan ketik.
- **Pemicu Toggle Instan (Instant Save)**: Mengubah sakelar status aktif/non-aktif kategori pada baris tabel akan langsung memicu pengiriman data AJAX Axios di latar belakang untuk memperbarui status kolom `is_active` secara instan tanpa perlu memunculkan modal konfirmasi tambahan.
