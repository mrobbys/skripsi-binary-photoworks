# Documentation: Admin Operations - Booking Management (Grid.js DataTables) Functional Specification

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

### C. Komponen Struktur DataTables (Sektor Pustaka Grid.js)

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

Ketika operator mengklik tombol aksi tiga titik pada baris data, sistem akan menyediakan dua opsi krusial:

1. **Trigger Modal Pelunasan Manual**: Hanya aktif jika status baris data bernilai `DP Terbayar`. Berfungsi mencatat pelunasan uang sisa (40% sisa pembayaran) secara tunai/QRIS di kasir studio foto, mengubah status booking menjadi `Lunas`.
2. **Trigger Input Tautan Aset (`gdrive_link`)**: Menyediakan form pop-up untuk menempelkan URL folder Google Drive hasil edit foto sesi klien. Penyimpanan data ini otomatis memicu _Fonnte Service Layer_ mengirim notifikasi WhatsApp berisi link unduh ke nomor HP pelanggan.

---

## 3. Batasan Pembatasan Sisi Depan (Front-End Constraints - SFC Style)

- **Hukum Anti-Inline HTML Attribute Script**: Seluruh interaksi pembukaan dropdown aksi, pengetikan _live search_, dan pemanggilan modal input dilarang keras ditulis langsung di dalam atribut bawaan tag HTML (Bebas penuh dari instruksi mentah `onclick="..."`).
- **Enkapsulasi Berkas Bersisian (Co-location Style via @include)**:
    - Struktur layout utama tabel, search box, dan kartu statistik diletakkan pada berkas views utama bernama `index.blade.php`.
    - Seluruh konfigurasi pustaka Grid.js, setup request AJAX Axios pencarian, dan handler modifikasi status data diisolasi penuh di dalam berkas pendukung bernama `script.blade.php`.
    - Proses penyatuan berkas dikunci secara mutlak menggunakan direktif `@include` lokal pada bagian paling bawah file:

```html
<x-layouts.backdoor>
    <div x-data="bookingDataTableHandler"></div>

    @include('backdoor.manajemen-pemesanan.script')
</x-layouts.backdoor>
```

- **Optimasi Detak Payload (Debounce)**: Fitur input pencarian kata kunci pada _Live Search Box_ wajib menggunakan pengawal fungsi Debounce minimal sebesar `400ms` sebelum mengirimkan request query ke database PostgreSQL guna mencegah overload load server.
