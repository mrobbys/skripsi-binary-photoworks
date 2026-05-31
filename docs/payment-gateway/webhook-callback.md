# Documentation: Payment Gateway - Asynchronous Webhook Callback Functional Specification

## 1. Arsitektur Endpoint & Mekanisme Pengamanan (Security Verification)

Sistem menyediakan satu gerbang API terbuka yang bertindak sebagai penerima beban data (_payload receiver_) otomatis dari Midtrans Server.

- **Endpoint URL**: `POST /api/payments/webhook`
- **Pengecualian Proteksi Token**: Endpoint ini wajib didaftarkan ke dalam array `$except` pada berkas `VerifyCsrfToken` middleware (atau konfigurasi CSRF di Laravel modern) agar server Midtrans dapat menembakkan data tanpa diinterupsi oleh sistem keamanan internal Laravel.

### A. Protokol Validasi Kunci Tanda Tangan (Signature Key Verification)

Untuk mencegah serangan pemalsuan data transaksi (_spoofing attack_), backend wajib melakukan kalkulasi ulang terhadap parameter `signature_key` yang dikirimkan oleh Midtrans menggunakan algoritma hashing SHA512:

$$
\text{Calculated Signature} = \text{SHA512}(\text{order\_id} + \text{status\_code} + \text{gross\_amount} + \text{Server\_Key})
$$

> 🛡️ **Aturan Mutlak Laboratorium**: Jika hasil enkripsi `Calculated Signature` tidak cocok dengan string `signature_key` bawaan payload Midtrans, sistem wajib langsung memutus sirkuit eksekusi, mencatat insiden keamanan ke dalam Spatie `activity_log`, dan mengembalikan respon `HTTP 403 Forbidden`.

---

## 2. Matriks Mutasi Status Transaksi & Basis Data (State Mutation Matrix)

Setelah lolos uji otentikasi kunci tanda tangan, pengontrol (_Webhook Controller_) akan membaca nilai dari properti `transaction_status` dan `fraud_status` untuk memanipulasi baris data pada tabel `payments` dan `bookings`:

| Midtrans `transaction_status` | Kondisi Tambahan           | Status `payments` | Status `bookings`            | Tindakan Tambahan            |
| :---------------------------- | :------------------------- | :---------------- | :--------------------------- | :--------------------------- |
| `capture`                     | `fraud_status == 'accept'` | `Settlement`      | Tergantung `payment_purpose` | Picu Trigger Fonnte WhatsApp |
| `settlement`                  | -                          | `Settlement`      | Tergantung `payment_purpose` | Picu Trigger Fonnte WhatsApp |
| `pending`                     | -                          | `Pending`         | `Menunggu`                   | Simpan Instruksi VA Code     |
| `deny` / `cancel` / `expire`  | -                          | `Batal`           | `Batal`                      | Bebaskan Slot Waktu Jadwal   |

### A. Logika Percabangan Penentuan Status Booking

Mengingat aplikasi Binary Photoworks mendukung skema pembayaran uang muka (DP 60%) dan Lunas Penuh, mutasi pada kolom `bookings.status` wajib mematuhi aturan relasi berikut:

1. **Jika `payments.payment_purpose == 'dp'`**:
   Status booking bermutasi menjadi **`DP Terbayar`**. Slot jadwal terkunci sepenuhnya.
2. **Jika `payments.payment_purpose == 'pelunasan'` atau Skema Awal adalah Lunas Penuh**:
   Status booking bermutasi menjadi **`Lunas`**.

---

## 3. Sirkuit Komunikasi Otomatis (Fonnte Notification Trigger Layer)

Ketika status pembayaran bertipe keberhasilan mutlak (`settlement` atau `capture accept`) terdeteksi oleh sistem, _Webhook Controller_ wajib memerintahkan objek **`NotificationService`** untuk mengalirkan instruksi kerja ke API Fonnte:

1. **Kasus Pembayaran DP Berhasil**:
    - Sistem menarik data nomor WhatsApp konsumen dari `Table users` lewat relasi booking.
    - Fonnte mengirimkan nota bukti bayar digital berisi detail _Kode Booking_, _Jam Sesi_, dan kalimat penegasan: _"Uang muka sebesar 60% berhasil kami terima, Bos. Slot jadwal Anda telah resmi dikunci!"_.
2. **Kasus Pembayaran Pelunasan / Lunas Penuh Berhasil**:
    - Fonnte mengirimkan invoice pelunasan penuh ke klien.
    - Sistem menyalakan indikator kesiapan sesi pada dasbor admin backdoor (Mengubah status list jadwal harian menjadi `SIAP`).

---

## 4. Contoh Payload Input Logis (Json Sandbox Mockup)

Berikut adalah visualisasi struktur data JSON murni yang dikirimkan oleh server Midtrans Sandbox menuju sirkuit aplikasi Anda untuk dijadikan acuan koding unit testing:

```json
{
    "transaction_time": "2026-05-31 00:02:45",
    "transaction_status": "settlement",
    "status_message": "midtrans payment notification",
    "status_code": "200",
    "signature_key": "8a7c2d9e...computed_sha512_string...",
    "payment_type": "bank_transfer",
    "order_id": "BPW-WSD01-260530-7AX",
    "merchant_id": "G10928374",
    "gross_amount": "300000.00",
    "fraud_status": "accept",
    "currency": "IDR"
}
```
