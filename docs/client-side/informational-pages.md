# Documentation: Client-Side - Informational Pages Functional Specification

## 1. Komponen Antarmuka Halaman Beranda (`views/frontdoor/home/`)

Halaman Beranda menggunakan pembungkus induk `<x-layouts.frontdoor>` dan dipecah menjadi sub-komponen sekat bagian (_sections co-location_) guna menjaga efisiensi pemeliharaan baris kode UI.

### A. Cetak Biru Struktur Berkas Fitur

```text
views/frontdoor/home/
 ├── index.blade.php              # File Utama Penggabung Seluruh Sekat
 └── sections/                    # Pecahan Komponen Visual Beranda
      ├── hero.blade.php
      ├── service-categories.blade.php
      ├── why-choose-us.blade.php
      ├── portfolio-grid.blade.php
      ├── testimonials.blade.php
      ├── location-hours.blade.php
      └── faq-preview.blade.php
```

### B. Spesifikasi Rincian Komponen Sekat (`Home.png`)

1. **Hero Section (`sections/hero.blade.php`)**: Menampilkan teks tajuk utama _"Abadikan Momen Berharga Anda"_, deskripsi sub-judul pemasaran, dan tombol aksi (_Call to Action_) `Lihat Katalog` yang mengarah ke modul halaman katalog layanan.
2. **Layanan Kami (`sections/service-categories.blade.php`)**: Tata letak grid tiga kolom berisi kartu kategori foto (_Wisuda_, _Family_, _Personal_) lengkap dengan gambar representatif hitam-putih artistik.
3. **Mengapa Memilih Binary? (`sections/why-choose-us.blade.php`)**: Komposisi dua kolom; gambar fotografer studio di sisi kiri, dan daftar empat poin keunggulan bernilai (_Authenticity_, _Comfortable_, _Timeless_, _Flexible_) di sisi kanan.
4. **Portofolio (`sections/portfolio-grid.blade.php`)**: Kisi galeri asimetris bergaya kolase dinamis untuk menampilkan mahakarya foto studio terbaik.
5. **Ulasan Klien (`sections/testimonials.blade.php`)**: Baris kartu testimoni horizontal yang memuat peringkat bintang (_star rating score_) komponen `<x-input-error />` bergaya statis, teks komentar dari konsumen (Arya Perdana, Siti Nurhaliza, Kevin Wijaya), dan nama penulis ulasan.
6. **Jam Operasional & Peta (`sections/location-hours.blade.php`)**: Kolom kiri merender kartu thumbnail peta lokasi statis dan teks alamat fisik studio (_Jl. Senopati No. 45, Jakarta Selatan_). Kolom kanan merender tabel waktu buka-tutup operasional yang ditarik secara dinamis dari database.
7. **FAQ Preview (`sections/faq-preview.blade.php`)**: Menampilkan 4 baris akordion pertanyaan terpopuler dan tombol kaki tautan `Lihat Semua FAQ`.
8. **Terhubung Dengan Kami (Footer Action)**: Menyediakan jajaran ikon media sosial (Instagram, Facebook, Twitter, TikTok), tombol tautan langsung API WhatsApp Fonnte Gateway, serta rincian kontak teks di baris paling bawah halaman.

---

## 2. Komponen Antarmuka Halaman Tentang Kami (`views/frontdoor/about/`)

Modul ini berfokus pada penyajian informasi riwayat pembentukan bisnis studio foto dan pengenalan profil jajaran tim pengelola internal.

### A. Rincian Sekat Antarmuka (`About.png`)

- **Kisah & Misi Kami (`index.blade.php`)**: Layout asimetris yang menaruh potret suasana perlengkapan lampu pencahayaan indoor studio foto di sisi kiri, serta blok teks narasi kronologis sejarah berdirinya studio foto beserta visi misi perusahaan di sisi kanan.
- **Tim Kami (`our-team.blade.php`)**: Kompleksitas grid kartu profil beranggotakan jajaran tim fotografer dan manajemen internal (Contoh: _Arya Saloka - Founding Partner_), dikemas menggunakan format kartu seragam.

---

## 3. Komponen Antarmuka Halaman FAQ Global (`views/frontdoor/faq/`)

Halaman ini memuat seluruh bank data pertanyaan dan jawaban yang sudah diinput oleh administrator melalui panel backdoor pengelola.

### A. Logika Sistem Filter Tabulasi (`FAQ.png`)

- **Mekanisme Navigasi**: Menyediakan pengontrol tombol kategori bertipe tab horizontal (_Umum_, _Pemesanan_, _Pembayaran_, _Hasil Foto_).
- **Enkapsulasi Reaktif Alpine.js**:
    - State `activeCategory` mengontrol visibilitas baris pertanyaan yang muncul pada layar.
    - Setiap baris pertanyaan dikemas menggunakan komponen akordion interaktif dengan transisi halus (`x-collapse`) untuk membuka dan menutup teks jawaban ketika area judul pertanyaan diklik oleh pengguna.

---

## 4. Komponen Antarmuka Halaman Kontak Kami (`views/frontdoor/contact/`)

Gerbang interaksi langsung bagi pelanggan anonim maupun klien terdaftar untuk mengirimkan pesan aduan, jalinan kerja sama, ataupun pertanyaan kustom di luar modul FAQ.

### A. Rincian Komponen Dua Kolom (`Contact Us.png`)

- **Sektor Kiri (Formulir Kontak Klien)**: Elemen pengumpulan data input mandiri yang terdiri atas field _Nama Lengkap_, _Alamat Email_, _Subjek_, dan kotak teks _Pesan Anda_, ditutup dengan tombol kirim bertuliskan `Kirim Pesan`.
- **Sektor Kanan (Informasi Kontak Fisik)**: Menampilkan panel informasi terisolasi yang memuat tiga komponen detail utama:
    1. _Lokasi Kami_: Alamat operasional resmi cabang (Jl. Trikora Pertokoan Galuh Marindu No.07, Banjarbaru).
    2. _Hubungi Kami_: Nomor telepon hotline utama (0811 519 9090).
    3. _Email Resmi_: Alamat surat elektronik korporat (hello@binaryphotoworks.id).

---

## 5. Regulasi Penggabungan Kode Sisi Depan (Front-End Compilation Style)

- **Aturan Bebas Inheritance Tradisional**: Seluruh file induk halaman informasional di atas dilarang keras memanggil direktif warisan purba `@extends` atau `@section`. Struktur wajib dibungkus murni menggunakan tag komponen penutup murni:

```html
<x-layouts.frontdoor>
    <livewire:faq-accordion-container />
</x-layouts.frontdoor>
```

- **Fleksibilitas Pemecahan Berkas Lokal**: Untuk komponen sekat lokal yang tidak membutuhkan transfer properti kompleks (seperti bagian sekat beranda), diperbolehkan menggabungkannya ke file `index.blade.php` utama menggunakan perintah direktif **`@include('frontdoor.home.sections.hero')`** demi kepraktisan proses pengerjaan layouting Tailwind CSS.
