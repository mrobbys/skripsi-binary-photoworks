<div align="center">
  <h1 align="center">Aplikasi Pemesanan dan Penjadwalan Layanan Pada Studio Foto Binary Photoworks</h1>
  
  <p align="center">
    <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13" /></a>
    <a href="https://www.php.net"><img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3+" /></a>
    <a href="https://www.postgresql.org"><img src="https://img.shields.io/badge/PostgreSQL-316192?style=for-the-badge&logo=postgresql&logoColor=white" alt="PostgreSQL" /></a>
    <a href="https://tailwindcss.com"><img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="TailwindCSS" /></a>
    <a href="https://alpinejs.dev"><img src="https://img.shields.io/badge/Alpine.js-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white" alt="AlpineJS" /></a>
  </p>
</div>

---

## Deskripsi Proyek

**Binary Photoworks** adalah aplikasi pemesanan dan penjadwalan layanan studio foto modern yang dirancang untuk mengotomatisasi seluruh alur bisnis studio, mulai dari pemesanan jadwal (booking), pembayaran online, hingga pelaporan kinerja studio. Aplikasi ini mengedepankan efisiensi operasional dan kemudahan bagi pelanggan maupun pengelola studio.

## Tampilan Aplikasi

Berikut adalah pratinjau antarmuka aplikasi Binary Photoworks:

- **Halaman Dashboard User**  
  <img width="2560" height="1600" alt="Image" src="https://github.com/user-attachments/assets/23ec1b24-572c-4073-acc5-c39fe05eed82" />
- **Halaman Services (Katalog Paket)**  
  <img width="2560" height="1600" alt="Image" src="https://github.com/user-attachments/assets/f9866b30-dfc6-4e60-830f-801baed2409b" />
- **Halaman Pilih Paket Varian dan Background**  
  <img width="2560" height="1600" alt="Image" src="https://github.com/user-attachments/assets/76fe1095-c708-48a8-afc6-be6ccddad555" />
- **Halaman Pilih Tanggal dan Slot Waktu**  
  <img width="2560" height="1600" alt="Image" src="https://github.com/user-attachments/assets/19507920-1fc1-4c1b-9b27-6e8c0cbcf89b" />
- **Halaman Pilih Layanan Tambahan**  
  <img width="2560" height="1600" alt="Image" src="https://github.com/user-attachments/assets/09a00223-296e-4b62-bc6c-4290465fe190" />
- **Halaman Ringkasan Pesanan**  
  <img width="2560" height="1600" alt="Image" src="https://github.com/user-attachments/assets/fd75ac74-89a8-40d3-936d-5cddbdaf297e" />
- **Halaman Dashboard Admin**  
  <img width="2560" height="1600" alt="Image" src="https://github.com/user-attachments/assets/6959554d-405b-4379-b9f6-612b1a22ec16" />
- **Halaman Manajemen Pemesanan**  
  <img width="2560" height="1600" alt="Image" src="https://github.com/user-attachments/assets/ca38e402-b122-4b38-9921-d491311b7608" />
- **Halaman Detail Pemesanan / Booking**  
  <img width="2560" height="1600" alt="Image" src="https://github.com/user-attachments/assets/11aeb385-9656-4c6d-b670-a1ca3ca33ba2" />

## Fitur Utama

Aplikasi ini dibagi menjadi dua bagian utama:

### Frontdoor (Portal Klien)

- **Katalog Layanan:** Tampilan detail paket foto, varian, dan layanan tambahan (add-ons).
- **Reservasi Online:** Kalender ketersediaan jadwal pintar untuk mencegah double-booking.
- **Pembayaran Terintegrasi:** Pembayaran otomatis menggunakan Midtrans (mendukung DP dan Pelunasan).
- **Client Area:** Manajemen riwayat pemesanan, akses kuitansi pembayaran, dan pemberian ulasan (testimoni).

### Backdoor (Portal Admin Studio)

- **Dashboard Analitik:** Visualisasi grafik tren volume pesanan, pendapatan bulanan, proporsi kategori, dan layanan tambahan.
- **Manajemen Operasional:** Pengelolaan master data paket, jadwal pemotretan, dan status pesanan.
- **Laporan PDF:** Generate laporan rekapitulasi secara otomatis (Pendapatan, Klien, Transaksi, dll) berformat PDF.
- **Role-Based Access Control (RBAC):** Pemisahan hak akses secara spesifik antara Superadmin, Owner, dan Admin.

## Tech Stack

- **Framework Backend:** Laravel 13 (PHP 8.3+)
- **Frontend / UI:** Alpine.js, Tailwind CSS v4
- **Database / Storage:** PostgreSQL (Supabase), S3-compatible Object Storage
- **Ekstensi Backend (Composer):** 
  - Spatie Laravel Permission *(Role-Based Access Control)*
  - Spatie Laravel PDF & Browsershot *(Generate laporan PDF)*
  - Spatie Activity Log *(Pencatatan log aktivitas)*
  - Spatie MediaLibrary & Sluggable *(Manajemen aset & slug)*
  - Spatie Laravel Data *(Data Transfer Objects / DTO)*
  - Tightenco Ziggy *(Routing Laravel di JavaScript)*
  - League Flysystem AWS S3 *(Driver Supabase Storage)*
- **Pustaka Frontend (NPM):**
  - Motion *(Animasi scroll interaktif & transisi UI modern via motion.dev)*
  - Axios *(HTTP Client)*
  - FullCalendar *(Visualisasi kalender jadwal)*
  - Chart.js *(Grafik dasbor analitik)*
  - FilePond *(Komponen unggah gambar)*
  - Flatpickr *(Pemilihan tanggal reservasi)*
  - Choices.js *(Komponen dropdown/select)*
  - SweetAlert2 & Tippy.js *(Popup alert & tooltip)*
  - Zod *(Validasi form client-side)*
  - Day.js & Currency.js *(Format tanggal & mata uang)*
  - RemixIcon *(Ikon antarmuka)*
- **Integrations:** Midtrans (Payment Gateway), Fonnte (WhatsApp Gateway)

## Panduan Instalasi (Local Development)

Ikuti langkah-langkah berikut untuk menjalankan aplikasi ini di lingkungan lokal Anda.

### Persyaratan Sistem

Pastikan sistem Anda telah memiliki:

- PHP >= 8.3
- Composer >= 2.0
- Node.js & NPM
- PostgreSQL (Atau koneksi remote ke Supabase)

### Langkah Instalasi

1. **Clone Repositori**

   ```bash
   git clone https://github.com/mrobbys/skripsi-binary-photoworks.git
   cd skripsi-binary-photoworks
   ```

2. **Instalasi Dependencies Backend & Frontend**

   ```bash
   composer install
   npm install
   ```

3. **Pengaturan Environment**
   Salin file `.env.example` menjadi `.env`:

   ```bash
   cp .env.example .env
   ```

   Buka file `.env` dan lengkapi konfigurasi database, Midtrans, Fonnte, serta kunci AWS S3 Anda.

4. **Generate Application Key**

   ```bash
   php artisan key:generate
   ```

5. **Migrasi Database & Seeding**
   Jalankan perintah ini untuk membangun tabel dan mengisi data awal (dummy data beserta akun pengguna):

   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Tautkan Storage**

   ```bash
   php artisan storage:link
   ```

7. **Jalankan Aplikasi**
   Aplikasi ini memiliki konfigurasi script pintar. Anda hanya perlu menjalankan satu perintah berikut di terminal untuk menyalakan Server PHP, Vite (Frontend), Queue Worker, dan Task Scheduler secara bersamaan:
   ```bash
   composer run dev
   ```

Aplikasi sekarang dapat diakses melalui `http://localhost:8000`.

### Catatan Khusus: Konfigurasi Laporan PDF (Puppeteer)

Aplikasi ini menggunakan `spatie/laravel-pdf` yang bergantung pada **Puppeteer** dan **Headless Chrome** untuk merender laporan ke dalam format PDF. 

Secara umum, perintah `npm install` sudah secara otomatis mengunduh *binary* Chromium yang dibutuhkan. Namun, jika Anda mengalami kegagalan atau *error* saat mencoba mencetak laporan PDF, pastikan perangkat atau server Anda telah menginstal pustaka (*libraries*) pendukung Chrome berikut:

- `libgconf-2-4`
- `libatk1.0-0`
- `libatk-bridge2.0-0`
- `libgdk-pixbuf2.0-0`
- `libgtk-3-0`
- `libgbm-dev`
- `libnss3-dev`
- `libxss-dev`
- `libasound2`

### Catatan Khusus: Penyimpanan File (Supabase S3)
Aplikasi ini secara bawaan dikonfigurasi untuk menggunakan **Supabase Storage** (berbasis protokol AWS S3) untuk menyimpan berbagai aset gambar unggahan seperti foto varian paket, gambar *background*, dan bukti pembayaran. 

Untuk memastikan fitur *upload* berjalan normal, Anda perlu melakukan pengaturan berikut di *dashboard* Supabase Anda:
1. Buat sebuah **Bucket** baru di menu *Storage*.
2. Pastikan Anda mengatur *bucket* tersebut menjadi **Public** agar gambar dapat diakses dan ditampilkan di aplikasi.
3. Buka menu **Project Settings -> Storage -> S3 Credentials**.
4. Salin kredensial *Access Key*, *Secret Key*, dan *Endpoint*, lalu masukkan ke dalam variabel `AWS_` di file `.env` Anda.

## Akses Pengujian

Gunakan kredensial akun berikut untuk menguji aplikasi pada berbagai level hak akses:

| Role           | Email                  | Password    | Hak Akses                                        |
| :------------- | :--------------------- | :---------- | :----------------------------------------------- |
| **superadmin** | `superadmin@gmail.com` | `Password1` | Kendali penuh seluruh sistem & pengaturan        |
| **owner**      | `owner@gmail.com`      | `Password1` | Pemantauan laporan, analitik, & rekapitulasi     |
| **admin**      | `admin@gmail.com`      | `Password1` | Manajemen operasional harian & data pesanan      |
| **user**       | `user@gmail.com`       | `Password1` | Akses portal klien, pembuatan pesanan, & riwayat |

_(Catatan: Semua password default dari seeder adalah `Password1`)_

## Struktur Direktori

Berikut adalah gambaran struktur folder utama pada repositori aplikasi ini:

```text
skripsi-binary-photoworks/
├── app/
│   ├── Console/           # Perintah kustom artisan dan task scheduler
│   ├── Domains/           # Logika bisnis terpisah (Booking, Payment, MasterData, PdfReports, dll)
│   ├── Http/              # Controller dasar dan Middleware
│   ├── Providers/         # App Service Providers
│   └── Support/           # Helper dan class pendukung (seperti Formatter)
├── config/                # File konfigurasi utama (database.php, permission.php, dll)
├── database/
│   ├── migrations/        # Skema struktur database
│   └── seeders/           # File dummy data awal (RoleSeeder, UserSeeder, dll)
├── resources/
│   ├── css/               # File CSS aplikasi (menggunakan Tailwind v4)
│   ├── js/                # File JavaScript (Alpine.js features dan komponen)
│   └── views/             # File Blade template (frontdoor, backdoor, components, pdfs)
├── routes/
│   ├── auth.php           # Rute otentikasi pengguna
│   ├── backdoor/          # Rute portal admin (Dashboard, Laporan, Manajemen)
│   ├── frontdoor/         # Rute portal klien (Katalog, Pemesanan, Transaksi)
│   ├── console.php        # Definisi perintah scheduler (pengingat H-1)
│   └── web.php            # Rute utama aplikasi
├── public/                # Aset publik (gambar, font, file hasil build Vite)
├── .env.example           # Template konfigurasi environment
├── composer.json          # Dependensi PHP (Laravel, Spatie, dll)
└── package.json           # Dependensi JavaScript (Tailwind v4, Alpine.js, Vite)
```
