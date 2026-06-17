# Documentation: Data Master - Studio Operational Hours Functional Specification

## 1. Komponen Antarmuka Utama (Inline Grid View)

Halaman panel pengelola untuk menentukan batasan waktu operasional harian studio. Antarmuka menyajikan matriks baris statis yang merepresentasikan 7 hari dalam seminggu.

### A. Komponen Tabel Konfigurasi (Dikelola via Native Alpine.js)

Menampilkan data jam kerja operasional dengan susunan kolom sebagai berikut:

1. **No.**: Penomoran indeks baris data (01 sampai 07).
2. **Hari**: Teks tebal penanda nama hari dalam seminggu (Senin s.d. Minggu).
3. **Jam Buka**:
    - Elemen input waktu inline berbasis HTML5 `time` atau komponen **Flatpickr** mode waktu.
    - Menampilkan batas awal studio mulai menerima sesi pemotretan (Contoh visual: `09:00`).
4. **Jam Tutup**:
    - Elemen input waktu inline serupa dengan Jam Buka.
    - Menampilkan batas akhir operasional studio dalam satu hari (Contoh visual: `09:00`).
5. **Status**: Sakelar geser (_Toggle Switch_) instan untuk menentukan apakah studio melayani pemesanan pada hari tersebut atau libur (`is_active`).

---

## 2. Aturan Validasi Bisnis & Aliran Logika (Backend Rules)

### A. Aturan Validasi Form Request (Server-Side)

Setiap kali terjadi perubahan data dari elemen input tabel, server Laravel wajib menguji parameter dengan ketentuan berikut:

- `start_time`: Wajib diisi (_required_), berformat waktu valid `H:i` (Contoh: 09:00).

- `end_time`: Wajib diisi (_required_), berformat waktu valid `H:i`, dan nilainya harus jatuh **setelah** waktu `start_time` (Contoh: Jika buka 09:00, tutup tidak boleh sebelum 09:01).

- `is_active`: Wajib diisi (_required_), bertipe data boolean.

### B. Dampak Logika Sistem pada Sisi Klien (In-App Calendar Constraint)

- Ketika pelanggan mengakses modul `Input Calendar & Time` pada formulir pemesanan bertahap di situs depan, sistem akan membaca data dari tabel `schedules` ini.

- Jika status suatu hari diatur `is_active = false` (Libur), maka hari tersebut otomatis terkunci (_disabled/greyed out_) pada kalender pilihan pelanggan.

- Pilihan jam (_time slots_) pemotretan yang dimunculkan kepada pelanggan wajib dibatasi secara ketat hanya berada di dalam rentang antara `start_time` hingga `end_time` dari hari terpilih.

---

## 3. Batasan Implementasi Sisi Depan (Front-End Constraints)

- **Hukum Anti-Inline Attribute JavaScript**: Haram hukumnya menuliskan fungsi pemicu event perubahan, inisialisasi flatpickr, atau manipulasi status sakelar langsung di dalam tag atribut elemen HTML Blade view.

- **Enkapsulasi Struktur Alpine.js (Feature-Based Module)**: Seluruh penanganan logika penangkapan data (seperti mendeteksi perubahan nilai pada input jam buka/tutup dan modifikasi sakelar status aktif) wajib diisolasi penuh di dalam berkas JavaScript mandiri `resources/js/features/` menggunakan fungsi `init(Alpine)` dan diregistrasikan via `Alpine.data()`. Lihat [docs/05-dynamic-loader.md](../05-dynamic-loader.md).

- **Alur Simpan Otomatis Asinkronus (Auto-Save Context)**:
    - Kehilangan fokus input (_blur event_) pada kolom `Jam Buka` atau `Jam Tutup` akan langsung memicu pengiriman data payload ke server secara otomatis.
    - Pengiriman data perubahan jam dan pergeseran sakelar status wajib dieksekusi secara asinkronus menggunakan pustaka **Axios** dengan metode `PATCH` atau `PUT` langsung ke database tanpa memicu muat ulang halaman web (_zero page reload_).
