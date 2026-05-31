# Documentation: Data Analytics & Print System - Chart Analytics Specification (AJAX Filter Layer)

## 1. Peta Matriks 4 Grafik Analitik Utama (Dashboard Analytics Mapping)

Seluruh grafik analitik di bawah ini diletakkan secara terpusat pada halaman utama _Dashboard Admin_ (Backdoor) dan dikawal oleh pengaman filter tunggal berupa _Dropdown_ Pemilihan Tahun.

### A. Grafik Tren Volume Reservasi (Bar Chart)

- **Tujuan Utama**: Memantau fluktuasi naik-turunnya jumlah total orderan masuk per bulan dalam satu tahun untuk membaca masa _high-season_ studio.
- **Logika Query Backend**:
    - Memfilter data tabel `bookings` berdasarkan kecocokan fungsi penanggalan `YEAR(booking_date)` yang dikirim oleh parameter AJAX dropdown.
    - Mengeksekusi penghitungan total baris `COUNT(id)` dan dikelompokkan menggunakan fungsi `groupBy` bulan (indeks 1 sampai 12).
- **Aturan Edge-Case Sisi Depan**: Jika terdapat bulan kosong yang sama sekali tidak memiliki transaksi (0 booking), backend wajib menyuntikkan angka `0` secara eksplisit ke dalam array bulan tersebut agar urutan sumbu X pada grafik tidak bergeser atau berantakan.

### B. Grafik Tren Pendapatan Per Bulan (Line Chart)

- **Tujuan Utama**: Memantau pertumbuhan grafik "cuan" bulanan dan menganalisis di bulan mana studio menghasilkan profit bersih tertinggi.
- **Logika Query Backend**:
    - Memfilter data tabel `bookings` yang memiliki kepastian finansial, yaitu status transaksi bernilai `'Completed'` atau `'Paid'` (Lunas/Selesai).
    - Melakukan akumulasi matematis menggunakan perintah `SUM(total_price)` dan dikelompokkan via `groupBy` bulan.
- **Aturan Pemformatan Nilai (Y-Axis Formatting)**: Untuk mencegah penumpukan digit angka yang padat pada sumbu Y, JavaScript frontend wajib meringkas angka jutaan menjadi satuan ringkas (Contoh penulisan: Nilai nominal `Rp 15.000.000` wajib dikonversi otomatis menjadi `15jt`).

### C. Grafik Proporsi Paket Terlaris (Donut Chart)

- **Tujuan Utama**: Mengetahui dengan pasti kategori dan paket produk mana yang menjadi tulang punggung (_backbone_) sumber pendapatan bisnis studio.
- **Logika Query Backend**:
    - Melakukan operasi penggabungan tabel (_Multi-level Join Path_): `bookings` $\rightarrow$ `package_variants` $\rightarrow$ `packages` $\rightarrow$ `categories`.
    - Menghitung frekuensi pesanan `COUNT(*)` dan dikelompokkan berdasarkan nama kategori paket `categories.name`.
- **Aturan Visualisasi**: Potongan irisan grafik donat wajib diurutkan dari persentase volume terbesar (Kategori Paling Laris) hingga terkecil, disusun searah jarum jam (_clockwise sorting_).

### D. Grafik Proporsi Layanan Tambahan / Add-ons (Pie Chart)

- **Tujuan Utama**: Mengukur efektivitas strategi penjualan silang (_upselling_) layanan ekstra di luar paket foto utama.
- **Logika Query Backend**:
    - Melakukan operasi relasi data (_Join Path_): tabel pivot `addon_booking` $\rightarrow$ tabel master `addons`.
    - Menghitung agregasi kuantitas total order `COUNT(*)` berdasarkan parameter kunci `addon_id`.
- **Aturan Visualisasi**: Menyajikan label nama item _Add-on_ (seperti ekstra cetak foto, frame, tambahan waktu) beserta nilai persentasenya (`%`) langsung di dalam irisan lingkaran jika kapasitas ruang grafis mencukupi.

---

## 2. Implementasi Jalur API Backend (Laravel 13 Response Controller)

Proses pendistribusian data grafik dilindungi penuh oleh atribut rute modern tanpa konstruktor lama, mengembalikan data murni dalam format enkapsulasi JSON:

```php
namespace App\Http\Controllers\Backdoor;

use Illuminate\Routing\Controllers\Controller;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Middleware('auth')]
#[Middleware('role:superadmin|owner')] // Menutup celah akses data keuangan dari staf luar
class AnalyticsChartController extends Controller
{
    public function getMonthlyRevenueData(Request $request): JsonResponse
    {
        $tahunTerpilih = $request->input('year', date('Y'));

        // Logika kueri matematis SUM() & COUNT() bookings terfilter tahun suci dan tidak berubah

        return response()->json([
            'status' => 'success',
            'year'   => $tahunTerpilih,
            'data'   => $formattedChartArray
        ]);
    }
}
```

---

## 3. Komponen Front-End Terisolasi (SFC Pattern & Anti-Inline Script)

Sesuai dengan hukum mutlak laboratorium **Binary Photoworks**, inisialisasi pustaka grafik (seperti Chart.js atau ApexCharts) dilarang keras ditulis di dalam tag HTML ataupun menggunakan atribut segaris `onchange="..."`.

- **Mekanisme Pemuatan Bersisian (Co-location Style)**:
    - Struktur elemen kanvas grafik `<canvas id="revenueChart"></canvas>` diletakkan rapi di dalam komponen dasbor utama.
    - Elemen select filter tahun diikat menggunakan direktif Alpine.js `x-model="selectedYear"` dan dipasangi fungsi pengamat perubahan `x-effect="fetchChartData()"`.
    - Seluruh logika pemanggilan Axios AJAX dan fungsi render bagan diisolasi murni di dalam file pendukung bersisian bernama `dashboard-chart-script.blade.php`.
    - Penyatuan dikunci menggunakan direktif `@include` lokal di baris terbawah file induk:

```html
<!-- views/backdoor/dashboard/index.blade.php -->
<x-layouts.backdoor>
    <!-- Widget Analytics Grid Layer -->
    <div x-data="adminDashboardChartHandler">
        <select x-model="selectedYear">
            <option value="2026">2026</option>
            <option value="2025">2025</option>
        </select>

        <canvas id="revenueChart"></canvas>
    </div>

    @include('backdoor.dashboard.partials.dashboard-chart-script')
</x-layouts.backdoor>
```
