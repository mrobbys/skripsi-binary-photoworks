# Documentation: Admin Operations - Session Schedule (Calendar & List View) Functional Specification

## 1. Komponen Antarmuka Manajemen Jadwal Sesi (UI Layout Mapping)

Modul operasional pemotretan ini berada di bawah kendali cangkang induk `<x-layouts.backdoor>` dan terbagi menjadi dua sub-halaman utama pada menu sidebar _JADWAL SESI_.

### A. Tampilan Sub-Menu 1: Kalender Sesi (`views/backdoor/jadwal-sesi/calendar.blade.php`)

- **Komponen Tampilan (`Kalender.png`)**:
    - **Bilah Navigasi Atas**: Tombol kendali rentang waktu (`<`, `>`, `Hari Ini`), teks penanda bulan berjalan (`Mei 2026`), dan sakelar opsi tampilan tipe grid (`Bulan`, `Minggu`, `Hari`).
    - **Grid Inti Kalender (FullCalendar.js Integration)**: Menampilkan kotak-kotak tanggal dalam satu bulan penuh (Senin hingga Minggu). Tanggal di luar bulan aktif (Contoh: April 27-30) akan dirender redup (_faded state_).
    - **Komponen Event Bar**: Kartu bar padat berwarna abu-abu gelap di dalam tanggal terisi yang memuat jam mulai, nama paket, dan nama klien (Contoh: `10:00 - Wisuda Pkt 1 (Robby S.)`).
    - **Legenda Filter (Footer Legend)**: Penanda status indikator warna visual di bagian bawah grid:
        1. `■ SESI DIKONFIRMASI` (Abu-abu gelap): Menandakan slot waktu yang sudah terkunci dan terbayar.
        2. `□ TERSEDIA` (Putih bersih): Menandakan slot jam kerja operasional studio yang masih kosong.
        3. `■ HARI LIBUR` (Merah muda/Pink pudar): Menandakan hari libur studio di mana pendaftaran slot dikunci (Contoh: Setiap hari Kamis pada gambar).

### B. Tampilan Sub-Menu 2: Daftar Jadwal (`views/backdoor/jadwal-sesi/list.blade.php`)

- **Komponen Tampilan (`Daftar Jadwal.png`)**:
    - **Kartu Indikator Statistik (Top Row Widgets)**: Menampilkan 3 metrik pemantauan volume kerja harian:
        1. _Total Sesi Foto Hari Ini_: (Contoh: `12 Sesi`).
        2. _Total Sesi Selesai Hari Ini_: (Contoh: `3 Sesi`).
        3. _Total Jadwal Mendatang_: Akumulasi antrean _booking_ di hari-hari esok (Contoh: `34 Sesi`).
    - **Bilah Aksi & Kontrol**: Input pencarian (_Live Search Box_) ber-placeholder `Cari jadwal berdasarkan nama klien...` di sisi kiri, dan tombol aksi `Cetak Jadwal Hari Ini` di sisi kanan.
    - **Struktur Tabel Detail Pemotretan**: Menggunakan tabel data yang memetakan kolom: _No._, _Waktu Sesi_ (Tanggal + Jam), _Nama Klien_, _Paket Foto_, _Background_, _Status Sesi_ (Badge biru: `ONGOING`), dan tombol aksi tiga titik vertikal.

---

## 2. Aturan Bisnis & Algoritma Pengumpulan Data (Data Aggregation Rules)

### A. Formula Kalkulasi Metrik Widget Dashboard Jadwal

Sistem memproses penghitungan otomatis untuk tiga widget di atas dari tabel `bookings` berdasarkan waktu waktu riil server (`Carbon::now()`), dengan rumusan matematis sebagai berikut:

$$
\text{Sesi Foto Hari Ini} = \sum (\text{bookings}) \quad \text{where } \text{booking\_date} = \text{CURRENT\_DATE} \land \text{status} \neq \text{'Batal'}
$$

$$
\text{Sesi Selesai Hari Ini} = \sum (\text{bookings}) \quad \text{where } \text{booking\_date} = \text{CURRENT\_DATE} \land \text{status} = \text{'Selesai'}
$$

$$
\text{Jadwal Mendatang} = \sum (\text{bookings}) \quad \text{where } \text{booking\_date} > \text{CURRENT\_DATE} \land \text{status} \in \{\text{'Menunggu'}, \text{'DP Terbayar'}, \text{'Lunas'}\}
$$

### B. Protokol Render Hari Libur Studio

Sistem kalender secara otomatis akan mewarnai seluruh kotak hari terkait menjadi warna merah muda pudar (_Hari Libur State_) jika pada tabel master `schedules` untuk hari tersebut diset nilai propertinya berupa `is_active = false`.

---

## 3. Batasan Pembatasan Sisi Depan (Front-End Constraints - Feature-Based Modules)

- **Hukum Anti-Inline HTML Attribute Script**: Segala bentuk penanganan aksi klik tombol ganti bulan di kalender, pembukaan pop-up detail event, ketikan _live search_, haram ditulis langsung pada atribut tag HTML (Steril penuh dari atribut mentah `onclick="..."`).
- **Enkapsulasi Berkas Bersisian (Co-location Style via @include)**:
    - Struktur elemen visual HTML diletakkan pada berkas views induk masing-masing (`calendar.blade.php` atau `list.blade.php`).
    - Seluruh logika inisialisasi pustaka `FullCalendar.js`, manipulasi data array, serta request AJAX Axios diisolasi penuh ke dalam berkas pendukung bersisian bernama `calendar-script.blade.php` dan `list-script.blade.php`.
    - Proses penyatuan file dikunci menggunakan perintah direktif `@include` lokal pada bagian baris paling bawah berkas:

```html
<x-layouts.backdoor>
    <div x-data="calendarScheduleHandler"></div>

    @include('backdoor.jadwal-sesi.calendar-script')
</x-layouts.backdoor>
```

- **Optimasi Payload (Debounce Control)**: Kotak input pencarian nama klien pada halaman daftar jadwal wajib dikawal oleh fungsi pengaman Debounce sebesar `400ms` sebelum menembakkan kueri asinkronus ke server database guna menjaga kestabilan performa Supabase.
