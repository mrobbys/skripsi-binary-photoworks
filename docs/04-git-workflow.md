# Documentation: 04 - Git Workflow & Branching Strategy

## 1. Strategi Percabangan Tiga Lapis (3-Layer Branching Strategy)

Sistem menggunakan pembagian ruang kerja terisolasi untuk menjamin kode pada server VPS produksi tetap berada dalam status stabil dan terhindar dari kerusakan fatal akibat kegagalan pengujian luring.

### A. Hierarki dan Fungsi Branch

1. **`main`**: Jalur utama produksi. Kode di dalam branch ini wajib 100% bebas dari galat runtime dan sinkron sepenuhnya dengan berkas live yang berjalan pada server VPS.
2. **`development`**: Pusat integrasi seluruh fitur baru sekaligus bertindak sebagai area pengujian lokal (Local Testing Sandbox) sebelum peluncuran.
3. **`feature/*` / `fix/*`**: Cabang kerja spesifik berumur pendek yang diisolasi murni untuk membangun satu modul fitur atau perbaikan masalah tertentu.

---

## 2. Standardisasi Pesan Komit (Conventional Commits)

Setiap jejak digital perubahan kode pada pangkalan repositori Binary Photoworks wajib dikawal oleh prefix tipe yang merepresentasikan esensi aktivitas pengodean:

- **`feat`**: Menambahkan fitur baru, halaman baru, atau komponen UI baru.
    > `feat(booking): implement multi-step calendar interface`
- **`fix`**: Memperbaiki kutu logis backend, kegagalan kueri, atau anomali visual UI.
    > `fix(midtrans): repair gross amount calculation for down payment`
- **`chore(deps)`**: Memasang, memperbarui, atau mengonfigurasi pustaka/paket pihak ketiga (Tailwind, Alpine.js, SDK Server).
    > `chore(deps): install tailwindcss and alpinejs via npm`
- **`chore`**: Pekerjaan umum di luar logika aplikasi inti (Contoh: modifikasi berkas pengabaian `.gitignore`).
    > `chore: update gitignore to bypass local environment logs`
- **`docs`**: Segala bentuk penulisan, penambahan, atau modifikasi dokumen markdown, dokumen PRD, dan spesifikasi teknis.
    > `docs(architecture): inject database schema and git workflow specs`

---

## 3. Protokol Rutinitas Pengodean Menuju VPS (Deployment Workflow)

Setiap siklus pengerjaan fitur wajib mematuhi 5 tahapan perintah sekuensial berikut pada terminal lingkungan lokal:

### Langkah 1: Sinkronisasi Jalur Integrasi Lokal

Selalu mulai pekerjaan dengan menyamakan baris kode pada branch integrasi lokal agar terhindar dari konflik kode (_code conflict_):

```bash
git checkout development
git pull origin development
```

### Langkah 2: Pembuatan Cabang Fitur Terisolasi

Lahirkan branch fitur baru langsung dari rahim `development` dan melompat ke dalamnya:

```bash
git checkout -b feature/midtrans-snap
```

### Langkah 3: Eksekusi Komit Inkremental (Bertahap)

Lakukan perekaman komit secara teratur berdasarkan pecahan progres terkecil, hindari komit tunggal skala raksasa di akhir fitur:

```bash
# Selesai instalasi SDK Midtrans
git add composer.json
git commit -m "chore(deps): install midtrans laravel server sdk"

# Selesai membuat UI popup
git add resources/views/frontdoor/booking/
git commit -m "feat(midtrans): craft snap overlay visual interface"
```

### Langkah 4: Merger ke Integrasi Lokal & Testing Stage

Setelah fitur selesai digarap dan lolos uji mandiri, satukan ke `development` menggunakan bendera `--no-ff` agar riwayat percabangan visual tetap tercatat rapi:

```bash
git checkout development
git merge feature/midtrans-snap --no-ff -m "merge: integrate midtrans snap payment interface"
git branch -d feature/midtrans-snap
```

### Langkah 5: Peluncuran ke Produksi (VPS Deployment Ready)

Jika hasil pengujian lokal pada branch `development` dinyatakan 100% aman dan stabil, satukan kode ke branch `main` untuk siap ditarik (_git pull_) oleh server VPS:

```bash
git checkout main
git merge development --no-ff -m "release: deploy stable version to vps server"
git push origin main
```
