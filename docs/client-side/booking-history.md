# Documentation: Client-Side - Booking History Functional Specification

## 1. Komponen Antarmuka Dasbor Klien (UI Layout Mapping)

Halaman riwayat transaksi ini membagi ruang kerja menjadi tata letak dua kolom utama menggunakan pembungkus tata letak induk `<x-layouts.frontdoor>`.

### A. Komponen Sidebar Navigasi Kontrol (`views/frontdoor/dashboard/index.blade.php`)

- **Komponen Tampilan (`Dashboard User - Jadwal Saya Active.png`)**:
    - **Identitas Klien**: Menampilkan nama lengkap akun (Contoh: `Robby Setiawan`) dan alamat email terdaftar di bagian paling atas.
    - **Menu Navigasi**: Menu daftar tautan vertikal berupa `Jadwal Saya`, `Profil Saya`, dan opsi `Keluar`.
    - **Status Aktif**: Sektor menu yang dipilih mendapatkan visual penanda latar belakang abu-abu (_active state background shading_).

### B. Komponen Panel Riwayat Pemesanan ("Jadwal Saya")

- **Komponen Visual (`Dashboard User - Jadwal Saya Active.png`)**:
    - **Header Modul**: Menampilkan teks judul `Jadwal Sesi Foto Anda`.
    - **Mekanisme Tabulasi**: Sepasang tombol filter dinamis kontrol Alpine.js yang membagi klasifikasi pesanan menjadi dua tipe:
        1. `Akan Datang` (Aktif secara bawaan).
        2. `Selesai`.
    - **Daftar Kartu Transaksi**: Komponen daftar berbentuk baris horizontal yang menampung informasi berikut:
        - _Kolom Waktu (Sektor Kiri)_: Menampilkan Hari, Tanggal, dan Rentang Jam Pemotretan lengkap dengan keterangan zona waktu regional `WITA` (Contoh: `Senin, 25 Mei 2026 | 10:00 - 10:30 WITA`).
        - _Kolom Rincian (Sektor Kanan)_: Menampilkan nama sub-varian paket yang dipesan (Contoh: `Studio Wisuda Paket 1`), durasi pengerjaan, status pembayaran, dan penamaan warna latar belakang latar studio yang dipilih (Contoh: `Sesi 1 Jam`, `DP Terbayar`, `Background Putih`).

### C. Komponen Informasi Kebijakan (Footer / Bawah Daftar)

- **Teks Booking Policy**: Di bagian paling bawah daftar jadwal, sistem menampilkan teks bantuan/kebijakan (Contoh: "Ingin mengubah pesanan, batal, atau ada kendala teknis?").
- **Tautan Aksi Bantuan**: Teks tersebut diikuti oleh tautan teks **"Hubungi Kami"** yang jika di-klik akan langsung mengarahkan Klien ke obrolan WhatsApp Admin Studio. Ini berfungsi sebagai jalur eskalasi (*escape hatch*) untuk kondisi-kondisi di luar batasan sistem (seperti ingin batal mendadak atau ingin menambah paket/orang).

---

## 2. Aturan Bisnis & Logika Pemetaan Status (State Filtering Rules)

### A. Algoritma Filter Status Tabulasi

Sistem memisahkan baris data berdasarkan nilai kolom `status` pada tabel database `bookings` menggunakan filter logika logika biner sebagai berikut:

- **Tab `Akan Datang` (Upcoming)**: Menampilkan seluruh data booking yang memiliki kecocokan kriteria status:

    $$
    \text{Status} \in \{\text{'Menunggu'}, \text{'DP Terbayar'}, \text{'Lunas'}\}
    $$

- **Tab `Selesai` (Past/History)**: Menampilkan seluruh data booking yang memiliki kecocokan kriteria status:

    $$
    \text{Status} \in \{\text{'Selesai'}, \text{'Batal'}\}
    $$

### B. Hierarki Aksi & Detail Janji Temu

- **Aksi Level Kartu (List View)**: Seluruh area kartu merupakan area klik (*clickable card*) untuk membuka rincian atau detail paket. Untuk menjaga antarmuka tetap bersih, **hanya ada 2 tombol aksi** di level ini, yaitu tombol **"Detail"**, serta **"Bayar Sekarang"** yang eksklusif muncul jika status pemesanan adalah `Menunggu` atau belum melakukan pembayaran.
- **Rincian / Detail**: Mengklik kartu akan mengubah status `showDetailModal` menjadi `true`. Sistem akan menarik rincian DTO secara mendalam dari contoh: `/api/client/appointments/{booking_code}`.
- **Aksi Level Detail (Berdasarkan Status)**:
  - `Menunggu`: Menampilkan tombol **Batalkan Reservasi**, **Bayar Sekarang**, dan **Ubah Jadwal**.
  - `DP Terbayar`: Menampilkan tombol **Unduh Kuitansi**, **Ubah Jadwal**, dan **Hubungi Admin**.
  - `Lunas`: Menampilkan tombol **Unduh Kuitansi** dan **Hubungi Admin**.
  - `Selesai`: Menampilkan tombol **Unduh Hasil Foto** (menuju tautan GDrive).
  - `Batal`: Tidak menampilkan aksi apa pun.

### C. Aturan Reschedule (Ubah Jadwal)

Sistem memberlakukan batasan waktu untuk keamanan operasional studio:
- **Batas Maksimal (H-1)**: Fitur "Ubah Jadwal" hanya dapat dilakukan maksimal 24 jam sebelum tanggal dan waktu pemotretan awal. 
  - *Contoh Kasus*: Jika jadwal awal adalah tanggal 5 Mei pukul 10:00 WITA, maka Klien hanya bisa melakukan *reschedule* sebelum tanggal 4 Mei pukul 10:00 WITA. Jika Klien mencoba mengubah pada tanggal 4 Mei siang atau tanggal 5 Mei, tombol "Ubah Jadwal" tidak akan muncul.
- **Ketersediaan Slot**: Jadwal baru yang dipilih harus melewati filter `isSlotOccupied()` sehingga dipastikan tidak bentrok dengan antrean Klien lain.

---

## 3. Batasan Implementasi Sisi Depan (Front-End Constraints - Feature-Based Modules)

- **Hukum Anti-Inline HTML Attribute Script**: Peralihan kelas aktif menu, penukaran filter tab `Akan Datang` vs `Selesai`, dan fungsi pemicu buka modal haram dituliskan langsung di dalam atribut tag HTML mentah elemen view (Bebas penuh dari atribut mentah `onclick="..."`).
- **Enkapsulasi Berkas Bersisian (Co-location Style via @include)**:
    - Struktur tata letak antarmuka data transaksi diletakkan pada berkas induk `index.blade.php`.
    - Seluruh manajemen state reaktif rekayasa filter data riwayat (seperti penentuan data array `appointments: []`, filter string `activeTab: 'upcoming'`, dan fungsi `fetchAppointments()`) wajib diisolasi di dalam file pendukung bernama `script.blade.php`.
    - Berkas skrip lokal ini wajib diintegrasikan ke bagian paling bawah file induk menggunakan perintah:

```html
<!-- views/frontdoor/dashboard/index.blade.php -->
<x-layouts.frontdoor>
    <div x-data="appointmentHistoryHandler">
        <!-- Komponen Tampilan HTML & Tailwind Sesuai Desain -->
    </div>

    @include('frontdoor.dashboard.script')
</x-layouts.frontdoor>
```

- **Pangkalan Komponen Mandiri (Shared Component Modal)**: Komponen pop-up rendering detail invoice dipisah ke dalam berkas internal `views/frontdoor/dashboard/components/appointment-detail-modal.blade.php` untuk menjaga kesederhanaan struktur kode.
