# Documentation: Client-Side - Dynamic Module & Page Loader

## 1. Ikhtisar (Overview)

Sistem aplikasi ini menggunakan mekanisme **Dynamic Module & Page Loader** asinkronus pada sisi klien (*client-side*). Logika JavaScript dimuat secara dinamis (*lazy load*) berdasarkan fitur dan halaman yang sedang aktif menggunakan Vite Code-Splitting.

Tujuan utama dari arsitektur ini adalah:
- **Optimalisasi Performa (Code Splitting)**: Halaman hanya mengunduh berkas JavaScript yang benar-benar diperlukannya saja, sehingga ukuran berkas bundle awal (`app.js`) tetap ramping.
- **Modularitas Berbasis Fitur**: Struktur folder JS dikelompokkan berdasarkan fungsionalitas bisnis (menyerupai arsitektur *Domain-Driven Design* di backend).
- **Inisialisasi Lifecycle Alpine.js yang Aman**: Memastikan seluruh registrasi komponen Alpine.js selesai dilakukan sebelum mesin Alpine berjalan (`Alpine.start()`).

---

## 2. Struktur Folder JS Berbasis Fitur (Feature-Based)

Semua logika JS spesifik fitur disimpan di dalam folder `resources/js/features/` dengan pola penamaan: `resources/js/features/[fitur]/[halaman].js`.

Berikut gambaran struktur direktorinya:
```text
resources/js/
├── app.js                   # Entry point utama (Global Bundle)
├── bootstrap.js             # Bootstrap library (Axios, dll)
├── lib/                     # Pustaka utilitas pembantu (e.g., sweetalert.js)
└── features/                # 📂 Folder modular per fitur
    ├── auth/
    │   ├── login.js         # Logika halaman login saja
    │   └── register.js      # Logika halaman register saja
    └── booking/
        ├── flow.js          # Logika alur booking multi-step
        └── history.js       # Logika riwayat booking
```

---

## 3. Cara Kerja (How it Works)

Mekanisme pemuatan berjalan melalui tiga komponen utama yang terhubung:

### A. Pengiriman Metadata dari Blade
Layout utama Blade (`components/layouts/auth.blade.php` atau `components/layouts/app.blade.php`) mengirimkan data identitas fitur dan halaman melalui atribut `data-*` pada tag `<body>`:
```html
<body data-feature="{{ $featureName ?? '' }}" data-page="{{ $pageName ?? '' }}">
```

Pada halaman spesifik (misal `auth/login/index.blade.php`), parameter tersebut dikirimkan ke komponen layout:
```html
<x-layouts.auth title="Masuk" feature-name="auth" page-name="login">
```

### B. Eksekusi Pemuatan Asinkronus di `resources/js/app.js`
Di dalam file [app.js](file:///Users/robbys/WebProjects/Plojek_Laravel/skripsi-binary-photoworks/resources/js/app.js), fungsi `startApplication` membaca atribut dari tag `<body>` dan melakukan dynamic import:

```javascript
const startApplication = async () => {
    const feature = document.body.dataset.feature;
    const page = document.body.dataset.page;

    if (feature && page) {
        try {
            // Mengimpor modul JS secara dinamis saat runtime
            const module = await import(`./features/${feature}/${page}.js`);
            
            // Menjalankan fungsi inisialisasi modul dan mengirim instance Alpine
            if (module.init) {
                module.init(Alpine);
            }
        } catch (err) {
            // Mencegah crash jika file tidak ditemukan / gagal di-load
            console.error(`Gagal memuat JS: features/${feature}/${page}.js`, err);
        }
    }

    // Menjalankan Alpine setelah modul selesai di-load dan teregistrasi
    Alpine.start();
};
startApplication();
```

---

## 4. Cara Menulis Modul JS Spesifik Halaman

Setiap modul halaman wajib mengekspor fungsi bernama `init` yang menerima parameter `Alpine`. Di dalam fungsi ini, Anda bebas meregistrasikan komponen Alpine, memanggil SweetAlert, atau memproses inisialisasi plugin lainnya.

Contoh penulisan pada `resources/js/features/auth/login.js`:
```javascript
/**
 * Inisialisasi logika halaman Login.
 * @param {import('alpinejs').Alpine} Alpine
 */
const init = (Alpine) => {
    // 1. Registrasi data komponen Alpine khusus untuk form login
    Alpine.data('loginForm', () => ({
        showPassword: false,
        email: '',

        togglePassword() {
            this.showPassword = !this.showPassword;
        }
    }));
    
    console.log('Logika JS halaman login berhasil dimuat.');
};

export { init };
```

---

## 5. Panduan & Best Practices

1. **Gunakan `@if` atau `@include` Notifikasi Melalui Blade Component**:
   Untuk menampilkan notifikasi notifikasi flash Laravel (seperti SweetAlert/Toast), panggil komponen Blade pembantu di layout utama, contoh:
   ```html
   <x-scripts.alert-toast />
   ```
2. **Kapan Harus Membuat File JS Halaman?**:
   - **Buat file JS baru** jika halaman tersebut memiliki interaksi form yang kompleks, validasi AJAX, atau menggunakan library pihak ketiga yang berat (e.g., Chart.js, FullCalendar).
   - **TIDAK perlu membuat file JS** jika halaman tersebut hanya berupa halaman statis, atau interaksi Alpine-nya sangat sederhana (misal, toggle dropdown menu biasa cukup ditulis inline pada tag HTML).
3. **Mencegah Error Runtime**:
   Seluruh dynamic import dibungkus dalam blok `try-catch`. Jika ada berkas JS halaman yang hilang atau rusak, aplikasi global (termasuk Alpine.js global) akan **tetap berjalan dengan normal** tanpa merusak visual halaman lain.
