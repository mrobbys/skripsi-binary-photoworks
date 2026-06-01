# Documentation: Data Master - Add-on Functional Specification

## 1. Komponen Antarmuka Utama (Main Grid View)

Halaman panel pengelola untuk memanipulasi seluruh item layanan tambahan studio. Penyajian data memanfaatkan pustaka **Grid.js** untuk mendukung performa pencarian dan pembagian halaman yang optimal.

### A. Widget Metrik Ringkasan

- **Card Total Add-ons Aktif**: Menampilkan jumlah akumulasi item layanan tambahan yang berstatus aktif di dalam database (Contoh visual: `10 Item`).

### B. Baris Kendali Elemen (Control Bar)

- **Input Pencarian**: Bidang isian teks dengan icon kaca pembesar dan placeholder `Cari nama add-on...`.
- **Tombol "+ Tambah Add-On"**: Tombol aksi berwarna terang untuk memicu kemunculan formulir penambahan data item baru.

### C. Komponen Tabel Layanan Tambahan (Dikelola via Grid.js)

Menampilkan data komprehensif add-on dengan susunan kolom sebagai berikut:

1. **No.**: Penomoran indeks baris data.
2. **Nama Add-On**: Nama teks tebal dari item layanan tambahan (Contoh: `Cetak Foto + Bingkai 10R`, `Extra Time 30 Menit`, `Sewa Kostum Tambahan`, `Sewa Studio Lampu Tambahan`).
3. **Harga**: Nilai nominal dalam format mata uang Rupiah (Contoh: `Rp 75.000`, `Rp 50.000`).
4. **Tipe Input**: Representasi dari kolom kuantitas di database:
    - `Counter (Multi)`: Item bisa dibeli lebih dari satu dalam satu pemesanan (`has_quantity = true`).
    - `Checkbox (Single)`: Item hanya berupa pilihan ya/tidak atau maksimal satu per pesanan (`has_quantity = false`).
5. **Deskripsi**: Penjelasan mendetail kegunaan atau rincian spesifikasi barang (Contoh: _"Cetak resolusi tinggi termasuk bingkai kayu..."_).
6. **Status**: Sakelar geser (_Toggle Switch_) instan untuk mengubah visibilitas item (`is_active`) pada formulir reservasi klien.
7. **Aksi (`⋮`)**: Menu dropdown mini yang menampung opsi modifikasi ubah (_Edit_) atau tindakan eliminasi data (_Delete_).

---

## 2. Aturan Validasi Bisnis & Pemetaan Database (Backend Rules)

### A. Aturan Validasi Form Request (Server-Side)

Setiap paket kiriman data dari formulir tambah/ubah wajib lolos pengujian kriteria server Laravel berikut:

- `name`: Wajib terisi (_required_), tipe string teks, maksimal 100 karakter, dan unik pada tabel `addons`.
- `price`: Wajib terisi (_required_), tipe integer numerik positif (Minimum nilai bernilai 0 jika item bersifat gratis).
- `has_quantity`: Wajib terisi (_required_), bertipe data boolean (`true` jika memilih tipe Counter, `false` jika Checkbox).
- `description`: Wajib terisi (_required_), tipe string teks bebas, maksimal 255 karakter.

### B. Otomatisasi Logika Penghitungan Pemesanan (In-App Flow Integration)

- Ketika klien berada di tahap `Form Add-ons` pada situs utama, sistem akan membaca properti `has_quantity`.
- Jika `has_quantity = true`, UI klien wajib menyajikan tombol increment/decrement (`-` dan `+`) dengan batas minimum bernilai 0. Nilai total harga pesanan dihitung otomatis di latar belakang melalui rumus: $\text{Harga At Purchase} \times \text{Kuantitas}$.

---

## 3. Batasan Implementasi Sisi Depan (Front-End Constraints)

- **Hukum Anti-Inline Attribute JavaScript**: Dilarang keras menuliskan fungsi penanganan klik, manipulasi baris penghitung, atau kontrol status sakelar secara langsung di dalam tag atribut elemen HTML Blade view.
- **Enkapsulasi Struktur Alpine.js (Feature-Based Module)**: Seluruh pengelolaan logika antarmuka (seperti pembukaan modal penambahan, penentuan visual tipe input counter/checkbox pada form, dan pemfilteran Grid.js) wajib diisolasi penuh di dalam berkas JavaScript mandiri `resources/js/features/` menggunakan fungsi `init(Alpine)` dan diregistrasikan via `Alpine.data()`. Lihat [docs/05-dynamic-loader.md](../05-dynamic-loader.md).
- **Pengaman Detak Pencarian (Debounce Control)**: Fitur filter pencarian nama add-on pada komponen Grid.js wajib dikawal menggunakan fungsi **Debounce minimal 400ms** dengan media pengiriman data asinkronus via pustaka **Axios** demi efisiensi query database.
- **Penyimpanan Sakelar Status Instan**: Mengubah status sakelar aktif/non-aktif pada kolom tabel akan langsung memicu penembakan payload HTTP Request PATCH secara asinkronus menggunakan **Axios** di latar belakang. Status database diperbarui secara instan tanpa perlu memicu muat ulang halaman web (_zero page reload_).
