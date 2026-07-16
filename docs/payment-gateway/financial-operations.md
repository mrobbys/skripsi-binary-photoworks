# Documentation: Payment Gateway - Settlement & Financial Operations

Dokumen ini memuat aturan bisnis terkait pelunasan kasir (Settlement), prosedur pengembalian dana (Refund), dan skenario perubahan paket (Upgrade/Downgrade) pada sistem reservasi.

## 1. Alur Interaksi Pelunasan di Meja Kasir (Cashier Settlement)

Untuk menyederhanakan operasional fisik di studio, pelunasan sisa tagihan (40%) tidak menggunakan Midtrans *Dynamic POS*.

1. Klien mendatangi kasir studio foto untuk melunasi sisa tagihan.
2. Klien melakukan pembayaran menggunakan metode konvensional (Cash, QRIS Statis, atau Transfer Bank).
3. Admin membuka Halaman Detail Pemesanan pada panel backdoor.
4. Admin menekan tombol **"Tandai Lunas Penuh"**.
5. Sistem mengubah status transaksi dari `DP Terbayar` menjadi `Lunas` (Fully Paid) di database secara langsung tanpa melibatkan *API Midtrans*.
6. Sistem memicu *PaymentReceiptController* untuk menghasilkan kuitansi PDF versi lunas yang dapat diunduh klien.

---

## 2. Panduan Operasional Refund (Pembatalan)

### A. Kanal Pembayaran Midtrans (Payment Channels)
Sistem Midtrans dikonfigurasi secara mutlak untuk hanya menampilkan metode pembayaran yang mendukung *Direct Refund* via API (seperti GoPay, ShopeePay, QRIS, dsb).

### B. Alur Refund Otomatis via Dashboard Midtrans
1. Klien menghubungi Admin via WhatsApp untuk mengajukan *refund* (pembatalan jadwal).
2. Admin mengakses dasbor (portal) Midtrans secara mandiri dan menekan tombol *Refund* pada transaksi terkait.
3. Server Midtrans menembakkan _API Webhook_ kembali ke aplikasi.
4. Aplikasi menangkap *webhook* tersebut dan otomatis mengubah status `Booking` menjadi `Cancelled` serta status `Payment` menjadi `Refunded/Cancelled`.

### C. Alur Refund Darurat (Manual)
Jika metode pembayaran klien tidak mendukung *API Refund* dari Midtrans:
1. Admin meminta nomor rekening klien.
2. Admin mentransfer pengembalian dana secara manual via bank.
3. Admin menekan tombol **"Batalkan Booking"** dari Halaman Manajemen Pemesanan untuk mengubah status menjadi batal.

---

## 3. Prosedur "Void & Recreate" (Upgrade / Downgrade Paket)

Untuk menjaga integritas rekam jejak finansial (Audit Trail) dan mencegah bentrok ketersediaan jadwal, sistem **melarang keras modifikasi/edit paket secara langsung pada pesanan yang sudah berjalan**.

1. **Konsep Void & Recreate**: Setiap perubahan paket yang melibatkan perbedaan durasi kalender dan harga harus diselesaikan dengan membatalkan pesanan lama, dan membuat pesanan baru.
2. Admin menekan tombol **Batalkan Booking** (pesanan lama mati).
3. Admin masuk ke halaman **Tambah Booking Manual** dan membuatkan pesanan baru dengan durasi paket yang baru.
4. Kode Booking (Booking Code) akan otomatis berganti ke kode yang baru (menandakan *invoice* baru).
5. **Penyelesaian Selisih Dana:** Proses pengembalian dana sisa (*downgrade*) atau penagihan dana kurang (*upgrade*) diselesaikan 100% menggunakan transfer manual / pembayaran di tempat, tanpa campur tangan Midtrans untuk menghindari komplikasi _API Refund_.

---

## 4. Prosedur "Upsell" Layanan Tambahan (Add-ons)

Berbeda dengan merubah paket utama, menambah layanan tambahan (seperti Cetak Foto atau Tambah Orang) di hari pelaksanaan sesi foto **TIDAK PERLU** membatalkan pesanan.

1. Layanan tambahan tidak merubah slot waktu kalender (durasi sesi tetap sama).
2. Admin menggunakan form **"+ Tambah Layanan Tambahan"** di Halaman Detail Pemesanan.
3. Sistem hanya menjumlahkan harga total dan tagihan akhir secara akumulatif.
4. Selisih harga ditagihkan langsung di kasir sebelum Admin menekan tombol "Tandai Lunas Penuh".

---

## 5. Aturan Pencatatan Ledger (Tabel Payments)

Tabel `payments` berfungsi sebagai *ledger* (buku besar) universal, bukan sekadar riwayat Midtrans. Untuk memastikan keakuratan Kartu Statistik Pendapatan dan perhitungan Sisa Tagihan pada Kuitansi PDF, *Controller* wajib mematuhi aturan berikut:

1. **Pembuatan Booking Manual (Create Page):** Jika Admin membuat pesanan manual dan mengatur statusnya menjadi `Lunas` (Fully Paid), sistem **WAJIB** melakukan insert 1 baris ke tabel `payments` dengan `amount` sebesar total harga, status `Settlement`, dan tipe `cash` atau `manual_transfer`.
2. **Pelunasan Kasir (Tandai Lunas):** Jika klien sebelumnya membayar DP via Midtrans (sehingga sudah ada 1 baris *payment* DP), dan melunasi sisa 40% di kasir, sistem **WAJIB** melakukan insert baris *payment* ke-2 untuk pesanan tersebut dengan `amount` sebesar 40% sisanya.
3. **Kalkulasi Kuitansi (PaymentReceiptController):** Controller penghasil PDF Kuitansi tidak boleh lagi berasumsi bahwa 1 Booking hanya memiliki 1 Payment. Perhitungan uang muka (amount paid) dan sisa tagihan (remaining) harus menggunakan fungsi agregasi `SUM(amount)` dari seluruh baris `payments` milik *booking* terkait yang berstatus `Settlement`.
