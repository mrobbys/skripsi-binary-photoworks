# Documentation: Auth - Register Functional Specification

## 1. Komponen Antarmuka & Elemen UI (Form Inputs)

Halaman pendaftaran (`Daftar`) menggunakan tata letak dua bagian (_split-screen_) dengan form terpusat berbasis Tailwind CSS yang memuat elemen-elemen berikut:

### A. Field Input & Interaksi (Strictly Required)

Semua field di bawah ini bersifat **Wajib Diisi** yang ditandai dengan indikator bintang merah (`*`) pada antarmuka:

- **Nama Lengkap**:
    - Tipe data: String teks alfabet.
    - Placeholder: `Jane Doe`.
    - Batasan: Maksimal 255 karakter.
- **Email**:
    - Tipe data: String format email valid (`@`).
    - Placeholder: `jane@example.com`.
    - Validasi: Harus bersifat unik dan belum pernah terdaftar di database.
- **Nomor Telepon**:
    - Tipe data: String angka/numerik.
    - Placeholder: `+62 ...`.
    - Fungsi khusus: Digunakan sebagai jalur pengiriman tautan galeri foto via Fonnte WhatsApp API kelak.
- **Password**:
    - Tipe data: Alfanumerik khusus (_masked characters_).
    - Kompleksitas: Minimal 8 karakter, wajib mengandung minimal 1 angka, dan minimal 1 huruf besar (kapital).
    - Fitur UI: Tombol ikon mata di sisi kanan untuk melihat/menyembunyikan karakter.
- **Konfirmasi Password**:
    - Tipe data: Alfanumerik khusus (_masked characters_).
    - Validasi: Karakter wajib sama persis secara _real-time_ dengan field Password di atas.
    - Fitur UI: Dilengkapi juga dengan tombol ikon mata mandiri untuk verifikasi visual.

### B. Tombol Aksi & Pemisah (Buttons & Separators)

- **Tombol "DAFTAR"**: Tombol eksekusi utama (CTA) untuk mengirimkan data formulir pendaftaran secara manual.
- **Pemisah "ATAU"**: Garis pembatas horizontal penanda metode alternatif registrasi.
- **Tombol "Daftar Dengan Google"**: Tombol integrasi media sosial untuk pendaftaran instan menggunakan akun Google OAuth2.

### C. Navigasi Kaki (Footer Link)

- **Tautan "Masuk"**: Teks navigasi berbunyi _"Sudah punya akun? Masuk"_ untuk mengarahkan pengguna kembali ke halaman Login jika mereka ternyata sudah memiliki akun terdaftar.

---

## 2. Aturan Validasi Bisnis & Logika Server (Backend Workflow)

### A. Aturan Validasi Laravel Form Request

Sebelum data disimpan ke database Supabase, server akan melakukan pemeriksaan ketat terhadap parameter berikut:

1. `name`: Wajib berupa string teks terisi.
2. `email`: Wajib berformat email, belum ada di tabel `users` (Unique check).
3. `phone`: Wajib berupa nomor telepon valid (tidak boleh huruf).
4. `password`: Harus lulus uji kompleksitas (min 8 karakter, ada huruf besar, ada angka) dan cocok dengan field `password_confirmation`.

### B. Otomatisasi Pembuatan Akun & Penugasan Hak Akses (Role Assignment)

- Setiap pengguna baru yang mendaftar secara mandiri melalui form pendaftaran ini akan langsung dimasukkan ke dalam tabel `users`.
- Sistem secara otomatis memberikan penugasan peran (_Default Role Assignment_) menggunakan Spatie RBAC dengan status sebagai **`user`** (klien publik).

### C. Penanganan Registrasi via Google (OAuth2 Socialite Context)

- Jika pengguna menekan tombol "Daftar Dengan Google", sistem akan mengambil data nama lengkap dan alamat email dari akun Google mereka.
- Kolom password pada database akan diisi secara otomatis dengan string acak terenkripsi yang aman di latar belakang.
- Pengguna yang mendaftar lewat jalur ini akan langsung dialihkan ke dalam sistem tanpa perlu melewati proses aktivasi manual.

---

## 3. Alur Pengalihan Pasca-Pendaftaran (Redirect Rule)

Setelah proses pembuatan baris data user baru di database dinyatakan sukses:

1. Sistem menampilkan notifikasi sukses berisi pesan _"Daftar Akun Berhasil! Silahkan Login dengan akun anda."_
2. Sistem mengalihkan pengguna ke halaman **Login** (`/login`) agar user memasukkan kredensial akun yang baru saja didaftarkan secara manual.

> **Catatan Revisi:** Alur ini sengaja tidak menggunakan _auto-login_ pasca registrasi. User diwajibkan untuk login secara eksplisit demi menjaga kejelasan alur autentikasi.

---

## 4. Batasan Implementasi Front-End (Alpine.js & Axios Constraints)

- **Enkapsulasi Skrip Lokal (Feature-Based Module)**: Seluruh fungsi interaksi antarmuka (seperti aksi klik ikon mata untuk mengintip kata sandi, validasi kesamaan teks konfirmasi sandi) wajib ditulis di dalam berkas JavaScript mandiri `resources/js/features/auth/register.js` menggunakan fungsi `init(Alpine)` dan diregistrasikan via `Alpine.data()`. Lihat [docs/05-dynamic-loader.md](../05-dynamic-loader.md).
- **Larangan Skrip Atribut**: Dilarang menuliskan logika penanganan JavaScript langsung pada atribut tag elemen HTML Blade view.
- **Komunikasi Asinkronus**: Proses pengecekan ketersediaan email (jika menggunakan fitur _live-availability check_) wajib dikirim melalui Axios menggunakan pembatas waktu pengetikan (_Debounce Control_) minimal 500ms agar tidak membebani performa database server.
