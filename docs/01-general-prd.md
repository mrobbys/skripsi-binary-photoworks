# PRD - Aplikasi Pemesanan dan Penjadwalan Layanan Pada Studio Foto Binary Photoworks

## Product Overview

| Category            | Detail                                                                       |
| :------------------ | :--------------------------------------------------------------------------- |
| **Target Date**     | 30 Juni 2026                                                                 |
| **Document Status** | DRAFT                                                                        |
| **Team Members**    | M. Robby Setiawan (Solo Dev)                                                 |
| **Tech Stack**      | PHP, Javascript, Laravel, Alpine.js, Tailwindcss, Supabase, Midtrans, Fonnte |

## Objective

Mengembangkan aplikasi pemesanan dan penjadwalan layanan pada studio foto Binary Photoworks untuk mendigitalisasi alur reservasi, meminimalisir risiko jadwal bentrok, dan mengotomatisasi pembayaran serta notifikasi menggunakan integrasi Midtrans dan Fonnte.

## Success Metrics

| Goal                                        | Metric                                                                                                                            |
| :------------------------------------------ | :-------------------------------------------------------------------------------------------------------------------------------- |
| Mencegah terjadinya double booking.         | 0% insiden jadwal bentrok karena sistem mengecek ketersediaan secara terkomputerisasi.                                            |
| Kemudahan pemesanan mandiri oleh klien.     | Klien dapat melakukan booking dan melihat jadwal tanpa harus bergantung pada balasan WhatsApp admin di luar jam operasional.      |
| Otomatisasi transaksi dan penyerahan hasil. | 100% verifikasi pembayaran via Midtrans dan pengiriman link Google Drive via WA Fonnte berjalan otomatis tanpa cek mutasi manual. |

## Requirements

| Requirement                     | User Story                                                                                                                    | Importance | Notes                                                                                                                                                                                                     |
| :------------------------------ | :---------------------------------------------------------------------------------------------------------------------------- | :--------- | :-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **User Auth**                   | Sebagai Klien/Admin, saya ingin bisa login agar bisa mengakses menu sesuai hak akses saya.                                    | HIGH       | Terintegrasi penuh dengan Spatie Role/Permission. Pembagian role: Superadmin, Admin, Owner, User. Dilengkapi sistem _Custom Redirect_: Admin/Owner ke dashboard backend, User/Klien ke katalog front-end. |
| **Self-Service Booking**        | Sebagai Klien, saya ingin memilih paket, background, jam sesi, dan layanan tambahan (add-ons) agar bisa pesan secara mandiri. | HIGH       |                                                                                                                                                                                                           |
| **Payment Gateway Integration** | Sebagai Klien, saya ingin membayar pesanan secara online via Midtrans agar diverifikasi secara real-time.                     | HIGH       | Status pembayaran berubah otomatis.                                                                                                                                                                       |
| **Automated WA Notifications**  | Sebagai Admin, saya ingin sistem otomatis mengirim pesan konfirmasi dan link Google Drive ke WA Klien via Fonnte.             | HIGH       |                                                                                                                                                                                                           |
| **Schedule Management**         | Sebagai Admin, saya ingin mengatur jam buka/tutup studio agar tidak ada klien yang pesan di luar jam kerja.                   | HIGH       |                                                                                                                                                                                                           |
| **Reporting System**            | Sebagai Owner, saya ingin mencetak laporan pendapatan dan performa harian ke format PDF agar bisa dievaluasi.                 | HIGH       |                                                                                                                                                                                                           |
| **Review System**               | Sebagai Klien, saya ingin memberikan rating bintang dan ulasan.                                                               | LOW        |                                                                                                                                                                                                           |

## Out of Scope

- Aplikasi ini hanya untuk layanan pemotretan dalam studio (wisuda, family, personal). Layanan skala besar/luar studio (wedding) hanya sebatas informasi katalog dan diarahkan manual ke WA admin.
- Aplikasi tidak mencakup administrasi internal studio seperti sistem penggajian pegawai.
- Aplikasi tidak menyimpan file foto secara internal di server, melainkan hanya memfasilitasi pengiriman link Google Drive.

## Engineering Constraints (Aturan Mutlak Lab)

- **No Inline HTML Scripts:** Melarang keras penulisan script JavaScript secara inline di dalam file HTML/Blade view.
- **Encapsulated Alpine Component:** Seluruh logika front-end interaktif wajib dipisahkan ke dalam blok tag skrip khusus menggunakan pola standarisasi `document.addEventListener('alpine:init')` dan `Alpine.data()`.
- **Asynchronous Communication:** Manfaatkan pustaka Axios dikombinasikan dengan teknik _Debouncing_ untuk menangani fitur penyimpanan otomatis (_auto-save_) maupun operasi AJAX tanpa memicu pemuatan ulang halaman (_reload_).
