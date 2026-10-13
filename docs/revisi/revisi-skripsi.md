# Panduan & Rencana Kerja Revisi Sidang Skripsi
**Aplikasi Reservasi Studio Foto — Binary Photoworks**

---

## 1. Rekapitulasi Catatan Penguji & Kesepakatan Desain

Berdasarkan hasil sidang skripsi dan diskusi:

| No | Penguji | Catatan Revisi | Solusi / Kesepakatan Desain |
|---|---|---|---|
| 1 | **H. M. MUFLIH, M.Kom** | *(Tidak ada catatan)* | Tidak ada tindakan yang diperlukan. |
| 2 | **IBRAHIM** | Tambahkan *waiting list* apabila ada pemesanan jadwal yang bersamaan. | **Opsi A**: Waiting List otomatis sebagai *fallback* konkurensi saat dua user *checkout* di slot dan detik yang sama. Klien pertama mendapat slot, klien kedua masuk antrean *Waiting List*. |
| 3 | **RIZQI ELMUNA HIDAYAH, S.Si., M.Kom., Ph.D** | 1. Jelaskan mekanisme penguncian slot otomatis.<br>2. Jelaskan mekanisme *auto-release slot* saat token Midtrans kedaluwarsa (*expired*). | Disusun ke dalam naskah **Bab III**: Flowchart di subbab *Usulan Sistem Baru* dan narasi teknis di *Analisis Kebutuhan Fungsional*. Logika kode backend sudah terbukti ada di `BookingService` & `SlotAvailabilityService`. |
| 4 | **RIZQI ELMUNA HIDAYAH, S.Si., M.Kom., Ph.D** | Pastikan validasi kapasitas studio/jumlah orang per varian paket. | **Ditunda sementara**: Dikonfirmasi langsung ke dosen penguji terlebih dahulu (karena batasan orang bersifat deskripsi paket dan dikontrol via SOP fisik di studio). |
| 5 | **RIZQI ELMUNA HIDAYAH, S.Si., M.Kom., Ph.D** | Skenario pengujian ekstrem *double booking* di detik yang sama (Black Box Testing Bab IV). | Dibuatkan **Automated Feature Test** menggunakan **Pest**. Menguji 2 request HTTP checkout simultan, lalu hasilnya dimasukkan ke tabel pengujian Bab IV. |

---

## 2. Roadmap & Urutan Pengerjaan Bertahap

Pengerjaan dibagi menjadi 3 tahapan terstruktur dengan *branching* Git terpisah:

```
┌────────────────────────────────────────┐
│  Fase 1: Fitur Waiting List            │  (Branch: feat/waiting-list-concurrency)
│  - Migrasi tabel waiting_lists         │
│  - Service & Controller logic          │
│  - Notifikasi WA saat slot terbuka     │
└──────────────────┬─────────────────────┘
                   │
                   ▼
┌────────────────────────────────────────┐
│  Fase 2: Pest Concurrency Testing      │  (Branch: test/booking-concurrency-pest)
│  - Inisialisasi framework Pest         │
│  - Simulasi request checkout simultan  │
│  - Bukti pencegahan double-booking     │
└──────────────────┬─────────────────────┘
                   │
                   ▼
┌────────────────────────────────────────┐
│  Fase 3: Pembaruan Naskah Skripsi      │  (Dikerjakan di MacOS)
│  - Bab III: Flowchart & Narasi Kunci   │
│  - Bab IV: Tabel Hasil Uji Pest        │
└────────────────────────────────────────┘
```

---

## 3. Spesifikasi Fase 1: Fitur Waiting List (Opsi A)

### A. Alur Kerja (Workflow)
1. Klien A dan Klien B membuka halaman booking di waktu bersamaan (keduanya melihat slot pukul 13:00 kosong).
2. Klien A menekan tombol Checkout lebih dulu (selisih milidetik):
   - Sistem mengakuisisi `Cache::lock("booking_checkout_{date}")`.
   - Data booking Klien A masuk ke database dengan status `PENDING`.
   - Token Snap Midtrans terbit untuk Klien A.
3. Klien B menekan tombol Checkout sesaat setelahnya:
   - Sistem mengakuisisi `Cache::lock`.
   - Sistem mendeteksi slot pukul 13:00 sudah terisi (`isSlotOccupied() === true`).
   - Alih-alih melempar error umum (*"Slot sudah terisi"*), sistem otomatis mencatat Klien B ke dalam tabel antrean **`waiting_lists`** dengan nomor urut antrean (misal: Antrean #1).
   - Respon JSON mengembalikan status `waiting_list` beserta pesan informatif:
     > *"Slot waktu pukul 13:00 baru saja diambil oleh pelanggan lain. Anda berhasil didaftarkan ke dalam Antrean Waiting List #1. Jika pembayaran pemesan utama dibatalkan atau kedaluwarsa, slot ini akan segera dialihkan kepada Anda."*
4. **Auto-Trigger ke Waiting List**:
   - Jika pembayaran Klien A kedaluwarsa (*expired* via Webhook Midtrans) atau dibatalkan oleh Admin:
   - Sistem memeriksa antrean `waiting_lists` teratas untuk slot tersebut.
   - Sistem mengirimkan notifikasi WhatsApp (Fonnte) kepada Klien B berisi tautan prioritas untuk menyelesaikan pemesanan slot yang baru saja terbuka.

### B. Perubahan Database (`waiting_lists`)
Membuat migrasi tabel baru `waiting_lists`:
- `id`: Primary key
- `booking_date`: Date
- `start_time`: Time (H:i)
- `end_time`: Time (H:i)
- `user_id`: Foreign key ke tabel `users`
- `package_variant_id`: Foreign key ke tabel `package_variants`
- `background_id`: Nullable foreign key ke tabel `backgrounds`
- `notes`: Nullable text
- `status`: Enum (`WAITING`, `NOTIFIED`, `CONVERTED`, `EXPIRED`, `CANCELLED`)
- `timestamps`

### C. Strategi Git Branch
```bash
git checkout development
git pull origin development
git checkout -b feat/waiting-list-concurrency
```

---

## 4. Spesifikasi Fase 2: Pest Concurrency Testing (Bab IV)

### A. Konsep Pengujian Tanpa Browser
Pengujian dilakukan di level API/HTTP Controller menggunakan **Pest**. Request dikirim langsung ke *endpoint*:
`POST /services/booking/checkout`

Payload request:
```json
{
  "package_variant_id": 1,
  "background_id": 1,
  "booking_date": "2026-10-01",
  "start_time": "13:00",
  "payment_scheme": "dp",
  "notes": "Testing Concurrency"
}
```

### B. Skenario Uji Ekstrem
1. **Skenario 1 (Pemesanan Pertama Sukses):**
   - User 1 mengirim request checkout pada slot tanggal `2026-10-01 13:00`.
   - **Hasil Diharapkan**: Status 200/201, baris baru bertambah di tabel `bookings` status `PENDING`.
2. **Skenario 2 (Pencegahan Double Booking & Pengalihan ke Waiting List):**
   - User 2 mengirim request checkout pada slot yang sama persis (`2026-10-01 13:00`).
   - **Hasil Diharapkan**: Sistem menolak pembuatan booking kedua (tidak terjadi *double booking* di tabel `bookings`), dan User 2 masuk ke tabel `waiting_lists`.
3. **Skenario 3 (Auto-Release Slot Kedaluwarsa):**
   - Waktu simulasi dimajukan melebihi `snap_token_expiry` (+1 jam) atau status diubah menjadi `CANCELLED`.
   - User 3 memesan slot tersebut.
   - **Hasil Diharapkan**: Slot kembali tersedia dan dapat dipesan dengan sukses.

### C. Format Tabel Black Box Testing untuk Bab IV Skripsi

| ID Kasus Uji | Skenario Pengujian | Masukan (Input) | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
|---|---|---|---|---|---|
| **TC-CONC-01** | Pengujian reservasi jadwal normal | Klien 1 memilih slot tanggal 01/10/2026 pukul 13:00 dan submit checkout | Slot berhasil dipesan, status booking menjadi PENDING, token Midtrans diterbitkan | Data tersimpan di tabel `bookings`, token pembayaran terbit | **Valid / Berhasil** |
| **TC-CONC-02** | Pengujian ekstrem konkurensi (dua klien submit jadwal yang sama pada detik yang sama) | Klien 2 mengirim request checkout pada slot tanggal 01/10/2026 pukul 13:00 sesaat setelah Klien 1 | Sistem mengunci slot (*distributed lock*), menolak *double booking*, dan mendaftarkan Klien 2 ke daftar *Waiting List* antrean #1 | Booking ganda dicegah, Klien 2 tercatat di tabel `waiting_lists` | **Valid / Berhasil** |
| **TC-CONC-03** | Pengujian *auto-release slot* saat transaksi kedaluwarsa (*expired*) | Waktu transaksi Klien 1 melewati batas 1 jam tanpa pembayaran | Sistem otomatis mengabaikan booking kedaluwarsa dan membuka kembali slot menjadi tersedia | Slot kembali berstatus kosong dan dapat dipesan oleh antrean berikutnya | **Valid / Berhasil** |

### D. Strategi Git Branch
```bash
git checkout development
git pull origin development
git checkout -b test/booking-concurrency-pest
```

---

## 5. Panduan Penyusunan Naskah Skripsi (Bab III & Bab IV)

### A. Penempatan Materi di Naskah
1. **Bab III — Subbab Usulan Sistem Baru**:
   - Letakkan **Flowchart Mekanisme Penguncian & Auto-Release Slot**.
   - Diagram menggambarkan alur: Pengecekan ketersediaan -> Penguncian atomic cache saat checkout -> Validasi overlap -> Pembuatan booking pending -> Percabangan pembayaran (Sukses vs Expired 1 Jam).
2. **Bab III — Subbab Analisis Kebutuhan Fungsional**:
   - Tambahkan butir kebutuhan:
     > *"Sistem harus mampu mengunci ketersediaan slot waktu secara realtime menggunakan algoritma time-overlap dan distributed atomic locking guna mencegah terjadinya double booking pada waktu reservasi yang bersamaan."*
     > *"Sistem harus memiliki mekanisme pelepasan jadwal otomatis (auto-release slot) apabila transaksi pembayaran uang muka (DP) melewati batas kedaluwarsa (1 jam) atau dibatalkan oleh payment gateway, sehingga slot tersebut dapat segera dipesan kembali oleh pelanggan lain."*
3. **Bab IV — Subbab Pengujian Sistem (Black Box Testing)**:
   - Tempelkan tabel hasil pengujian ekstrem konkurensi di atas sebagai bukti pengujian *race condition*.

---

## 6. Checklist Eksekusi Selanjutnya

- [ ] **Langkah 1**: Buat branch `feat/waiting-list-concurrency` di lokal Linux.
- [ ] **Langkah 2**: Implementasikan migrasi tabel `waiting_lists` dan modelnya.
- [ ] **Langkah 3**: Modifikasi `BookingService::processCheckout` untuk mengalihkan ke waiting list jika slot terisi.
- [ ] **Langkah 4**: Uji coba alur waiting list di lokal development.
- [ ] **Langkah 5**: Buat branch `test/booking-concurrency-pest` dan tulis script Pest test.
- [ ] **Langkah 6**: Jalankan `./vendor/bin/pest` hingga semua skenario lolos (*Passed*).
- [ ] **Langkah 7**: Buka MacOS dan salin materi narasi & tabel ke naskah skripsi.
