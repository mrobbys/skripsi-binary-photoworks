# Documentation: Data Master - Background Functional Specification

## 1. Komponen Antarmuka Utama (Main Grid View)

Halaman sentral bagi pengelola studio untuk mengatur semua ketersediaan layar latar belakang foto indoor. Komponen tabel sepenuhnya ditenagai oleh **Grid.js** untuk manajemen data yang responsif.

### A. Widget Metrik Ringkasan

- **Card Total Background Aktif**: Menampilkan jumlah total latar belakang studio yang saat ini berstatus aktif dan dapat dipilih oleh klien (Contoh visual: `5 Latar`).

### B. Baris Kendali Elemen (Control Bar)

- **Input Pencarian**: Bidang isian teks dengan pencarian ikon kaca pembesar dan placeholder `Cari nama background...`.
- **Tombol "+ Tambah Background"**: Tombol aksi utama (CTA) untuk memicu munculnya jendela formulir penambahan data latar baru.

### C. Komponen Tabel Data (Dikelola via Grid.js)

Menampilkan daftar aset latar belakang studio dengan struktur kolom sebagai berikut:

1. **No.**: Penomoran baris otomatis.
2. **Preview**: Menampilkan gambar mini (_thumbnail container_ persegi) dari aset foto latar asli yang tersimpan di cloud storage.
3. **Nama Background**: Nama pengenal unik latar belakang (Teks Tebal, Contoh: `Putih`, `Abstrak Abu`, `Hitam`).
4. **Deskripsi**: Penjelasan ringkas mengenai nuansa latar untuk membantu operasional admin (Contoh: _"Latar belakang terang, cocok untuk pas foto..."_).
5. **Status**: Sakelar geser (_Toggle Switch_) instan untuk mengubah visibilitas latar belakang (`is_active`) pada form reservasi klien.
6. **Aksi (`⋮`)**: Dropdown tombol interaksi untuk memicu opsi ubah (_Edit_) atau hapus data.

---

## 2. Manajemen Unggah Berkas & Integrasi Cloud Storage

Pengelolaan file gambar pada modul ini sepenuhnya menggunakan standarisasi arsitektur penyimpanan modern:

### A. Komponen Input Unggah (FilePond System)

- Input file pada form penambahan/pengubahan wajib menggunakan pustaka **FilePond** untuk memberikan pengalaman seret-dan-lepas (_drag-and-drop_) yang interaktif bagi admin.
- Fitur FilePond wajib dikonfigurasi untuk melakukan validasi tipe berkas khusus gambar (`image/jpeg`, `image/png`) dengan batas ukuran maksimal **2 MB** demi efisiensi ruang penyimpanan cloud.

### B. Aliran Data Media (Spatie MediaLibrary & Supabase)

- **Abstraksi Kode**: Kontroler tidak boleh mengurusi pemindahan file mentah secara manual. Sistem memanfaatkan _trait_ dari `Spatie\MediaLibrary\HasMedia` yang ditempelkan pada model `Background`.
- **Sektor Penyimpanan**: File gambar yang lolos validasi akan dialirkan oleh driver _Flysystem AWS S3_ menuju bucket `binary-assets` yang berada di infrastruktur **Supabase Storage**.

---

## 3. Aturan Validasi Bisnis & Struktur Server (Backend Rules)

### A. Aturan Validasi Form Request

Setiap kali operasi pembuatan atau pembaruan data dikirim, server Laravel akan mengeksekusi aturan validasi berikut:

- `name`: Wajib diisi (_required_), tipe string, maksimal 50 karakter, unik di dalam tabel `backgrounds`.
- `description`: Wajib diisi (_required_), tipe string teks bebas, maksimal 255 karakter.
- `image`: Wajib diisi saat pembuatan data baru (_required on create_), berupa file gambar valid.

### B. Operasi Basis Data Inti (Database Core Mapping)

- Data teks disimpan langsung ke tabel `backgrounds` (kolom `id`, `name`, `description`, `is_active`, `timestamps`).
- Data biner gambar diatur secara otomatis ke dalam tabel bawaan paket yaitu `media` yang terhubung secara polimorfik dengan id milik tabel backgrounds.

---

## 4. Batasan Implementasi Sisi Depan (Front-End Constraints)

- **Enkapsulasi Skrip Lokal (SFC Style)**: Logika inisialisasi FilePond, pengaturan pratinjau gambar, dan penanganan respons eror dilarang ditulis sebagai skrip inline di dalam atribut tag HTML. Semua wajib dikemas di dalam tag `<script>` khusus pada bagian bawah berkas `.blade.php` yang sama menggunakan struktur `document.addEventListener('alpine:init')` melalui entitas `Alpine.data()`.
- **Kontrol Pengetikan Pencarian (Debounce)**: Aksi pemfilteran baris tabel Grid.js melalui input pencarian nama background wajib dikawal fungsi **Debounce minimal 400ms** via Axios agar tidak membebani performa pembacaan database.
- **Eksekusi Sakelar Status Instan**: Ketika tombol sakelar status aktif/non-aktif pada kolom tabel digeser, komponen Alpine.js harus langsung menembakkan HTTP Request PATCH secara asinkronus menggunakan **Axios** untuk memperbarui nilai `is_active` secara _real-time_ tanpa memicu pemuatan ulang halaman (_zero reload page_).
