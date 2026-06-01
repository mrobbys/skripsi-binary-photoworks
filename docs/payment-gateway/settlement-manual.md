# Documentation: Payment Gateway - Admin Backdoor Settlement (Manual & Midtrans POS)

## 1. Alur Interaksi Pelunasan di Meja Kasir (Cashier Desk Workflow)

Ketika pelanggan ingin melunasi sisa tagihan di studio, Admin membuka **Halaman Detail Pemesanan khusus** (`/admin/operations/bookings/{id}`) pada panel backdoor. Di dalam halaman detail ini, terdapat kartu kendali keuangan (_Payment Control Card_) yang menyediakan dua jalur eksekusi pelunasan:

### A. Jalur 1: Pelunasan Digital (Midtrans Backdoor Snap)

1. Admin memilih metode pembayaran `Midtrans Dynamic POS` pada kartu kendali keuangan.
2. Admin menekan tombol `Picu Pop-Up Pembayaran`.
3. Alpine.js mengirimkan request Axios ke backend untuk meminta `snap_token` pelunasan sisa tagihan.
4. Server Laravel menjerat server Midtrans dengan mengirimkan parameter `order_id` baru yang telah dimodifikasi menggunakan akhiran khusus untuk menghindari bentrokan ID.
5. Jendela pop-up Midtrans Snap muncul sebagai _overlay overlay_ langsung di atas Halaman Detail Pemesanan Admin. Klien melakukan scan QRIS atau membayar VA langsung di depan meja kasir.

### B. Jalur 2: Pelunasan Konvensional (Manual Tunai)

- Admin memilih opsi `Tunai (Cash)`, menerima fisik uang fiat dari pelanggan, lalu menekan tombol `Konfirmasi Lunas Penuh`. Sistem secara mandiri langsung menyuntikkan status lunas ke database Supabase tanpa melibatkan gerbang API Midtrans.

---

## 2. Aturan Bisnis & Formulasi Unik ID Transaksi

### A. Aturan Modifikasi `order_id` Midtrans

Untuk mematuhi regulasi Midtrans yang melarang penggunaan ID berulang, sirkuit backend wajib memodifikasi kode booking asli dengan menyuntikkan sufiks khusus pelunasan:

$$
\text{Order ID Pelunasan} = \text{bookings.booking\_code} + \text{"-PLN"}
$$

- **Contoh ID Transaksi DP 60% Awal**: `BPW-WSD01-260530-7AX`
- **Contoh ID Transaksi Pelunasan Kasir 40%**: `BPW-WSD01-260530-7AX-PLN`

### B. Formula Nominal Pelunasan (Gross Amount)

Nominal yang ditagihkan kepada server Midtrans untuk sisa pembayaran offline terkunci mutlak di angka 40% dari total harga keseluruhan:

$$
\text{Gross Amount Pelunasan} = 0.40 \times \text{bookings.total\_price}
$$

---

## 3. Implementasi Skrip Terisolasi Sisi Admin (Feature-Based Modules - Anti-Inline Script)

Sesuai dengan hukum arsitektur utama, inisialisasi objek SDK Midtrans Snap diisolasi murni di dalam berkas pendukung co-location dan dipanggil menggunakan perintah `@include` di bagian bawah halaman detail.

```javascript
// resources/js/features/booking/settlement.js
export function init(Alpine) {
    Alpine.data('adminMidtransSettlementHandler', () => ({
        isProcessing: false,

        async triggerAdminPayment(bookingId) {
            this.isProcessing = true;

            try {
                // 1. Ambil token pelunasan khusus dari server via rute web
                const response = await axios.post(`/admin/operations/bookings/${bookingId}/generate-pelunasan-token`);
                const snapToken = response.data.snap_token;

                // 2. Eksekusi SDK Midtrans Snap di atas layar detail admin
                window.snap.pay(snapToken, {
                    onSuccess: (result) => {
                        alert('Pelunasan berhasil dicatat oleh sistem, Bos!');
                        window.location.reload();
                    },
                    onPending: (result) => {
                        alert('Menunggu penyelesaian pembayaran digital klien.');
                    },
                    fclose: () => {
                        this.isProcessing = false;
                    },
                });
            } catch (error) {
                alert('Gagal mendistribusikan sinyal transaksi ke server Midtrans.');
                this.isProcessing = false;
            }
        },
    }));
}
```
