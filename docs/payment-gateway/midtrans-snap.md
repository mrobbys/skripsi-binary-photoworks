# Documentation: Payment Gateway - Midtrans Snap Integration Functional Specification

## 1. Alur Interaksi & Integrasi Sisi Klien (Client-Side Workflow)

Integrasi Midtrans Snap beroperasi pada langkah pamungkas (_final step_) dari komponen Multi-Step Booking Form di sisi frontdoor pelanggan.

### A. Alur Sekuensial Eksekusi Transaksi

1. **Konfirmasi Pemesanan**: Pelanggan meninjau ringkasan pesanan (Kategori, Varian Paket, Jadwal Sesi, Background, dan Add-ons) lalu memilih tombol opsi `Skema Pembayaran`.
2. **Pengiriman Payload**: Saat tombol `Bayar Sekarang` ditekan, Alpine.js mengirimkan data pesanan via Axios ke endpoint `/api/booking/checkout`.
3. **Penyediaan Snap Token**: Server Laravel memproses reservasi, mendaftarkannya ke database Supabase dengan status awal `Menunggu`, lalu menembakkan API request ke server Midtrans untuk mendapatkan nilai string `snap_token`.
4. **Pemicu Pop-Up Snap**: Nilai `snap_token` dikembalikan ke sisi klien dalam bentuk respon JSON, yang kemudian langsung memicu kemunculan jendela pop-up interaktif Midtrans Snap di atas layar browser klien.

---

## 2. Pemetaan Status & Aturan Bisnis Transaksi (Transaction State & Business Rules)

### A. Formula Kalkulasi Nominal Transaksi (Gross Amount)

Sistem kasir Binary Photoworks membagi jumlah tagihan awal berdasarkan pilihan skema pembayaran (`payment_scheme`) yang dipilih oleh konsumen. Formulasi matematis pengiriman dana ke Midtrans diatur sebagai berikut:

- **Skema Lunas Penuh (`payment_scheme = 'lunas'`)**:
    $$
    \text{Gross Amount} = \text{total\_price}
    $$
- **Skema Uang Muka (`payment_scheme = 'dp'`)**:
    $$
    \text{Gross Amount} = 0.60 \times \text{total\_price}
    $$
    _(Catatan: Nilai DP dikunci sebesar 60% dari total harga pemesanan keseluruhan sesuai regulasi keuangan studio)._

### B. Matriks Penanganan Callback Sisi Depan (Snap JavaScript SDK Options)

Ketika pop-up Midtrans Snap mendeteksi aksi dari pengguna, sistem front-end wajib menangkap status tersebut untuk melakukan pengalihan halaman (_redirect state_) secara presisi:

| Event Callback SDK | Aksi Logika Front-End                                            | Pengalihan Halaman (Redirect Destination)                                                |
| :----------------- | :--------------------------------------------------------------- | :--------------------------------------------------------------------------------------- |
| `onSuccess`        | Memperbarui status tampilan secara instan via AJAX ke server.    | Dialihkan ke `/dashboard/appointments` dengan pop-up SweetAlert2 sukses.                 |
| `onPending`        | Menampilkan instruksi kode bayar / nomor VA (Virtual Account).   | Dialihkan ke halaman invoice ringkasan pembayaran sementara.                             |
| `onError`          | Menangkap kegagalan jaringan atau penolakan transaksi dari bank. | Menampilkan notifikasi galat dan kembali ke halaman detail _checkout_.                   |
| `onClose`          | Menangani kondisi saat pelanggan menutup pop-up tanpa membayar.  | Tetap di halaman _checkout_, mempertahankan data state booking dengan status `Menunggu`. |

---

## 3. Implementasi Skrip Terisolasi (Front-End Constraints - SFC Style)

- **Hukum Anti-Inline HTML Attribute Script**: Seluruh pemanggilan fungsi SDK Midtrans Snap (seperti metode `snap.pay()`) haram ditulis langsung pada atribut tag HTML (Bebas murni dari kode mentah `onclick="snap.pay(...)"`).
- **Protokol Pemuatan Berkas SDK**: Berkas pustaka JavaScript utama milik Midtrans (`snap.js`) dimuat secara luring/daring pada tata letak induk `<x-layouts.frontdoor>`.
- **Enkapsulasi Berkas Bersisian (Co-location Style via @include)**:
    - Elemen visual tombol bayar diletakkan pada berkas views utama bernama `checkout.blade.php`.
    - Seluruh logika penanganan respons data, konfigurasi objek callback Snap (`onSuccess`, `onPending`), dan trigger Axios diisolasi penuh di dalam file pendukung bernama `snap-handler.blade.php`.
    - Proses integrasi disatukan menggunakan perintah direktif `@include` lokal di baris paling bawah:

```html
<!-- views/frontdoor/booking/checkout.blade.php -->
<x-layouts.frontdoor>
    <div x-data="midtransPaymentHandler">
        <!-- Komponen Ringkasan Checkout & Tombol Bayar -->
        <button type="button" x-on:click="triggerCheckoutExecution" x-bind:disabled="isProcessing">
            <span x-show="!isProcessing">Bayar Sekarang</span>
            <span x-show="isProcessing">Mengunci Slot Jadwal...</span>
        </button>
    </div>

    @include('frontdoor.booking.snap-handler')
</x-layouts.frontdoor>
```

---

## 4. Suplemen Lingkungan Pengembangan Lokal (Local Development Setup via ngrok)

Karena server sandbox Midtrans membutuhkan domain publik yang valid untuk mengirimkan data status transaksi (_asynchronous callback_), pengujian di lingkungan `localhost` wajib menggunakan sirkuit pelayan terowongan (tunneling) **ngrok**.

### A. Protokol Inisialisasi Terowongan (Tunneling Command)

1. Jalankan server lokal Laravel Bos Stark seperti biasa (Default port: `8000`):

    ```bash
    php artisan serve
    ```

2. Buka terminal baru, lalu aktifkan ngrok untuk membungkus port terowongan HTTP tersebut:

    ```bash
    ngrok http 8000
    ```

3. Salin tautan publik secure (`https://...ngrok-free.app`) yang dilahirkan oleh ngrok ke dalam berkas konfigurasi lingkungan proyek.

### B. Kalibrasi Berkas Konfigurasi Lingkungan (`.env`)

Sesuaikan baris konfigurasi environment Laravel agar gerbang notifikasi Midtrans mengenali jalur terowongan lokal Anda:

```env
APP_URL=[https://xxxx-xxxx-xxxx.ngrok-free.app](https://xxxx-xxxx-xxxx.ngrok-free.app)
MIDTRANS_APPEND_NOTIF_URL=[https://xxxx-xxxx-xxxx.ngrok-free.app/api/payments/webhook](https://xxxx-xxxx-xxxx.ngrok-free.app/api/payments/webhook)
```

> ⚠️ **Peringatan Batasan Laboratorium**: Setiap kali ngrok diaktifkan ulang, string URL publik akan selalu berubah secara dinamis. Pastikan untuk selalu memperbarui nilai `APP_URL` di `.env` dan menyelaraskan tautan _Notification URL_ di dashboard akun Sandbox Midtrans Anda sebelum memulai uji terbang transaksi.
