# Documentation: Auth - Login Functional Specification

## 1. Komponen Antarmuka & Elemen UI (Form Inputs)

Halaman login menggunakan satu form terpusat berbasis Tailwind CSS dengan komponen input berikut:

### A. Field Input & Interaksi

- **Input Email**:
    - Tipe data: String format email valid (`@`).
    - Sifat: Wajib diisi (_Required_).
    - Validasi: Memunculkan pesan eror jika format salah atau email tidak terdaftar di database.
- **Input Password**:
    - Tipe data: Alfanumerik khusus (_masked characters_).
    - Sifat: Wajib diisi (_Required_).
    - **Ketentuan Kompleksitas**: Panjang minimal 8 karakter, mengandung minimal 1 angka, dan mengandung minimal 1 huruf besar (kapital).
    - Fitur UI: Tombol ikon mata untuk melihat/menyembunyikan teks password (_Toggle Show/Hide_).
- **Checkbox "Ingat Saya" (Remember Me)**:
    - Tipe data: Boolean.
    - Fungsi: Mengaktifkan token _cookie persistent session_ agar akun tidak otomatis keluar (_logout_) ketika peramban ditutup.
- **Tombol Login "Masuk Dengan Google"**:
    - Tipe: Tombol Integrasi OAuth2 (Sosial).
    - Fungsi: Memicu pengalihan ke halaman pemilihan akun resmi Google (_Google Account Chooser_) untuk otentikasi instan tanpa input kata sandi manual.

### B. Navigasi & Tautan Hambatan (_Links_)

- **Tautan Lupa Kata Sandi**: Mengarahkan pengguna ke halaman `Reset Password Page` jika kehilangan akses akun.
- **Tautan Registrasi Baru**: Mengarahkan pengguna non-terdaftar ke halaman `Register Page` khusus klien.

---

## 2. Aturan Validasi Bisnis & Proteksi Keamanan

### A. Validasi Sisi Server (Backend Validation)

- Sistem wajib memvalidasi kepatuhan format masukan kata sandi (minimal 8 karakter, wajib memiliki angka, wajib memiliki huruf besar) sebelum melakukan pencocokan data.
- Sistem wajib mencocokkan kombinasi email dan password pada tabel `users`.
- Jika kredensial salah atau tidak ditemukan, sistem mengembalikan pesan peringatan seragam tanpa memberi tahu apakah email atau password yang salah demi alasan keamanan (Contoh: _"Kredensial yang Anda masukkan tidak cocok dengan data kami."_).

### B. Proteksi Serangan Brute-Force (Rate Limiting)

- Batasan Percobaan: Maksimal **5 kali percobaan masuk** yang gagal dalam kurun waktu 1 menit.
- Efek Pembatasan: Jika batas terlampaui, sistem otomatis mengunci form selama 60 detik dan menampilkan hitung mundur sebelum pengguna bisa mencoba masuk kembali.

### C. Alur Kerja Logika Jalur Masuk Google (OAuth2 Socialite Layer)

- **Kondisi Pengguna Lama**: Jika email Google yang digunakan sudah terdaftar di tabel `users`, sistem langsung membuatkan session aktif dan mengarahkan pengguna ke dasbor yang sesuai.
- **Kondisi Pengguna Baru**: Jika email Google belum ada di database, sistem otomatis membuatkan baris data baru di tabel `users` dengan mengambil data nama dan email dari Google, memberikan Role standar sebagai `user` secara otomatis, lalu memberikan akses masuk langsung.
- **Pengecekan Keamanan**: Jalur masuk menggunakan Google tidak dikenakan aturan pembatasan _Rate Limiting_ lokal karena validasi keamanan sepenuhnya didelegasikan kepada Google.

---

## 3. Matriks Pengalihan Rute Otomatis (Custom Redirect)

Setelah session dinyatakan valid (baik via form manual maupun via Google), sistem membaca hak akses global (Spatie RBAC) dan langsung membagi rute tujuan pelanggan tanpa melalui halaman perantara:

| Kondisi Level Akses (Role)                   | Halaman Tujuan Pasca-Login | Deskripsi Tampilan                                                                           |
| :------------------------------------------- | :------------------------- | :------------------------------------------------------------------------------------------- |
| **`superadmin`** / **`admin`** / **`owner`** | `/backdoor/dashboard`      | Membuka akses penuh ke widget analitik bisnis dan seluruh kendali menu manajemen studio.     |
| **`user`**                                   | `/dashboard`               | Dialihkan ke dasbor akun pribadi untuk langsung mengelola reservasi dan melihat jadwal foto. |

---

## 4. Batasan Implementasi Front-End & Pengiriman Data

- **Pola Enkapsulasi Logika (Feature-Based Module)**: Seluruh logika visual (seperti efek animasi loading pada tombol submit dan fungsi buka-tutup teks password) wajib ditulis di dalam berkas JavaScript mandiri `resources/js/features/auth/login.js` menggunakan fungsi `init(Alpine)` dan diregistrasikan via `Alpine.data()`. Lihat [docs/05-dynamic-loader.md](../05-dynamic-loader.md).
- **Larangan Skrip Atribut**: Dilarang keras menaruh logika JavaScript langsung pada atribut tag HTML (_No Inline Attribute Scripts_).
- **Efisiensi Payload AJAX**: Pengiriman data otentikasi asinkronus (jika ada) wajib divalidasi dan dikirim menggunakan Axios melalui fungsi pengaman pengetukan (_Debounce Control_).
