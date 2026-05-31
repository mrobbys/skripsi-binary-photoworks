# Documentation: Communications API - WhatsApp Gateway Functional Specification (Fonnte Integration)

## 1. Arsitektur Pemicu Notifikasi (Notification Trigger Points)

Sistem komunikasi ini berjalan di latar belakang (_background processing_) memanfaatkan infrastruktur API Fonnte untuk mengirimkan pesan transaksional terotomatisasi beserta tautan dokumen digital kepada klien.

### A. Momentum 1: Penerbitan Invoice Transaksi (Skenario DP & Skenario Lunas Penuh)

Sistem mendeteksi mutasi keuangan secara _real-time_ dan membagi pemicu pengiriman pesan serta tautan E-Invoice PDF berdasarkan skema yang dipilih oleh pelanggan.

#### Skenario Jalur Lunas Penuh (100% Online Frontend)

- **Pemicu Otomatis**: Klien memilih opsi pembayaran langsung lunas di halaman depan website, lalu sukses menyelesaikan transaksi 100% via Midtrans.
- **Status Pesanan Akhir**: `Lunas`.
- **Format Teks Pesan WhatsApp**:

```text
Halo [NAMA_KLIEN], terima kasih! Pembayaran LUNAS PENUH Anda telah berhasil kami terima.

Kode Booking : [KODE_BOOKING]
Status       : LUNAS (100% Terbayar Resmi)
Total Bayar  : Rp [NOMINAL_TRANSAKSI]
Tanggal Sesi : [TANGGAL_FOTO]

Unduh Bukti Pembayaran Resmi (E-Invoice PDF) Anda melalui tautan berikut:
[APP_URL]/client/appointments/[KODE_BOOKING]/download-invoice

Pantau status jadwal dan detail reservasi Anda langsung di Dasbor Klien:
[APP_URL]/client/dashboard

Jadwal foto Anda telah terkunci aman di sistem kami. Sampai jumpa di studio, Bos!
```

#### Skenario Jalur Bertahap (Uang Muka 60% Frontend + Pelunasan 40% Backdoor)

**Tahap 1: Pembayaran Uang Muka**

- **Pemicu Otomatis**: Klien sukses membayar nilai DP 60% melalui Midtrans di halaman depan website.
- **Status Pesanan Awal**: `DP Terbayar`.
- **Format Teks Pesan WhatsApp**:

```text
Halo [NAMA_KLIEN], pembayaran Uang Muka (DP 60%) Anda sebesar Rp [NOMINAL_DP] untuk Kode Booking [KODE_BOOKING] telah sah diterima sistem.

Unduh nota pembayaran DP Anda melalui tautan berikut:
[APP_URL]/client/appointments/[KODE_BOOKING]/download-invoice

Lihat detail jadwal reservasi Anda pada tautan Dasbor Klien berikut:
[APP_URL]/client/dashboard

Sisa tagihan 40% dapat dilunasi di meja kasir saat hari pelaksanaan sesi foto. Terima kasih!
```

**Tahap 2: Eksekusi Pelunasan di Studio**

- **Pemicu Otomatis**: Admin/Staff kasir mengeksekusi tindakan pelunasan sisa tagihan 40% (baik via Tunai fisik maupun Midtrans POS) pada halaman detail pesanan backdoor.
- **Status Pesanan Akhir**: Berubah dari `DP Terbayar` menjadi `Lunas`.
- **Format Teks Pesan WhatsApp**:

```text
Halo [NAMA_KLIEN], berikut adalah bukti pelunasan sisa tagihan Anda di meja kasir.

Kode Booking : [KODE_BOOKING]
Status       : LUNAS (Sisa 40% Telah Diselesaikan)
Total Bayar  : Rp [NOMINAL_SISA_PELUNASAN]
Tanggal Sesi : [TANGGAL_FOTO]

Unduh Bukti Pembayaran Lunas Penuh (Final E-Invoice PDF) Anda melalui tautan berikut:
[APP_URL]/client/appointments/[KODE_BOOKING]/download-invoice

Riwayat lengkap seluruh transaksi Anda kini dapat diakses melalui Dasbor Klien:
[APP_URL]/client/dashboard

Seluruh administrasi keuangan Anda kini telah dinyatakan LUNAS PENUH. Terima kasih!
```

---

### B. Momentum 2: Distribusi Tautan Hasil Foto (Google Drive Link)

- **Pemicu Otomatis**: Admin memasukkan URL Google Drive pada kolom `gdrive_link` di halaman detail pesanan backdoor dan mengubah status transaksi menjadi `Selesai`.
- **Format Teks Pesan WhatsApp**:

```text
Kabar gembira [NAMA_KLIEN]! 🎉
Proses penyuntingan foto untuk Kode Booking [KODE_BOOKING] telah selesai dilakukan oleh tim fotografer kami.

Anda dapat mengunduh file foto resolusi tinggi melalui tautan resmi Google Drive berikut:
[URL_GOOGLE_DRIVE]

Tautan ini bersifat privat. Pastikan Anda segera mengunduh file sebelum batas waktu penyimpanan berakhir.
```

---

## 2. Aturan Bisnis & Integrasi Sistem Backend (Laravel 13 Notification Layer)

### A. Isolasi Kredensial API Berbasis Atribut (Container Injection)

Mengikuti pedoman mutlak **Laravel 13 Attribute Refactor**, token otentikasi Fonnte dilarang keras ditulis mentah di dalam kode program, melainkan disuntikkan langsung dari file konfigurasi `.env` ke parameter fungsi menggunakan atribut kontainer:

```php
namespace App\Services;

use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\Http;

class FonnteWhatsappService
{
    public function __construct(
        #[Config('services.fonnte.token')] private string $fonnteToken
    ) {}

    public function sendNotification(string $target, string $message): bool
    {
        // Logika pengiriman HTTP Client ke endpoint Fonnte tetap suci dan terjaga
        return true;
    }
}
```

### B. Formula Kecepatan Antrean Pengiriman (Queue Asynchronous Formula)

Agar performa website tidak macet akibat menunggu respons server Fonnte, seluruh proses pengiriman pesan wajib dilempar ke dalam sistem antrean (_Queue Job_) dengan batas toleransi kegagalan yang ketat:

```php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Attributes\Timeout;

#[Tries(3)] // Maksimal percobaan ulang jika jaringan Fonnte sibuk
#[Timeout(60)] // Batas waktu tunggu eksekusi rute
class SendWhatsappNotificationJob implements ShouldQueue
{
    // Sirkuit penanganan antrean berjalan di latar belakang
}
```

---

## 3. Protokol Penanganan Kegagalan & Validasi Kontak (Edge-Case Safety)

- **Standardisasi Nomor Ponsel (Sanitization Input)**: Sistem backend secara otomatis melakukan pembersihan string pada nomor HP Klien sebelum ditembakkan ke API Fonnte. Karakter interlokal seperti `08...` atau `+62...` dipaksa bermutasi menjadi format standar kode negara `628...` menggunakan fungsi regex PHP.
- **Katup Pengaman Log (Fallback Audit Trail)**: Jika sirkuit API Fonnte mengalami kegagalan transmisi (Response status selain `200 OK`), sistem wajib menangkap galat tersebut dan menyuntikkannya ke dalam sistem log internal Laravel (`Log::channel('whatsapp')`) agar Admin dapat melakukan audit kegagalan pengiriman pesan di kemudian hari.
