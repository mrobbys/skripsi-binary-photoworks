# Documentation: Data Master - Package & Variant Functional Specification (Drawer UI)

## 1. Halaman Utama: Kelola Paket & Varian (Main Grid View)

Halaman sentral untuk memantau seluruh katalog produk studio yang tersaji menggunakan kerangka kerja data-table **Grid.js** demi performa penyaringan kilat.

### A. Widget Metrik Ringkasan (Top Cards)

- **Card Total Paket**: Menampilkan total entitas paket utama yang terdaftar (Contoh visual: `8 Paket`).
- **Card Total Varian Aktif**: Menampilkan total akumulasi sub-varian paket berstatus aktif di studio (Contoh visual: `10 Varian`).

### B. Baris Kendali Elemen (Control Bar)

- **Input Pencarian**: Fitur pencarian terpadu dengan placeholder `Cari nama paket atau kategori...`.
- **Tombol "+ Tambah Paket"**: Memicu kemunculan _Side-Drawer Form_ untuk memasukkan parameter paket utama baru.

### C. Komponen Tabel Katalog (Dikelola via Grid.js)

Menampilkan baris data paket dengan struktur kolom sebagai berikut:

1. **No.**: Penomoran indeks baris data.
2. **Nama Paket & Kategori**: Menampilkan nama paket utama (Teks Tebal) beserta label kategori di bawahnya (Teks Sekunder Grey) (Contoh: `Personal Portrait` - `CATEGORY: PERSONAL`).
3. **Jumlah Varian**: Jumlah sub-varian yang terikat pada paket tersebut.
4. **Rentang Harga**: Batas harga terendah hingga tertinggi dari varian yang aktif di dalamnya (Contoh: `Rp200.000 - Rp400.000`).
5. **Status**: Sakelar geser (_Toggle Switch_) global untuk mengaktifkan/menonaktifkan paket dari situs booking klien.
6. **Aksi (`⋮`)**: Menu dropdown yang memuat tautan menuju halaman `Detail Paket`, operasi `Edit`, atau tindakan `Hapus`.

---

## 2. Halaman Deteksi Mendalam: Detail Paket View

Halaman khusus yang terbuka ketika pengelola memilih aksi detail pada salah satu paket utama.

### A. Panel Informasi Global Paket

- **Navigasi Balik**: Tautan `← KEMBALI KE KELOLA PAKET` di bagian paling atas.
- **Header Judul**: Menampilkan teks dinamis `Detail Paket: [Nama Paket]`.
- **Blok Informasi Kategori**: Menampilkan nama kategori induk tempat paket ini bernaung dilengkapi tombol `📝 Edit Info Paket`.
- **Blok Keterangan Global**: Menampilkan daftar poin butir (_bullet points_) fasilitas bawaan yang berlaku untuk seluruh varian di bawah paket ini (Tersimpan di tabel `features` secara polimorfik sebagai `Package` featureable).

### B. Area Tabel Hubungan Varian Sesi & Harga

- Memuat sub-tabel berisi daftar varian khusus dengan baris kendali tombol `+ Tambah Varian`.
- Kolom tabel terdiri dari: `No.`, `Nama Varian & Fasilitas` (Nama varian tebal diikuti poin fasilitas spesifik komponen tersebut), `Harga` (Format mata uang Rupiah), `Durasi` (Format menit), `Status` (Toggle keaktifan sub-varian), dan `Aksi` menu tiga titik.

---

## 3. Komponen Formulir Samping (Side-Drawer Form Specifications)

### A. Drawer Satu: Tambah / Edit Paket Baru

Panel yang meluncur dari area kanan layar untuk mengonfigurasi properti dasar paket utama:

- **Pilihan Kategori (Label: Nama Varian / Kategori)**: Komponen input pilihan (_Select Box_) dibantu **Choices.js** untuk mengikat paket ke kategori yang tepat.
- **Input Nama Paket (Label: Nama Paket Spesifik)**: Teks string nama utama paket.
- **Status Paket Aktif Toggle**: Boolean switch penentu visibilitas paket di halaman pemesanan mandiri klien.
- **Keterangan / Fasilitas Global Paket (Dynamic Array Input)**:
    - Input baris teks dinamis yang dikendalikan penuh oleh Alpine.js.
    - Tombol `+ Tambah Baris` untuk memperbanyak baris fasilitas baru.
    - Ikon tempat sampah merah (`🗑️`) di tiap baris untuk menghapus baris fasilitas tertentu secara _real-time_.
- **Footer Action**: Tombol `Batal` dan tombol utama `Simpan & Lanjut Ke Varian`.

### B. Drawer Dua: Tambah / Edit Varian Sesi Baru

Panel khusus untuk memanipulasi detail harga dan durasi waktu internal dari sub-varian paket:

- **Nama Varian**: Input teks penanda paket turunan (Placeholder: `Contoh: Paket 4, Paket Premium`).
- **Harga Varian (Rp)**: Input bertipe numerik positif.
- **Durasi Sesi (Menit)**: Input angka penentu durasi alokasi waktu kalender pemesanan klien.
- **Toggle WhatsApp Only (Alur Manual)**:
    - Parameter pemetaan untuk kolom boolean `is_whatsapp_only`.
    - Fungsi Bisnis: Jika diaktifkan, varian khusus ini (seperti paket foto luar ruangan/wedding) tidak bisa dipesan otomatis via Midtrans melainkan diarahkan langsung ke tim CS WhatsApp manual.
- **Status Varian Aktif Toggle**: Kontrol boolean status ketersediaan opsi sub-varian.
- **Fasilitas Spesifik Varian (Dynamic Array Input)**: Kolom isian teks dinamis bertingkat dilengkapi tombol `+ Tambah Baris` dan ikon hapus `🗑️` (Tersimpan di database sebagai polimorfik `PackageVariant` featureable).
- **Footer Action**: Tombol `Batal` dan `Simpan Varian`.

---

## 4. Regulasi Validasi Server & Integritas Data (Backend Rules)

- **Validasi Sisi Server (PackageRequest & VariantRequest)**:
    - Nama paket dan nama varian wajib terisi (_required_), minimal 3 karakter.
    - Harga dan durasi wajib berupa angka integer positif minimum bernilai 1.
- **Logika Polimorfik Fitur**: Ketika array fasilitas disimpan, server Laravel wajib membedakan pengisian data berdasarkan asal drawer: jika dari Drawer Paket dimasukkan dengan `featureable_type = Package`, jika dari Drawer Varian dimasukkan dengan `featureable_type = PackageVariant`.

---

## 5. Batasan Implementasi Front-End (Alpine.js & Grid.js Setup)

- **Manajemen Array Dinamis**: Penambahan baris, pengosongan nilai placeholder, serta penghapusan indeks baris fasilitas pada kedua komponen drawer dilarang keras menggunakan script manipulasi DOM atau atribut inline HTML. Seluruh status array wajib dibungkus di dalam objek reaktif komponen `Alpine.data()` yang ditanam di dalam tag `<script>` khusus pada bagian bawah berkas `.blade.php` yang sama (SFC Style).
- **Efek Animasi Drawer**: Efek transisi meluncur lancar (_smooth slide-over animation_) saat membuka dan menutup panel samping wajib memanfaatkan direktif transisi bawaan Tailwind CSS yang dikombinasikan dengan pembatas status boolean Alpine (`x-show` dan `x-transition`).
- **Aksi Toggle Instan Tanpa Reload**: Mengubah sakelar keaktifan status di tabel utama akan langsung mengirimkan payload HTTP Request PATCH via **Axios** di latar belakang untuk memperbarui kolom database secara instan.
