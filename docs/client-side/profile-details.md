# Documentation: Client-Side - Profile Details Functional Specification

## 1. Komponen Antarmuka Pengelolaan Profil (UI Layout Mapping)

Halaman pembaruan data diri ini menggunakan kerangka induk berekstensi komponen `<x-layouts.frontdoor>` dengan pembagian struktur visual dua kolom yang simetris.

### A. Komponen Sidebar Kontrol Klien

- **Komponen Tampilan (`Dashboard User - Profil Saya Active.png`)**:
    - Menampilkan ringkasan informasi akun aktif berupa Nama Klien (`Robby Setiawan`) dan alamat Email (`rubi@gmail.com`) pada bagian atas panel.
    - **Status Navigasi**: Menu `Profil Saya` berada dalam posisi aktif, ditandai dengan balutan warna latar belakang abu-abu (_active state background shading_).

### B. Komponen Formulir Data Diri ("Profil Saya")

- **Komponen Visual (`Dashboard User - Profil Saya Active.png`)**:
    - **Header Modul**: Teks judul `Profil Saya` berukuran besar sebagai penanda zona kerja.
    - **Sektor Input Form**: Menyediakan tiga elemen kotak input teks esensial yang wajib dilengkapi (ditandai dengan simbol asterisk merah `*`):
        1. **NAMA LENGKAP \***: Input berbasis teks untuk memperbarui nama akun pengguna.
        2. **EMAIL \***: Input berbasis teks format email (bersifat unik di dalam sistem database).
        3. **NO TELEPON \***: Input berbasis angka nomor WhatsApp aktif pelanggan yang terintegrasi dengan gateway Fonnte.
- **Tombol Aksi Eksekusi**: Tombol lebar penuh (_full-width_) berwarna solid bertuliskan `Simpan Perubahan` diletakkan pada baris paling bawah form.

---

## 2. Aturan Bisnis & Validasi Gerbang Backend (Data Integrity Rules)

### A. Protokol Validasi Form Request Layer

Sebelum aliran data menyentuh tabel `users`, sistem wajib melakukan inspeksi ketat melalui `Form Request Layer` dengan parameter aturan baku berikut:

- **Nama Lengkap**: `required | string | min:3 | max:100`
- **Email**: `required | string | email | max:255 | unique:users,email,` sesuai ID pengguna aktif saat ini untuk menghindari duplikasi interupsi.
- **No Telepon**: `required | numeric | digits_between:10,14` (Wajib menggunakan format nomor lokal bersih agar sirkuit transmisi WhatsApp API Fonnte tidak mengalami malfungsi).

### B. Aliran Manajemen Data (Data Flow)

1. Klien menekan tombol `Simpan Perubahan`.
2. Alpine.js mencegat aksi submit bawaan browser, mengaktifkan status reaktif `isLoading = true`, lalu mengirimkan payload via Axios menggunakan metode HTTP `PUT/PATCH` menuju endpoint `/api/client/profile/update`.
3. Server memproses validasi, mengonversinya menjadi objek `ProfileData` bertipe pasti (DTO), dan memerintahkan `UserRepository` untuk memperbarui baris data terkait di database.
4. Jika proses berhasil, sistem mengembalikan respon JSON sukses yang langsung memicu pop-up notifikasi SweetAlert2 bertuliskan "Data diri berhasil diperbarui, Bos!".

---

## 3. Batasan Pembatasan Sisi Depan (Front-End Constraints - SFC Style)

- **Hukum Anti-Inline HTML Attribute Script**: Seluruh penanganan animasi pemuatan tombol (_loading state spinner_), pencegatan tombol submit, dan validasi reaktif sisi depan haram hukumnya ditulis langsung di dalam atribut elemen HTML (Bebas penuh dari instruksi mentah `onsubmit="..."`).
- **Enkapsulasi Berkas Bersisian (Co-location Style via @include)**:
    - Struktur elemen input formulir diletakkan pada berkas views utama bernama `details.blade.php`.
    - Logika reaktif pengiriman data AJAX (objek `name`, `email`, `phone`, `errors: []`, dan fungsi `updateProfile()`) wajib diisolasi di dalam file pendukung terpisah bernama `profile-script.blade.php` di folder yang sama.
    - Proses penyatuan berkas wajib dikunci menggunakan direktif `@include` lokal pada baris paling bawah:

```html
<!-- views/frontdoor/dashboard/details.blade.php -->
<x-layouts.frontdoor>
    <div x-data="clientProfileHandler">
        <!-- Render Form UI Profil Saya Sesuai Gambar -->
    </div>

    @include('frontdoor.dashboard.profile-script')
</x-layouts.frontdoor>
```
