# Documentation: Data Analytics & Print System - PDF Reports Specification (Spatie PDF Layer)

## 1. Matriks Regulasi & Peletakan Tombol Cetak (Operational Blueprint)

Sistem pencetakan dokumen fisik wajib didistribusikan secara taktis pada panel kontrol antarmuka berikut:

| No  | Nama Laporan / Dokumen                    | Orientasi Kertas | Penempatan Tombol Aksi Sisi Admin / Klien                |
| :-- | :---------------------------------------- | :--------------- | :------------------------------------------------------- |
| 1   | Laporan Rekapitulasi Pendapatan Transaksi | Portrait         | Backdoor: Menu Sistem Laporan                            |
| 2   | Laporan Rekapitulasi Pemesanan            | Portrait         | Backdoor: Menu Sistem Laporan                            |
| 3   | Laporan Jadwal Operasional Harian         | Portrait         | Backdoor: Tombol Khusus di Halaman Daftar Jadwal (index) |
| 4   | Laporan Rekapitulasi Performa Hari        | Portrait         | Backdoor: Menu Sistem Laporan                            |
| 5   | Laporan Rekapitulasi Ulasan Pelanggan     | Portrait         | Backdoor: Menu Sistem Laporan                            |
| 6   | Laporan Data Klien                        | Portrait         | Backdoor: Menu Sistem Laporan                            |
| 7   | Laporan Data Paket (Katalog Master)       | Landscape        | Backdoor: Menu Sistem Laporan                            |
| 8   | Laporan Rekapitulasi Jadwal Pemotretan    | Portrait         | Backdoor: Menu Sistem Laporan                            |
| 9   | Cetak Bukti Pembayaran (E-Invoice PDF)    | Portrait         | Dashboard Akun Klien & Detail Pemesanan Backdoor         |

---

## 2. Aturan Bisnis, Parameter Filter, & Logika Kueri (Backend Query Rules)

Setiap sirkuit kueri database wajib divalidasi dengan ketat oleh Laravel 13 Controller sebelum dilempar ke mesin Spatie PDF:

### 1. Laporan Rekapitulasi Pendapatan Transaksi

- **Logika Bisnis**: Menghitung murni total aliran uang masuk (_real revenue_) dari transaksi yang sah.
- **Kriteria Filter**: Rentang waktu Tanggal Awal s/d Tanggal Akhir (Wajib).
- **Kondisi Data**: `bookings.status` bernilai `'Lunas'` atau `'Selesai'`.
- **Struktur Kaki Tabel**: Wajib menampilkan akumulasi kalkulasi matematis di bagian paling bawah halaman:

$$
\text{Grand Total Pendapatan} = \sum (\text{bookings.total\_price}) \quad \text{where status} \in \{\text{'Lunas'}, \text{'Selesai'}\}
$$

### 2. Laporan Rekapitulasi Pemesanan

- **Logika Bisnis**: Menampilkan kompilasi sejarah seluruh reservasi masuk tanpa memandang status keuangan.
- **Kriteria Filter**: Rentang waktu Tanggal Reservasi dan Status Booking (Pilihan Dropdown: Semua, Menunggu, Selesai, Batal).

### 3. Laporan Jadwal Operasional Harian

- **Logika Bisnis**: Bertindak sebagai lembar panduan kerja (_job sheet_) kru fotografer dan admin di lokasi studio pada hari berjalan.
- **Kriteria Filter**: Tanggal Tunggal Spesifik (_Single Date Filter_).
- **Aturan Pengurutan**: Wajib diurutkan dari jam kerja paling pagi secara menaik: `ORDER BY start_time ASC`.

### 4. Laporan Rekapitulasi Performa Hari

- **Logika Bisnis**: Mengukur analisis tingkat kepadatan operasional untuk mengetahui hari apa yang mendatangkan volume reservasi tertinggi.
- **Kriteria Filter**: Rentang waktu Tanggal Awal s/d Tanggal Akhir (Wajib).

### 5. Laporan Rekapitulasi Ulasan Pelanggan

- **Logika Bisnis**: Bahan evaluasi internal tim untuk memantau nilai kepuasan pelanggan terhadap kualitas potret studio.
- **Kriteria Filter**: Rentang waktu Tanggal Awal s/d Tanggal Akhir (Wajib).

### 6. Laporan Data Klien

- **Logika Bisnis**: Menghimpun daftar identitas pelanggan baru yang mendaftarkan akun di website studio.
- **Kriteria Filter**: Rentang waktu pendaftaran berbasis kolom waktu `created_at` milik user.

### 7. Laporan Data Paket (Katalog Master)

- **Logika Bisnis**: Menyajikan data master katalog harga, varian paket, dan durasi studio aktif.
- **Kondisi Data**: Mengunci baris data dengan parameter `is_active = true`.
- **Format Lembar kertas**: Berbentuk melebar (**Landscape**) karena kolom varian data yang masif.

### 8. Laporan Rekapitulasi Jadwal Pemotretan

- **Logika Bisnis**: Merangkum kepadatan pemesanan studio dalam rentang waktu jangka panjang (mingguan atau bulanan) untuk manajemen stok background studio.
- **Kriteria Filter**: Rentang waktu Tanggal Awal s/d Tanggal Akhir (Wajib).

### 9. Cetak Bukti Pembayaran (E-Invoice PDF)

- **Logika Bisnis**: Dokumen nota struk resmi transaksi personal klien yang memuat rincian paket, background, addons terperinci, nominal DP 60%, dan sisa pelunasan 40%.
- **Aksesibilitas**: Dapat diunduh secara mandiri oleh klien lewat dasbor, tautan WhatsApp Fonnte, maupun kasir backdoor.

---

## 3. Implementasi Pemanggilan Cetak (Laravel 13 Attribute Syntax)

Sesuai hukum laboratorium, otorisasi penembakan dokumen cetak PDF dilindungi murni menggunakan deklarasi atribut PHP 8 di tingkat atas pengontrol:

```php
namespace App\Http\Controllers\Backdoor;

use Illuminate\Routing\Controllers\Controller;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Spatie\LaravelPdf\Facades\Pdf;

#[Middleware('auth')]
#[Middleware('role:superadmin|owner|admin')] // Batasan akses pencetakan internal
class PdfReportController extends Controller
{
    public function streamPendapatanReport($startDate, $endDate)
    {
        // Logika query data pendapatan terfilter tetap suci dan tidak berubah

        return Pdf::view('backdoor.reports.pdf.pendapatan', compact('data'))
            ->paperSize('a4')
            ->portrait()
            ->download('Laporan-Pendapatan-' . $startDate . '.pdf');
    }
}
```

---

## 4. Aturan Desain & Pembatasan HTML (Anti-Inline Script)

- **Hukum Anti-Inline JS pada Dokumen Cetak**: Seluruh berkas Blade template untuk laporan PDF dilarang keras memuat script JavaScript interaktif (Bebas total dari tag `<script>`). Kompilasi murni hanya menggunakan HTML5 dan CSS standar Tailwind/Bootstrap utility untuk tata letak tabel.
- **Standardisasi Kop Surat Resmi Studio**: Setiap lembar laporan (nomor 1 sampai 8) wajib menyertakan komponen kepala surat (_Header Logo_) resmi **Binary Photoworks** beserta informasi alamat fisik, nomor WhatsApp Fonnte, dan pembatas garis horizontal padat di bagian atas.
