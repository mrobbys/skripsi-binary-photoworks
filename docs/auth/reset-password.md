# Documentation: Auth - Reset Password Functional Specification

## 1. Alur Tahapan Pertama: Pengajuan Pemulihan (Halaman Lupa Password)

Tahap awal di mana pengguna (klien/admin) meminta tautan pengaturan ulang kata sandi karena kehilangan akses masuk akun.

### A. Elemen Teks & Judul Antarmuka

- **Judul Utama**: `Lupa Password`
- **Deskripsi**: `Masukkan alamat email Anda. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi.`

### B. Komponen Formulir Input

- **Field EMAIL**:
    - Tipe data: String format email.
    - Placeholder: `nama@email.com`.
    - Sifat: Wajib diisi (_Required_).
- **Tombol "KIRIM TAUTAN RESET"**: Tombol aksi utama (CTA) untuk memicu pengiriman token reset ke email terinput.

### C. Navigasi Kaki

- **Tautan Balik**: `← Kembali ke halaman Masuk` (Mengarahkan kembali ke halaman Login utama).

---

## 2. Alur Tahapan Kedua: Notifikasi Pengiriman (Halaman Periksa Email Anda)

Halaman status antara (_interstitial status page_) yang muncul otomatis setelah server berhasil memproses pengiriman token email.

### A. Elemen Teks & Judul Antarmuka

- **Judul Utama**: `Periksa Email Anda`
- **Deskripsi**: `Tautan untuk mengatur kata sandi telah dikirim ke email Anda. Silakan periksa kotak masuk atau folder spam.`

### B. Tombol Aksi & Tautan Navigasi

- **Tombol "Kembali ke Beranda"**: Mengarahkan pengguna kembali ke halaman depan situs utama (_Frontdoor Landing Page_).
- **Tautan "Kirim Ulang"**: Teks interaktif berbunyi `Belum menerima email? Kirim Ulang` untuk memicu kembali fungsi pengiriman email jika token tidak kunjung masuk dalam batas waktu tertentu.

---

## 3. Alur Tahapan Ketiga: Eksekusi Perubahan (Halaman Perbarui Kata Sandi)

Formulir akhir tempat pengguna menetapkan kata sandi baru mereka setelah mengklik tautan resmi dari dalam email mereka.

### A. Elemen Teks & Judul Antarmuka

- **Judul Utama**: `Perbarui Kata Sandi`
- **Deskripsi**: `Silakan masukkan email Anda dan buat kata sandi baru untuk mengamankan akun Anda.`

### B. Komponen Formulir Input (Strictly Required)

Semua field input pada tahapan ini bersifat wajib diisi:

- **Field Email**:
    - Tipe data: String format email terdaftar.
    - Placeholder: `nama@email.com`.
- **Field Password Baru**:
    - Tipe data: Alfanumerik khusus (_masked characters_).
    - Placeholder: `Minimal 8 karakter`.
    - **Aturan Kompleksitas**: Wajib minimal sepanjang 8 karakter, memiliki minimal 1 angka, dan memiliki minimal 1 huruf kapital besar.
    - Fitur UI: Tombol ikon mata di ujung kanan field untuk fungsi _Toggle Show/Hide Password_.
- **Field Konfirmasi Password Baru**:
    - Tipe data: Alfanumerik khusus (_masked characters_).
    - Placeholder: `Ulangi password baru`.
    - Validasi: Karakter wajib identik dengan field Password Baru.
    - Fitur UI: Tombol ikon mata mandiri untuk verifikasi visual.

### C. Tombol Aksi & Navigasi Kaki

- **Tombol "PERBARUI PASSWORD"**: Tombol eksekusi final untuk mengubah password lama di database Supabase menjadi password baru.
- **Tautan Balik**: `← Kembali ke halaman Masuk`.

---

## 4. Aturan Logika Bisnis Server (Backend Handlers)

- **Validasi Token Kedaluwarsa**: Tautan token reset yang dikirim ke email hanya berlaku selama **60 menit**. Jika lewat, sistem wajib menolak proses update dan meminta user mengajukan ulang.
- **Keamanan Enkripsi**: Setelah password baru dinyatakan lulus uji kompleksitas, server wajib melakukan enkripsi ulang menggunakan fungsi Hashing (Bcrypt) sebelum disimpan ke tabel `users`.

---

## 5. Batasan Implementasi Front-End

- **Enkapsulasi Alpine.js (SFC Style)**: Logika visual front-end seperti fungsi membuka mata sandi pada field password baru dan konfirmasi sandi wajib diisolasi rapi di dalam tag `<script>` khusus yang diletakkan pada bagian bawah file `.blade.php` yang sama via objek `Alpine.data()`.
- **Larangan Skrip Atribut**: Dilarang keras menaruh logika JavaScript langsung pada atribut elemen tag HTML (_No Inline Attribute Scripts_).
