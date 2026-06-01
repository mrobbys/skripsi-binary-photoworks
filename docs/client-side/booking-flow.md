# Documentation: Client-Side - Booking Flow & Wizard Functional Specification

## 1. Komponen Antarmuka Wizard & Alur Aliran Data (UI Step Map)

Modul pemesanan mandiri klien menggunakan mekanisme _Multi-Step Wizard Form_ terpusat yang terbagi menjadi beberapa fase sekuensial setelah pengguna berhasil melewati gerbang otentikasi.

### A. Gerbang Katalog Awal (`views/frontdoor/services/index.blade.php`)

- **Komponen Tampilan (`Services.png`)**: Menyajikan daftar kartu paket utama (Contoh: Paket Personal) lengkap dengan informasi batas harga minimum (_starting price_), deskripsi visual, dan tombol pemicu tindakan `Pilih Layanan`.
- **Fungsi Sistem**: Menekan tombol `Pilih Layanan` akan membawa pengguna langsung masuk ke dalam sirkuit multi-step wizard dengan membawa parameter ID paket terpilih.

### B. Langkah 1: Variant Package & Background Selection (`views/frontdoor/booking/flow.blade.php`)

- **Komponen Visual (`Multi Step Form - 1.png`)**: Layout terbagi menjadi dua sektor utama:
    - **Sektor Kiri (Informasi)**: Pratinjau gambar representatif paket dan daftar butir teks ketentuan fungsional (Keterangan fasilitas).
    - **Sektor Kanan (Input Kendali)**:
        - _List Radio Card Varian_: Menampilkan daftar sub-varian paket (Contoh: Basic Rp 299.000) yang memuat rincian durasi sesi, batas foto edit, dan jumlah kuota latar belakang.
        - _Grid Background Picker_: Menampilkan daftar pilihan latar belakang studio dalam bentuk thumbnail lingkaran persegi dengan deskripsi nama teks di bawahnya.
- **Tombol CTA**: `Lanjutkan ke Jadwal Sesi`.

### C. Langkah 2: Pemilihan Tanggal & Slot Waktu

- **Komponen Visual (`Multi Step Form - 2.png`)**: Indikator langkah wizard menunjukkan tahapan `1. Jadwal` berstatus aktif.
    - **Sektor Kiri (Kalender Inline)**: Menampilkan visual penanggalan bulanan berjalan (Contoh: Mei 2026). Tanggal libur (`schedules.is_active = false`) wajib berstatus _disabled/greyed out_.
    - **Sektor Kanan (Grid Slot Waktu)**: Menampilkan tombol-tombol pilihan jam operasional studio yang tersedia pada tanggal terpilih (Contoh rentang: `09:00` WITA).
- **Tombol CTA**: `Lanjutkan ke Layanan Tambahan`.

### D. Langkah 3: Seleksi Layanan Tambahan (Form Add-ons)

- **Komponen Visual (`Multi Step Form - 3.png`)**: Indikator langkah wizard bergeser ke tahapan `2. Add-ons`.
    - Menyajikan tata letak grid dua kolom yang berisi kartu pilihan item layanan tambahan (Contoh: Tambahan 1 orang Rp 25.000 / orang).
    - **Sistem Input**: Jika properti `has_quantity = true`, kartu wajib menampilkan pengontrol penghitung kuantitas numerik (`-` nilai `+`). Jika `has_quantity = false`, cukup menampilkan elemen Checkbox tunggal.
- **Tombol Navigasi**: `Kembali` (ke Langkah 2) dan `Lihat Ringkasan Pesanan`.

### E. Langkah 4: Detail Ringkasan & Skema Pembayaran

- **Komponen Visual (`Multi Step Form - 4.png`)**: Indikator langkah wizard berada di fase puncak `3. Ringkasan`.
    - **Tabel Rincian Invoice**: Menampilkan ringkasan teks komprehensif berupa Nama Paket, Pilihan Background, Tanggal Sesi, Jam Waktu Sesi, Data Profil Pelanggan (Nama, WhatsApp, Email), breakdown harga varian dasar, rincian biaya itemized add-ons, serta Total akumulasi biaya.
    - **Input Skema Bayar (Radio Group)**: Pilihan penentuan skema transaksi antara bayar `Lunas` atau `DP 60%`.
- **Tombol CTA**: `Bayar Sekarang` (Memicu kemunculan pop-up tertanam Midtrans Snap SDK).

### F. Halaman Akhir: Sukses Reservasi (`views/frontdoor/booking/success.blade.php`)

- **Komponen Tampilan (`Reservasi Berhasil.png`)**: Tampilan invoice sukses pasca-pembayaran webhook Midtrans terkonfirmasi.
    - Menampilkan ID Reservasi/Kode Booking unik (Contoh: `BPN-25MAY09`), rincian jadwal dengan keterangan zona waktu lengkap (`WITA`), dan data total nominal pembayaran.
    - **Dinamis Badge**: Menampilkan status skema bayar terpilih (Contoh: `DP 60% TERBAYAR (Rp 300.000)`) lengkap dengan maklumat peringatan sisa kewajiban pelunasan tunai/QRIS di studio sebesar Rp 200.000 setelah sesi foto selesai.
- **Aksi Tautan Kaki**: Tersedia tombol `Lihat Riwayat Pesanan` (Mengarahkan ke dashboard user) dan `Unduh Bukti Reservasi (PDF)`.

---

## 2. Aturan Bisnis & Algoritma Inti Pencegah Overlap Jadwal (0% Double Booking)

### A. Sistem Validasi Konflik Waktu Real-Time

Untuk menjamin tidak terjadi tabrakan jadwal pemotretan pada sistem laboratory, sistem wajib mengeksekusi pemeriksaan berlapis pada tabel `bookings` menggunakan parameter `booking_date`, `start_time`, dan `end_time`:

- **Pemicu Deteksi**: Setiap kali klien mengklik tanggal pada kalender Langkah 2, Alpine.js langsung menembakkan request asinkronus via Axios menuju endpoint API internal.

2. **Kueri Basis Data (Sektor Repository Layer)**:

```php
// Mencari booking yang sudah berstatus sukses/terbayar pada tanggal & jam terkait
$isOccupied = Booking::where('booking_date', $selectedDate)
    ->where('status', '!=', 'Batal')
    ->where(function ($query) use ($startTime, $endTime) {
        $query->where(function ($q) use ($startTime, $endTime) {
            $q->where('start_time', '<', $endTime)
              ->where('end_time', '>', $startTime);
        });
    })->exists();
```

- **Efek UI**: Jika `$isOccupied` bernilai `true`, slot tombol jam tersebut otomatis diberikan atribut `.disabled` pada grid front-end agar tidak dapat diklik oleh klien.

### B. Rumus Kalkulasi Finansial Sistem

Sistem matematika internal menghitung akumulasi total pesanan menggunakan kombinasi harga paket dasar dan volume layanan add-on:

$$
\text{Total Price} = \text{Price Varian} + \sum (\text{Price Addon} \times \text{Quantity})
$$

Jika pengguna memilih skema pembayaran uang muka (Down Payment 60%) pada formulir konfirmasi ringkasan:

$$
\text{Uang Muka (DP)} = \text{Total Price} \times 60\%
$$

$$
\text{Sisa Pelunasan Kasir} = \text{Total Price} - \text{Uang Muka (DP)}
$$

---

## 3. Batasan Implementasi Sisi Depan (Front-End Constraints - Feature-Based Modules)

- **Hukum Anti-Inline HTML Attribute Script**: Haram hukumnya menaruh baris penanganan perubahan langkah wizard, manipulasi angka konter add-on, atau klik pilihan penanggalan langsung di dalam atribut tag HTML elemen view (Wajib steril dari penggunaan atribut mentah seperti `onclick="..."` atau `onchange="..."`).
- **Enkapsulasi Berkas Bersisian (Co-location Style via @include)**:
    - Struktur tata letak antarmuka multi-step diletakkan pada berkas induk `flow.blade.php`.
    - Seluruh manajemen state reaktif rekayasa wizard (seperti objek array `currentStep: 1`, `selectedPackage`, `selectedBackground`, `selectedDate`, `selectedTime`, beserta fungsi `nextStep()` dan `prevStep()`) wajib diisolasi di dalam file pendukung bernama `script.blade.php`.
    - Berkas skrip lokal ini wajib diintegrasikan ke bagian paling bawah file induk menggunakan perintah:
    -

```html
<x-layouts.frontdoor>
    <div x-data="bookingWizardHandler"></div>

    @include('frontdoor.booking.script')
</x-layouts.frontdoor>
```

- **Optimasi Detak Payload (Debounce)**: Setiap pemanggilan data eksternal penentuan ketersediaan tanggal/waktu via Axios wajib dikawal menggunakan fungsi pengaman Debounce guna menjaga performa kueri database PostgreSQL Supabase.
