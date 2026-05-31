# Documentation: 03 - Tech Stack & Domain Architecture Reference

## 1. Core Technology Stack & Infrastructure

- **Framework Core**: Laravel 13
- **Authentication Starter**: Laravel Breeze (Custom Front-End UI Layer)
- **Database Engine**: PostgreSQL hosted on Supabase
- **Object Storage**: Supabase Storage (S3-Compatible Integration)
- **Front-End UI Engine**: Alpine.js & Tailwind CSS
- **Development Tools**: Prettier (with Tailwind CSS & Blade plugins), ESLint
- **Third-Party Integrations**: Midtrans, Fonnte

---

## 2. Comprehensive Package Inventory

### A. Backend Components (Composer Ecosystem)

1. **`spatie/laravel-permission`**: Pengendali otorisasi berbasis Role & Permission (Superadmin, Admin, Owner, User).
2. **`spatie/laravel-activitylog`**: Pencatat otomatis log aktivitas/audit trail operasional di panel admin.
3. **`spatie/laravel-medialibrary`**: Pengelola unggahan file gambar background studio ke penyimpanan cloud.
4. **`spatie/laravel-sluggable`**: Generator otomatis text-slug pengaman URL untuk data kategori dan paket.
5. **`spatie/laravel-data`**: Pustaka pembuat objek Data Transfer Object (DTO) yang aman dan bertipe data pasti (_type-safe_).
6. **`spatie/laravel-pdf`**: Mesin perakit cetak laporan PDF modern menggunakan Headless Chrome (Browsershot wrapper).
7. **`midtrans/midtrans-php`**: Kit SDK resmi penyambung transaksi pembayaran Snap Pop-up dan penangan webhook.
8. **`league/flysystem-aws-s3-v3`**: Driver penghubung sistem file S3 agar Laravel bisa membaca Supabase Storage.
9. **`barryvdh/laravel-debugbar`**: Alat bantu debugging untuk memantau performa query, memori, dan execution time selama fase development.
10. **`getsolaris/laravel-make-service`**: Ekstensi Artisan command untuk mempercepat pembuatan file blueprint Service Layer baru secara otomatis.

### B. Frontend Components (NPM Ecosystem)

1. **`tailwindcss` & `autoprefixer`**: Kerangka kerja penyusun antarmuka visual layout admin & klien.
2. **`alpinejs`**: Motor penggerak utama modul reaktif interaktif pada form booking dan panel melayang.
3. **`axios`**: Handler pengiriman data HTTP Request berbasis AJAX asinkronus.
4. **`fullcalendar`**: Komponen visual kalender manajemen jadwal booking studio fotografer.
5. **`chart.js`**: Komponen pembuat grafik analitik dashboard pendapatan pemilik studio.
6. **`filepond`**: Komponen antarmuka unggah berkas (gambar) interaktif dengan fitur drag-and-drop.
7. **`flatpickr`**: Pustaka pemilih tanggal (date picker) yang ringan dan tangguh untuk form kalender.
8. **`remixicon`**: Pustaka ikon berbasis open-source untuk kebutuhan antarmuka visual yang modern.
9. **`sweetalert2`**: Pembuat pop-up notifikasi cantik dan responsif sebagai pengganti alert standar peramban.
10. **`choices.js`**: Elemen kendali input khusus untuk select box tingkat lanjut dengan fitur pencarian dan kustomisasi.
11. **`grid.js`**: Pustaka data table yang ringan dan modular untuk menangani pencarian, pengurutan (sorting), serta pembagian halaman (pagination) data secara dinamis pada panel pengelola.

---

## 3. Modular Domain-Driven Architecture Blueprint

Struktur direktori `app/` mengadopsi pola pemisahan domain bisnis terisolasi untuk menjamin kode tetap bersih, mudah dirawat, dan memiliki tingkat pembagian tugas yang jelas (_Separation of Concerns_):

```text
app/
 ├── Domains/
 │    ├── Auth/                                 # DOMAIN: OTENTIKASI PENGGUNA
 │    │    ├── Models/
 │    │    │    └── User.php
 │    │    ├── Repositories/
 │    │    │    └── UserRepository.php
 │    │    ├── Services/
 │    │    │    └── AuthService.php              # Logika enkripsi, session, & tokens
 │    │    ├── DTOs/
 │    │    │    ├── LoginData.php               # Data objek validasi login
 │    │    │    ├── RegisterData.php            # Data objek registrasi klien
 │    │    │    └── ResetPasswordData.php       # Data objek pemulihan sandi
 │    │    └── Http/
 │    │         ├── Controllers/
 │    │         │    ├── LoginController.php    # Handler masuk akun
 │    │         │    ├── RegisterController.php # Handler daftar klien baru
 │    │         │    └── ResetPasswordController.php
 │    │         └── Requests/
 │    │              ├── LoginRequest.php
 │    │              ├── RegisterRequest.php
 │    │              └── ResetPasswordRequest.php
 │    │
 │    ├── Studio/                               # DOMAIN: OPERASIONAL STUDIO & JAM KERJA
 │    │    ├── Models/
 │    │    │    └── Schedule.php
 │    │    ├── Repositories/
 │    │    │    └── ScheduleRepository.php       # Query pengecekan jam operasional buka-tutup
 │    │    ├── Services/
 │    │    │    └── ScheduleService.php          # Logika perubahan jam kerja operasional
 │    │    ├── DTOs/
 │    │    │    └── ScheduleData.php
 │    │    └── Http/
 │    │         ├── Controllers/Admin/
 │    │         │    └── ScheduleController.php # Controller setting jam kerja oleh Admin
 │    │         └── Requests/
 │    │              └── UpdateScheduleRequest.php
 │    │
 │    ├── Catalog/                              # DOMAIN: KATALOG LAYANAN & MASTER DATA STUDIO
 │    │    ├── Models/
 │    │    │    ├── Category.php
 │    │    │    ├── Package.php
 │    │    │    ├── PackageVariant.php
 │    │    │    ├── Background.php
 │    │    │    └── Addon.php
 │    │    ├── Repositories/
 │    │    │    ├── CategoryRepository.php
 │    │    │    ├── PackageRepository.php
 │    │    │    ├── PackageVariantRepository.php
 │    │    │    ├── BackgroundRepository.php
 │    │    │    └── AddonRepository.php
 │    │    ├── Services/
 │    │    │    ├── CatalogMasterService.php     # Logika CRUD seluruh master entitas studio
 │    │    │    └── VariantPriceService.php      # Kalkulator kombinasi harga paket + addons
 │    │    ├── DTOs/
 │    │    │    ├── CategoryData.php
 │    │    │    ├── PackageData.php
 │    │    │    ├── PackageVariantData.php
 │    │    │    ├── BackgroundData.php
 │    │    │    └── AddonData.php
 │    │    └── Http/
 │    │         ├── Controllers/Admin/
 │    │         │    ├── CategoryController.php   # CRUD Kategori Foto
 │    │         │    ├── PackageController.php    # CRUD Paket Foto Utama
 │    │         │    ├── VariantController.php    # CRUD Detail Varian Sesi Foto
 │    │         │    ├── BackgroundController.php # CRUD Layar Latar Studio
 │    │         │    └── AddonController.php      # CRUD Layanan Tambahan
 │    │         └── Requests/
 │    │              ├── StoreCategoryRequest.php
 │    │              ├── StorePackageRequest.php
 │    │              ├── StoreVariantRequest.php
 │    │              ├── StoreBackgroundRequest.php
 │    │              └── StoreAddonRequest.php
 │    │
 │    └── Booking/                              # DOMAIN: RESERVASI SAKRAL & ULASAN
 │         ├── Models/
 │         │    ├── Booking.php
 │         │    ├── AddonBooking.php
 │         │    └── Review.php
 │         ├── Repositories/
 │         │    ├── BookingRepository.php        # Query kunci jadwal, deteksi bentrok, & invoice
 │         │    └── ReviewRepository.php         # Query penarikan ulasan global
 │         ├── Services/
 │         │    ├── BookingScheduleValidator.php # Algoritma inti penjamin 0% double booking
 │         │    └── ReviewService.php            # Manajemen ulasan publik bergaya Setmore/Google Maps
 │         ├── DTOs/
 │         │    ├── BookingReservationData.php   # Penampung data multi-step form booking
 │         │    ├── ReviewData.php
 │         │    └── PaymentCallbackData.php      # Pemetaan data kiriman Webhook Midtrans
 │         └── Http/
 │              ├── Controllers/
 │              │    ├── Client/
 │              │    │    ├── BookingFlowController.php # Pemandu form booking bertahap klien
 │              │    │    └── ClientReviewController.php # Input rating bintang dari konsumen
 │              │    └── Admin/
 │              │         ├── OrderManagementController.php # Pengendali transaksi & pelunasan kasir
 │              │         └── BookingCalendarController.php # Penyedia data untuk FullCalendar.js view
 │              └── Requests/
 │                   ├── SubmitReservationRequest.php
 │                   └── StoreReviewRequest.php
 │
 ├── Services/                                  # SEKTOR LAYER LAYANAN GLOBAL (Non-Domain Specific)
 │    ├── MidtransService.php                   # Pembuat token SNAP & validasi respon API Midtrans
 │    └── FonnteService.php                     # Handler tembakan API gateway pengiriman WhatsApp
 │
 ├── Repositories/                              # Wadah Blueprint Base/Generic Repository
 ├── Console/                                   # Kumpulan Artisan Commands bikinan sendiri
 └── Providers/                                 # Pengatur konfigurasi Service Providers bawaan aplikasi
```

---

## 4. Modular Front-End & Asset Architecture Blueprint (`resources/`)

Struktur direktori `resources/` mengadopsi kombinasi modularitas _Component Co-location_ khas React dengan tetap mempertahankan efisiensi runtime monolitik Laravel murni, terpetakan secara menyeluruh sesuai arsitektur sitemap sistem:

```text
resources/
 ├── js/
 │    ├── app.js                        # Default Laravel entry point (Compiled via Vite)
 │    ├── bootstrap.js                  # Default Laravel bootstrap layer
 │    │
 │    ├── lib/                          # KONFIGURASI CENTRAL THIRD-PARTY LIBRARIES
 │    │    ├── axios.js                 # Axios Instance + Interceptors Global (Zero 419/500 Error)
 │    │    ├── grid.js                  # Pengaturan tema, localization, & boilerplate Grid.js
 │    │    └── sweetalert.js            # Preset & Mixin bawaan notifikasi SweetAlert2
 │    │
 │    └── utils/                        # KLASTER KODE HELPER GLOBAL (React Barrel Pattern)
 │         └── index.js                 # Export tunggal utilitas (formatRupiah, debounce, dll)
 │
 └── views/
      ├── layouts/                      # INDUK LAYOUT SHELLS (Format Tag Pembungkus <x-...>)
      │    ├── frontdoor.blade.php      # Base layout template untuk area umum / klien
      │    ├── backdoor.blade.php       # Base layout template untuk area pengelola / admin
      │    └── auth.blade.php           # Base layout template khusus halaman otentikasi
      │
      ├── components/                   # SHARED UI PRIMITIVES (Komponen Global Bersama - Kebab-case)
      │    ├── toggle-switch.blade.php  # Komponen sakelar geser (statis & stateless)
      │    ├── modal-backdrop.blade.php # Komponen dasar overlay hitam melayang
      │    ├── input-error.blade.php    # Komponen render alert kesalahan validasi form
      │    └── submit-button.blade.php  # Tombol reaktif dengan animasi loading spinner
      │
      ├── auth/                         # 1. MODUL LEVEL: OTENTIKASI
      │    ├── components/              # Komponen eksklusif cluster Auth (ex: auth-card.blade.php)
      │    │
      │    ├── login/                   # Klaster Fitur: Halaman Masuk Akun
      │    │    ├── index.blade.php     # Struktur UI Form HTML & Tailwind CSS
      │    │    └── script.blade.php    # Skrip Alpine.js lokal (Toggle mata sandi & loading submit)
      │    │
      │    ├── register/                # Klaster Fitur: Halaman Pendaftaran Klien
      │    │    ├── index.blade.php     # Struktur UI Form HTML & Tailwind CSS
      │    │    └── script.blade.php    # Skrip Alpine.js lokal (Validasi kesamaan konfirmasi sandi)
      │    │
      │    └── reset-password/          # Klaster Fitur: Halaman Pemulihan Sandi
      │         ├── index.blade.php     # Struktur UI Form HTML & Tailwind CSS
      │         └── script.blade.php    # Skrip Alpine.js lokal (Form request token & update password)
      │
      ├── frontdoor/                    # 2. MODUL LEVEL: FRONTDOOR (Sitemap Utama Klien)
      │    │
      │    ├── home/                    # Klaster Fitur: Landing Page
      │    │    ├── index.blade.php     # Struktur Utama Page Induk
      │    │    └── sections/           # Pemecahan Sekat Komponen Landing Page (Co-location)
      │    │         ├── hero.blade.php
      │    │         ├── service-categories.blade.php
      │    │         ├── why-choose-us.blade.php
      │    │         ├── portfolio-grid.blade.php
      │    │         ├── testimonials.blade.php
      │    │         ├── location-hours.blade.php
      │    │         └── faq.blade.php
      │    │
      │    ├── about/                   # Klaster Fitur: Profil Tentang Kami
      │    │    ├── index.blade.php     # Explanation About Us
      │    │    └── our-team.blade.php  # Komponen Struktur Anggota Tim Studio
      │    │
      │    ├── services/                # Klaster Fitur: Tampilan Katalog Paket Foto
      │    │    └── index.blade.php     # Display All Packages (Proteksi Login Interceptor)
      │    │
      │    ├── booking/                 # Klaster Fitur: Form Reservasi Bertahap
      │    │    ├── flow.blade.php      # Multi-step Form UI (State Step 1 s.d Step 5)
      │    │    └── script.blade.php    # Logika Hitung Otomatis & Validasi Overlap Slot Jadwal
      │    │
      │    ├── faq/                     # Klaster Fitur: Pusat Pertanyaan (All FAQ)
      │    │    └── index.blade.php
      │    │
      │    ├── reviews/                 # Klaster Fitur: Kolom Ulasan Publik (All Reviews)
      │    │    └── index.blade.php
      │    │
      │    ├── contact/                 # Klaster Fitur: Hubungi Kami
      │    │    ├── index.blade.php     # Contact Form & Connect With Us Layout
      │    │    └── map-location.blade.php
      │    │
      │    └── dashboard/               # Klaster Fitur: Dasbor Akun Klien Pribadi
      │         ├── components/         # Komponen internal dasbor user
      │         │    └── appointment-detail-modal.blade.php # Pop-up detail janji temu
      │         ├── index.blade.php     # Urutan Janji Temu (Upcoming & Past Appointments)
      │         ├── script.blade.php    # Handler fetch appointments history via Axios
      │         └── details.blade.php   # Halaman "Your Details" (Update Profil Klien)
      │
      └── backdoor/                     # 3. MODUL LEVEL: BACKDOOR (Sitemap Utama Pengelola)
           │
           ├── dashboard/               # Klaster Fitur: Dasbor Analitik Utama
           │    ├── index.blade.php     # Widget Ringkasan Statistik & Tampilan Hari Ini
           │    └── script.blade.php    # Handler Chart.js rendering via AJAX tahunan
           │
           ├── jadwal-sesi/             # Klaster Fitur: Manajemen Kalender Kerja
           │    ├── calendar.blade.php  # Tampilan visual FullCalendar.js sebulan penuh
           │    ├── calendar-script.blade.php # Sinkronisasi JSON data booking ke kalender
           │    └── list.blade.php      # Alternatif Tampilan: Daftar baris jadwal internal
           │
           ├── manajemen-pemesanan/     # Klaster Fitur: Data Transaksi Order
           │    ├── index.blade.php     # DataTables / Grid.js transaksi reservasi global
           │    └── script.blade.php    # Handler status update, link GDrive, & pelunasan kasir
           │
           ├── data-klien/              # Klaster Fitur: Registri Pelanggan Terdaftar
           │    ├── index.blade.php     # Tabel daftar data user dengan role 'user'
           │    └── script.blade.php    # Handler Axios filter dan audit profile detail
           │
           ├── data-master/             # Klaster Fitur: Gudang Data Master Studio
           │    ├── components/         # Komponen bersama internal sub-modul data-master
           │    │    └── tes-komponen.blade.php
           │    ├── category/           # Sub-Fitur: Kategori Foto (Modal Form)
           │    │    ├── index.blade.php
           │    │    └── script.blade.php
           │    ├── package/            # Sub-Fitur: Kelola Paket & Varian (Drawer Form)
           │    │    ├── index.blade.php
           │    │    ├── script.blade.php
           │    │    └── scripts/       # Pembagian sub-logika JS pendukung
           │    │         ├── script-pertama.blade.php
           │    │         └── script-kedua.blade.php
           │    ├── background/         # Sub-Fitur: Latar Belakang Studio (FilePond)
           │    │    ├── index.blade.php
           │    │    └── script.blade.php
           │    ├── addon/              # Sub-Fitur: Layanan Tambahan (Counter vs Checkbox)
           │    │    ├── index.blade.php
           │    │    └── script.blade.php
           │    └── schedule/           # Sub-Fitur: Jadwal Operasional (Inline Auto-Save)
           │         ├── index.blade.php
           │         └── script.blade.php
           │
           ├── ulasan-klien/            # Klaster Fitur: Moderasi Testimoni Global
           │    ├── index.blade.php     # Pengaturan visibilitas ulasan bintang klien
           │    └── script.blade.php
           │
           ├── laporan/                 # Klaster Fitur: Sistem Pelaporan Kasir
           │    ├── index.blade.php     # Panel parameter cetak laporan keuangan/booking
           │    └── script.blade.php    # Action trigger penembakan mPDF layer converter
           │
           └── pengaturan-sistem/       # Klaster Fitur: Pengaturan Internal Tim
                ├── role/               # Sub-Fitur: Spatie Role & Permission Management
                │    ├── index.blade.php
                │    └── script.blade.php
                ├── user/               # Sub-Fitur: Manajemen Akun Staff / Internal Team
                │    ├── index.blade.php
                │    └── script.blade.php
                └── activity-log/       # Sub-Fitur: Audit Trail Log Aktivitas Admin
                     └── index.blade.php
```

### A. Aturan Isolasi Berkas Berbasis Fitur (Domain Views Isolation)

- Seluruh halaman utama diletakkan di dalam folder `views/domains/{fitur}/` untuk mencerminkan arsitektur domain backend secara 1-to-1.
- Berkas view wajib ditulis menggunakan metode **Single File Component (SFC) Style**, di mana elemen HTML/Tailwind berada di atas, dan logika reaktif Alpine.js dibungkus rapi di dalam tag `<script>` khusus pada bagian paling bawah file yang sama.

### B. Klaster Komponen Global (Shared UI Primitives)

- Komponen visual yang digunakan berulang kali di banyak domain (seperti sakelar toggle, badge status, atau kerangka dasar modal) wajib dibuatkan file terpisah di dalam `views/components/`.
- Komponen global ini bersifat _stateless_ atau hanya menerima parameter kiriman atribut data dari file induk menggunakan direktif `@props` bawaan Laravel Blade.

### C. Standardisasi Utilitas & Axios Instance (`js/shared/`)

- **`axios-instance.js`**: Pembuatan konfigurasi terpusat untuk interaksi AJAX asinkronus. Berkas ini bertugas menyuntikkan _Header X-CSRF-TOKEN_ secara otomatis di setiap tembakan request, serta mengonfigurasi penanganan eror global (seperti pop-up notifikasi cantik SweetAlert2 otomatis jika server mengalami gangguan 500).
- **`utils.js`**: Menyediakan fungsi pembantu terpusat yang sering dipanggil di dalam tag `<script>` Blade view, seperti fungsi `formatRupiah(angka)` untuk pemetaan nominal mata uang, dan fungsi `debounce(func, wait)` untuk membatasi detak frekuensi eksekusi AJAX input pencarian data master.

---

## 5. Layered Data Flow & Clean Code Principles

Setiap komponen di dalam sub-folder Domain memiliki peran kaku yang tidak boleh saling tumpang tindih:

1. **`Form Request Layer`** &rarr; Hanya bertanggung jawab melakukan validasi kepatuhan format data input mentah dari klien.
2. **`DTO Layer (Spatie Data)`** &rarr; Mengonversi data hasil validasi menjadi objek yang memiliki kepastian tipe (_type-safe_) untuk dialirkan antar layer.
3. **`Controller Layer (Slim)`** &rarr; Hanya bertindak sebagai pengatur lalu lintas. Menerima request, memanggil service yang tepat, dan mengembalikan respons (HTML/JSON). Tidak boleh ada logika bisnis atau query di sini!
4. **`Service Layer (Fat)`** &rarr; Pusat dari seluruh operasi logika bisnis aplikasi (perhitungan harga DP, kalkulasi ekstra durasi, memicu event kirim WhatsApp via Fonnte Service).
5. **`Repository Layer`** &rarr; Lapisan tunggal pemisah yang memegang kendali penuh atas query database (Eloquent builder, pengecekan ketersediaan slot tanggal jam). Mencegah controller atau service menyentuh database secara liar.

---

## 6. Environment & Storage Schema Configurations (.env)

```ini
# Database Core System (PostgreSQL Supabase)
DB_CONNECTION=pgsql
DB_HOST=your-supabase-instance.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-supabase-password

# Object Storage Asset (S3-Compatible Config for Spatie Media Library)
SUPABASE_STORAGE_ACCESS_KEY=your-project-ref-id
SUPABASE_STORAGE_SECRET_KEY=your-service-role-key
SUPABASE_STORAGE_BUCKET=binary-assets
SUPABASE_STORAGE_ENDPOINT=https://your-project-id.supabase.co/storage/v1/s3
SUPABASE_STORAGE_URL=https://your-project-id.supabase.co/storage/v1/object/public/binary-assets

# Third-Party Vendor Gateways
MIDTRANS_MERCHANT_ID=
MIDTRANS_CLIENT_KEY=
MIDTRANS_SERVER_KEY=
MIDTRANS_IS_PRODUCTION=false

FONNTE_TOKEN=
```

---

## 7. Absolute Engineering Constraints (Aturan Mutlak Front-End)

- **ANTI INLINE HTML ATTRIBUTE SCRIPTS**: Haram hukumnya menuliskan baris kode eksekusi JavaScript langsung di dalam atribut tag elemen HTML Blade view (menghindari penggunaan atribut fungsional mentah seperti `onclick="..."` atau `onchange="..."`).
- **FRONTEND LOGIC ENCAPSULATION (SFC STYLE)**: Logika reaktif komponen wajib dikemas rapi di dalam blok tag `<script>` khusus yang diletakkan di bagian paling bawah pada file `.blade.php` yang sama. Inisialisasi wajib menggunakan struktur standarisasi `document.addEventListener('alpine:init')` dan dipanggil via direktif `Alpine.data()`. Hal ini memangkas ketergantungan pendaftaran file asset baru di `vite.config.js`.
- **AJAX & AUTO-SAVE CONTEXT**: Operasi kirim data tanpa interupsi reload (seperti fitur auto-save pada pengaturan jam kerja operasional atau filter pencarian data master) wajib dieksekusi via Axios dengan kawalan fungsi _Debounce_ demi efisiensi performa server.
