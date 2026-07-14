# Documentation: Admin Operations - Booking Management (Native Alpine DataTables) Functional Specification

## 1. Komponen Antarmuka Manajemen Pemesanan (UI Layout Mapping)

Halaman operasional administrasi ini menggunakan kerangka dasar komponen kustom `<x-layouts.backdoor>` dengan fokus utama pada pemrosesan data transaksi massal secara asinkronus.

### A. Komponen Kartu Ringkasan Finansial (Top Widgets)

- **Komponen Tampilan (`Manajemen Pemesanan.png`)**: Menampilkan 3 kartu indikator ringkasan akumulasi performa kasir:
    1. **Total Transaksi Sukses**: Akumulasi total dana riil masuk dari pesanan aktif (Contoh: `Rp 12.000.000`).
    2. **Status Lunas Penuh**: Jumlah kuantitas reservasi yang sudah menyelesaikan seluruh kewajiban administrasi (Contoh: `48 Sesi`).
    3. **Status DP Terbayar**: Jumlah kuantitas reservasi aktif yang baru membayar uang muka 60% (Contoh: `10 Sesi`).

### B. Komponen Bilah Kontrol & Aksi Utama

- **Komponen Tampilan (`Manajemen Pemesanan.png`)**:
    - **Live Search Input Box**: Elemen pencarian reaktif dengan placeholder teks `Cari kode booking atau nama klien...`.
    - **Tombol Tambah Data**: Tombol `+ Tambah Booking` di sisi kanan untuk memicu pembukaan form pembuatan reservasi manual oleh staff/admin via admin panel.

### C. Komponen Struktur DataTables (Sektor Native Alpine)

- **Komponen Tampilan (`Manajemen Pemesanan.png`)**: Menyajikan struktur tabel dinamis dengan pemetaan kolom sebagai berikut:
    1. **No.**: Penomoran indeks baris data yang berurutan.
    2. **Kode Booking**: String unik kode reservasi (Contoh: `BPN-26MAY01`).
    3. **Nama Klien**: Nama lengkap dari pemilik janji temu sesi foto.
    4. **Jadwal Sesi**: Tanggal pelaksanaan sesi foto (Contoh: `26 Mei 2026`).
    5. **Paket & Varian**: Nama paket beserta sub-varian yang dipilih (Contoh: `Studio Wisuda - Paket 1`).
    6. **Total Bayar**: Nominal transaksi beserta keterangan skema bayar di bawahnya (Contoh: `Rp 500.000 (Lunas)` atau `Rp 500.000 (DP: Rp 300.000)`).
    7. **Status**: Badge penanda warna reaktif. Hijau untuk `LUNAS` dan biru solid untuk `DP TERBAYAR`.
    8. **Aksi**: Tombol dropdown tiga titik vertikal (`icon: remixicon`) untuk memicu menu pop-up tindakan manipulasi data (Pelunasan Kasir, Pengisian Tautan GDrive, atau Pembatalan).

---

## 2. Aturan Bisnis & Logika Kalkulasi Finansial (Data Aggregation Rules)

### A. Formula Akumulasi Kartu Indikator Finansial

Total Transaksi Sukses dihitung dari penjumlahan nilai kolom `amount` pada tabel `payments` dengan filter kriteria status pembayaran berhasil (`status = 'Settlement'`), dirumuskan secara matematis sebagai berikut:

$$
\text{Total Transaksi Sukses} = \sum (\text{payments.amount}) \quad \text{where status} = \text{'Settlement'}
$$

Kuantitas pada kartu status dihitung menggunakan agregasi hitung baris (_count rows_) dari tabel `bookings` berdasarkan kriteria status transaksi:

$$
\text{Status Lunas Penuh} = \sum (\text{bookings}) \quad \text{where status} = \text{'Lunas'}
$$

$$
\text{Status DP Terbayar} = \sum (\text{bookings}) \quad \text{where status} = \text{'DP Terbayar'}
$$

### B. Sirkuit Menu Aksi Dropdown Kontrol Admin

Ketika operator mengklik tombol aksi tiga titik pada baris data tabel, sistem akan menyediakan empat opsi krusial yang menavigasi atau merubah status data:

1. **Lihat Detail**: Mengarahkan admin ke halaman khusus Detail Booking untuk melihat rincian pemesan, riwayat pembayaran, serta form penambahan *addons*.
2. **Tandai Lunas Penuh (Manual Kasir)**: Hanya muncul jika status baris data bernilai `DP Terbayar`. Berfungsi mencatat pelunasan uang sisa (40%) secara tunai/QRIS statis di kasir studio foto, mengubah status booking menjadi `Lunas` (Fully Paid) tanpa melalui Midtrans.
3. **Input Tautan Aset (`gdrive_link`)**: Hanya muncul jika status baris data bernilai `Lunas`. Menyediakan form *pop-up* untuk menempelkan URL folder Google Drive hasil edit foto. Penyimpanan data ini otomatis memicu *Fonnte Service Layer* mengirim notifikasi WhatsApp berisi link unduh ke nomor HP pelanggan.
4. **Batalkan Booking**: Membatalkan pesanan. Digunakan jika klien tidak datang, atau sebagai bagian dari metode "Void & Recreate" saat klien ingin merubah/ *downgrade* paket. Merubah status menjadi `Cancelled`.

---

## 3. Batasan Pembatasan Sisi Depan (Front-End Constraints - Feature-Based Modules)

- **Hukum Anti-Inline HTML Attribute Script**: Seluruh interaksi pembukaan dropdown aksi, pengetikan _live search_, dan pemanggilan modal input dilarang keras ditulis langsung di dalam atribut bawaan tag HTML (Bebas penuh dari instruksi mentah `onclick="..."`).
- **Enkapsulasi Berkas Bersisian (Co-location Style via @include)**:
    - Struktur layout utama tabel, search box, dan kartu statistik diletakkan pada berkas views utama bernama `index.blade.php`.
    - Seluruh konfigurasi pustaka tabel Native Alpine, setup request AJAX Axios pencarian, dan handler modifikasi status data diisolasi penuh di dalam komponen JS (misal `useDatatable.js`).
    - Proses penyatuan berkas dikunci secara mutlak menggunakan direktif `@include` lokal pada bagian paling bawah file:

```html
<x-layouts.backdoor>
    <div x-data="bookingDataTableHandler"></div>

    @include('backdoor.manajemen-pemesanan.script')
</x-layouts.backdoor>
```

- **Optimasi Detak Payload (Debounce)**: Fitur input pencarian kata kunci pada _Live Search Box_ wajib menggunakan pengawal fungsi Debounce minimal sebesar `400ms` sebelum mengirimkan request query ke database PostgreSQL guna mencegah overload load server.

---

---

## 4. Spesifikasi Halaman Tambah Booking Manual (Create Page)

Halaman form pembuatan reservasi manual oleh admin dirancang dengan susunan form reaktif sebagai berikut:
1. **Dropdown Klien:** Pemilihan user yang sudah terdaftar menggunakan pustaka `Choices.js`.
2. **Paket & Varian:** Dropdown bertingkat menggunakan `Choices.js` (Pilih paket -> memicu data varian).
3. **Pilihan Background:** Dropdown menggunakan `Choices.js`.
4. **Kalender Sesi (Booking Date):** Pemilihan tanggal menggunakan antarmuka kalender `Flatpickr` (konfigurasi dasar).
5. **Time Slot (Jadwal Waktu):** Menggunakan elemen Dropdown `Choices.js` (bukan tombol grid seperti frontdoor) untuk menghemat ruang vertikal. Opsi waktu dirender berdasarkan durasi varian terpilih.
6. **Total Harga:** Atribut `readonly`, dikalkulasi secara reaktif oleh Alpine.js.
7. **Status Awal Booking:** Dropdown pilihan status saat dibuat menggunakan `Choices.js` (contoh: `Pending` atau `Lunas`).
8. **Input Add-ons (Dynamic Repeater):** Diimplementasikan menggunakan pendekatan *Form Repeater* (Alpine.js). Admin dapat menekan tombol `[+ Tambah Layanan Tambahan]` untuk memunculkan baris baru berisi *Dropdown* pilihan Add-on (wajib menggunakan `Choices.js`) dan *Input* kuantitas angka. *Field* kuantitas akan otomatis terkunci pada angka `1` jika data Add-on di- *database* memiliki atribut `has_quantity = false`.
