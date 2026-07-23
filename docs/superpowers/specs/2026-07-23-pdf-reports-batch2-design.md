# Design Spec — PDF Reports Batch 2

**Tanggal:** 2026-07-23
**Branch:** feat/admin-reports
**Status:** Approved → Ready to implement

---

## Konteks

Sistem sudah memiliki 4 PDF report yang berjalan:
1. `rekapitulasi-pendapatan-transaksi` — `RekapitulasiPendapatanTransaksiController`
2. `rekapitulasi-pemesanan` — `RekapitulasiPemesananController`
3. `jadwal-operasional-harian` (cetak hari ini) — `JadwalOperasionalHarianController`
4. `payment-receipt` — `PaymentReceiptController`

Batch 2 ini menambah **5 controller baru** dan **5 view baru** di halaman Reports Index backdoor.

---

## Arsitektur Umum

- **Controller**: `app/Domains/PdfReports/Http/Controllers/{NamaController}.php`
- **View**: `resources/views/pdfs/{nama-laporan}.blade.php`
- **Request**: `DateRangeReportRequest` untuk filter rentang; inline untuk single date
- **PDF options**: `->format('a4')->portrait()->margins(10, 10, 10, 10)` kecuali Data Paket (`landscape`)
- **Formatter::rupiah()** untuk format harga
- **Formatter::dateId()** untuk format tanggal Indonesia
- **Formatter::timeRange()** untuk format jam start-end (sudah return "HH:ii - HH:ii WITA")

---

## Report 1: Laporan Jadwal Operasional Harian (Filter Tanggal)

| Properti | Nilai |
|---|---|
| Controller | `JadwalOperasionalHarianFilterController` |
| View | `pdfs.jadwal-operasional-harian-filter` |
| Route name | `backdoor.reports.jadwal-harian-filter.pdf` |
| Route URL | `GET /backdoor/reports/jadwal-operasional-harian` |
| Filter | Single date: `tanggal` (date, required, bebas termasuk masa depan) |
| Request | Inline di controller |
| PDF | Portrait, margin 10, layout khusus (tanpa kop-surat resmi) |

**Query**: `booking_date = $tanggal`, status IN `[DP_PAID, SUCCESS, DONE]`, ORDER BY `start_time ASC`

**Kolom tabel**: `NO | WAKTU SESI | NAMA KLIEN | PAKET & BACKGROUND | KETERANGAN`

- PAKET & BACKGROUND: `{pkg->name} - {variant->name}` (bold), `{background->name ?? '-'}` (text-xs di bawah)
- KETERANGAN: `$booking->notes ?? ''`
- Layout: sama dengan jadwal-operasional-harian.blade.php (header logo kiri + judul kanan, footer timestamp)

**Nama PDF**: `jadwal-harian-{$tanggal->format('Y-m-d')}.pdf`

---

## Report 2: Laporan Rekapitulasi Performa Hari

| Properti | Nilai |
|---|---|
| Controller | `RekapitulasiPerformaHariController` |
| View | `pdfs.rekapitulasi-performa-hari` |
| Route name | `backdoor.reports.performa-hari.pdf` |
| Route URL | `GET /backdoor/reports/rekapitulasi-performa-hari` |
| Filter | Date range: `DateRangeReportRequest` |
| PDF | Portrait, kop surat + signature |

**Query**: `booking_date BETWEEN $start AND $end`, status IN `[DP_PAID, SUCCESS, DONE]`, eager load `payments`

**Grouping**: Bookings di-group per nama hari Indonesia (isoFormat dddd). Tampil 7 baris tetap Senin–Minggu.

- Total Sesi: `count()` booking per hari
- Total Pendapatan: SUM `payments->where('status', 'settlement')->sum('amount')` per hari
- Baris Total di footer tabel: total semua sesi + total semua pendapatan

**Kolom tabel**: `NO | HARI | TOTAL SESI FOTO | TOTAL PENDAPATAN`

**Nama PDF**: `laporan-rekapitulasi-performa-hari-{start}-sd-{end}.pdf`

---

## Report 3: Laporan Rekapitulasi Ulasan Pelanggan

| Properti | Nilai |
|---|---|
| Controller | `RekapitulasiUlasanPelangganController` |
| View | `pdfs.rekapitulasi-ulasan-pelanggan` |
| Route name | `backdoor.reports.ulasan-pelanggan.pdf` |
| Route URL | `GET /backdoor/reports/rekapitulasi-ulasan-pelanggan` |
| Filter | Date range berdasarkan `reviews.created_at` |
| PDF | Portrait, kop surat + signature |

**Query**: `Review::with(['user:id,name'])->whereBetween('created_at', [$start, $end])->orderBy('created_at', 'asc')`

**Kolom tabel**: `NO | TANGGAL | NAMA KLIEN | RATING | KOMENTAR`

- TANGGAL: `Formatter::dateId($review->created_at, 'd-m-Y')`
- RATING: `"{$review->rating}/5"` (skala 1–5)
- NAMA KLIEN: `$review->user?->name ?? '-'`

**Ringkasan footer** (di luar tabel, align-right):
- `Total Ulasan: {count}`
- `Rata-rata Rating Periode Ini: {avg}/5.0` (1 desimal)

**Nama PDF**: `laporan-rekapitulasi-ulasan-pelanggan-{start}-sd-{end}.pdf`

---

## Report 4: Laporan Data Klien

| Properti | Nilai |
|---|---|
| Controller | `DataKlienController` |
| View | `pdfs.data-klien` |
| Route name | `backdoor.reports.data-klien.pdf` |
| Route URL | `GET /backdoor/reports/data-klien` |
| Filter | Date range berdasarkan `users.created_at` |
| PDF | Portrait, kop surat + signature |

**Query**: `User::role(RoleType::USER->value)->withCount('bookings')->whereBetween('created_at', [$start, $end])->orderBy('created_at', 'asc')`

- Total booking menghitung semua booking tanpa filter status (representasi aktivitas klien)
- phone nullable → tampilkan '-' jika null

**Kolom tabel**: `NO | NAMA KLIEN | NO WHATSAPP | EMAIL | TOTAL BOOKING | TANGGAL BERGABUNG`

- TANGGAL BERGABUNG: `Formatter::dateId($user->created_at)` → format panjang Indonesia (1 Juni 2026)

**Footer**: `Total Klien Periode Ini: {totalKlien}`

**Nama PDF**: `laporan-data-klien-{start}-sd-{end}.pdf`

---

## Report 5: Laporan Data Paket (Katalog Master)

| Properti | Nilai |
|---|---|
| Controller | `DataPaketController` |
| View | `pdfs.data-paket` |
| Route name | `backdoor.reports.data-paket.pdf` |
| Route URL | `GET /backdoor/reports/data-paket` |
| Filter | Tidak ada (tampilkan semua aktif) |
| PDF | **Landscape** A4, kop surat + signature |

**Query**: `PackageVariant::with(['package.category'])->where('is_active', true)->whereHas('package', fn($q) => $q->where('is_active', true))->orderBy('package_id')`

- Filter double: `is_active` di tabel `packages` DAN `package_variants`

**Kolom tabel**: `NO | KATEGORI | NAMA PAKET | VARIAN PAKET | HARGA | DURASI (MENIT) | BOOKING VIA | STATUS`

- HARGA: `Formatter::rupiah($variant->price)`
- DURASI: angka integer dari database (thead: "DURASI (MENIT)")
- BOOKING VIA: `$variant->is_whatsapp_only ? 'WhatsApp' : 'Sistem'`
- STATUS: selalu 'Aktif' (sudah difilter)

**Metadata**: Tanpa filter (hanya Cetak + Tanggal Cetak)

**Nama PDF**: `laporan-data-paket-katalog-master.pdf`

---

## Report 6: Laporan Rekapitulasi Jadwal Pemotretan

| Properti | Nilai |
|---|---|
| Controller | `RekapitulasiJadwalPemotretanController` |
| View | `pdfs.rekapitulasi-jadwal-pemotretan` |
| Route name | `backdoor.reports.jadwal-pemotretan.pdf` |
| Route URL | `GET /backdoor/reports/rekapitulasi-jadwal-pemotretan` |
| Filter | Date range berdasarkan `booking_date` |
| PDF | Portrait, kop surat + signature |

**Query**: `Booking::with(['user:id,name','packageVariant.package','background'])->whereBetween('booking_date', [$start, $end])->whereIn('status', [DP_PAID, SUCCESS])->orderBy('booking_date','asc')->orderBy('start_time','asc')`

- Status filter: hanya `DP_PAID` dan `SUCCESS` (bukan PENDING, CANCELLED, DONE)

**Kolom tabel**: `NO | TANGGAL | NAMA | JAM | PAKET & BACKGROUND | KETERANGAN`

- TANGGAL: `Formatter::dateId($booking->booking_date, 'd-m-Y')`
- JAM: `Formatter::timeRange($booking->start_time, $booking->end_time)`
- PAKET & BACKGROUND: `"{$pkg->name} - {$variant->name} / {$bg ?? '-'}"`
- KETERANGAN: `$booking->notes ?? ''`

**Footer**: `Total Jadwal Pemotretan Periode Ini: {total} Sesi`

**Nama PDF**: `laporan-rekapitulasi-jadwal-pemotretan-{start}-sd-{end}.pdf`

---

## Routes Summary

Semua ditambahkan dalam group middleware `auth`, prefix `backdoor/reports`:

```
GET /jadwal-operasional-harian         -> JadwalOperasionalHarianFilterController    -> jadwal-harian-filter.pdf
GET /rekapitulasi-performa-hari        -> RekapitulasiPerformaHariController          -> performa-hari.pdf
GET /rekapitulasi-ulasan-pelanggan     -> RekapitulasiUlasanPelangganController       -> ulasan-pelanggan.pdf
GET /data-klien                        -> DataKlienController                         -> data-klien.pdf
GET /data-paket                        -> DataPaketController                         -> data-paket.pdf
GET /rekapitulasi-jadwal-pemotretan    -> RekapitulasiJadwalPemotretanController      -> jadwal-pemotretan.pdf
```
