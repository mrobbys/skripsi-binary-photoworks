# Documentation: Admin Operations - Dashboard Widgets Functional Specification

## 1. Komponen Antarmuka Dasbor Pengelola (UI Layout Mapping)

Panel utama dasbor pengelola menggunakan pembungkus shell komponen khusus `<x-layouts.backdoor>` untuk merender seluruh elemen kontrol navigasi internal studio.

### A. Komponen Sidebar Utama Backdoor (`views/layouts/backdoor.blade.php`)

- **Komponen Tampilan (`Dashboard-binary.png`)**:
    - **Identitas Studio**: Menampilkan teks tajuk utama korporat `Binary Photoworks`.
    - **Daftar Navigasi Kontrol**: Menu navigasi vertikal meliputi _Dashboard_ (Aktif), _Jadwal Sesi_, _Manajemen Pemesanan_, _Data Klien_, _Data Master_, _Ulasan Klien_, _Laporan_, dan _Pengaturan Sistem_.
    - **Informasi Profil Autentikasi**: Menampilkan nama operator yang bertugas (`Robby Setiawan`), label hak akses (`Administrator`), dan tombol pemicu aksi `Logout` berekstensi ikon pintu keluar.

### B. Komponen Kartu Statistik Ringkas (Row Widgets)

- **Komponen Tampilan (`Dashboard-binary.png`)**: Menampilkan 3 buah kartu informasi metrik performa studio pada bulan berjalan:
    1. **Total Reservasi (Bulan Ini)**: Kuantitas akumulasi sesi foto yang dipesan (Contoh: `100 Sesi`).
    2. **Total Pendapatan (Bulan Ini)**: Nominal perputaran uang masuk (Contoh: `Rp 10.000.000`).
    3. **Total Klien Terdaftar**: Jumlah keseluruhan akun pengguna tipe pelanggan di dalam sistem database (Contoh: `128 Klien`).

### C. Komponen Visualisasi Grafik Analitik (Sektor Chart.js)

- **Komponen Tampilan (`Dashboard-binary.png`)**:
    - **Dropdown Filter**: Pengontrol seleksi data tahunan di bagian kanan atas (Contoh default: `Tahun 2026`).
    - **Grafik Pendapatan Per Bulan (Sektor Kiri)**: Diagram batang (_Bar Chart_) Chart.js untuk memantau fluktuasi omzet bulanan dari Januari hingga Desember.
    - **Grafik Proporsi Paket Terlaris (Sektor Kanan)**: Diagram donat (_Donut Chart_) Chart.js untuk melihat perbandingan popularitas produk (_Wisuda_, _Family_, _Personal_) dengan total volume penjualan di bagian tengah chart.

### D. Komponen Tabel Monitor Real-Time (Data List Row)

- **Komponen Tampilan (`Dashboard-binary.png`)**:
    - **Tabel Jadwal Pemotretan Hari Ini**: Menampilkan data _Jam Sesi_, _Nama Klien_, _Paket Foto_, dan _Status Sesi_. Dilengkapi badge indikator reaktif (Contoh: `SIAP` berwarna hijau atau `MENUNGGU` berwarna abu-abu).
    - **Tabel Reservasi Terbaru**: Menampilkan baris log transaksi mutakhir yang memuat _Kode Booking_, _Tanggal_, _Total Bayar_, dan badge _Status Pembayaran_ (Contoh: `DP 60% TERBAYAR`).

---

## 2. Aturan Bisnis & Aliran Kueri Data (Backend Logic Execution)

### A. Formula Akumulasi Metrik Finansial

Metrik Pendapatan yang dirender pada kartu statistik ringkas hanya menghitung nominal dana dari transaksi yang memiliki status valid (_Settlement_ / Terbayar), dirumuskan secara matematis sebagai berikut:

$$
\text{Total Pendapatan} = \sum (\text{amount}) \quad \text{where status} = \text{'Lunas'} \lor \text{'DP Terbayar'}
$$

### B. Sinkronisasi Kueri Tabel "Jadwal Hari Ini"

Data yang tampil pada tabel pemotretan harian disaring secara otomatis oleh server menggunakan pencocokan waktu internal server (`Carbon::now()`):

$$
\text{Filter Parameter} \longrightarrow \text{booking\_date} = \text{CURRENT\_DATE} \quad \text{and status} \neq \text{'Batal'}
$$

---

## 3. Batasan Pembatasan Sisi Depan (Front-End Constraints - SFC Style)

- **Hukum Anti-Inline HTML Attribute Script**: Mekanisme pemicu perubahan dropdown filter tahun, interaksi klik navigasi sidebar, maupun animasi hovering grafik haram ditulis langsung di dalam atribut tag HTML mentah elemen view (Bebas dari kode mentah `onchange="..."`).
- **Enkapsulasi Berkas Bersisian (Co-location Style via @include)**:
    - Struktur tata letak grid dashboard diletakkan pada berkas views utama bernama `dashboard.blade.php`.
    - Seluruh logika pemanggilan Chart.js, penembakan data asinkronus Axios untuk filter tahunan, dan penanganan auto-refresh data list tabel diisolasi di dalam file pendukung bernama `dashboard-script.blade.php`.
    - Berkas skrip lokal tersebut wajib disatukan di baris paling bawah file induk dengan instruksi berikut:

```html
<!-- views/backdoor/dashboard.blade.php -->
<x-layouts.backdoor>
    <div x-data="dashboardAnalyticsHandler">
        <!-- Struktur Grid Dashboard Sesuai Gambar Dashboard-binary.png -->
    </div>

    @include('backdoor.dashboard.dashboard-script')
</x-layouts.backdoor>
```

- **Optimasi Request Payload (Debounce)**: Setiap aksi pembaruan grafik analitik Chart.js yang dipicu oleh perubahan dropdown selektor tahun wajib dikawal menggunakan fungsi pengaman Debounce guna menjaga kestabilan beban query database PostgreSQL Supabase.
