# Documentation: Client-Side — Dynamic Module & Page Loader

## 1. Ikhtisar (Overview)

Sistem aplikasi ini menggunakan mekanisme **Dynamic Module & Page Loader** asinkronus pada sisi klien (_client-side_). Logika JavaScript dimuat secara dinamis (_lazy load_) berdasarkan fitur dan halaman yang sedang aktif menggunakan Vite Code-Splitting.

Tujuan utama dari arsitektur ini adalah:

- **Optimalisasi Performa (Code Splitting)**: Halaman hanya mengunduh berkas JavaScript yang benar-benar diperlukannya, sehingga ukuran bundle awal (`app.js`) tetap ramping.
- **Modularitas Berbasis Fitur**: Struktur folder JS dikelompokkan berdasarkan fungsionalitas bisnis, menyerupai arsitektur _Domain-Driven Design_ di backend.
- **Inisialisasi Lifecycle Alpine.js yang Aman**: Memastikan seluruh registrasi komponen Alpine.js selesai sebelum mesin Alpine berjalan (`Alpine.start()`).
- **Gaya Penulisan React-Style**: Komponen ditulis sebagai **pure function** menggunakan `Alpine.reactive()` untuk state dan **arrow functions** untuk method — tanpa `this`.

---

## 2. Struktur Folder JS Berbasis Fitur (Feature-Based)

Semua logika JS spesifik fitur disimpan di dalam folder `resources/js/features/` dengan pola penamaan: `resources/js/features/[fitur]/[halaman].js`.

```text
resources/js/
├── app.js                        # Entry point utama (Global Bundle)
├── bootstrap.js                  # Bootstrap library (Axios, dll)
├── lib/                          # Pustaka utilitas pembantu (e.g., sweetalert.js)
└── features/                     # 📂 Folder modular per fitur
    ├── auth/
    │   ├── Login.js              # Logika halaman login
    │   └── Register.js           # Logika halaman register
    └── master-data/
        ├── Category.js           # Logika CRUD kategori
        └── Package.js            # Logika CRUD paket
```

> **Konvensi Penting:** Nama file PascalCase = nama komponen Alpine.
> `Category.js` → `x-data="Category"` | `Login.js` → `x-data="Login"`

---

## 3. Cara Kerja (How it Works)

Mekanisme pemuatan berjalan melalui tiga lapisan yang terhubung:

### A. Pengiriman Metadata dari Blade

Layout utama Blade mengirimkan identitas modul via atribut `data-module` pada tag `<body>`:

```html
<body data-module="{{ $jsModule ?? '' }}"></body>
```

Pada halaman spesifik, parameter dikirimkan ke komponen layout:

```html
<x-layouts.backdoor title="Kategori" js-module="master-data/Category">
  <x-slot:content>
    {{-- Konten halaman --}}
  </x-slot:content>
</x-layouts.backdoor>
```

> **Mendukung Multi-Module:** Jika halaman membutuhkan lebih dari satu modul JavaScript, Anda dapat memisahkannya dengan tanda koma (e.g., `js-module="auth/Login,auth/Register"`). Untuk memuat modul, `app.js` akan membagi string tersebut dengan tanda koma dan mengimpor masing-masing berkas secara asinkron.

### B. Auto-Registration di `resources/js/app.js`

Fungsi `startApplication` membaca `data-module`, mengimpor file JS yang sesuai, lalu **secara otomatis** mendaftarkannya ke `Alpine.data()` menggunakan nama file sebagai key komponen:

```javascript
const startApplication = async () => {
  const modulePathsString = document.body.dataset.module;

  if (modulePathsString) {
    // Memisah modul berdasarkan koma (e.g., "auth/Login,auth/Register")
    const modulePaths = modulePathsString.split(',').map(path => path.trim()).filter(Boolean);

    try {
      const modules = import.meta.glob('./features/**/*.js');

      for (const modulePath of modulePaths) {
        const key = `./features/${modulePath}.js`;

        if (modules[key]) {
          const module = await modules[key]();

          // ✅ Pola baru (React-style): export default function
          // Nama komponen diambil otomatis dari nama file PascalCase (tanpa .js)
          // Contoh: Category.js → Alpine.data('Category', ...)
          if (module.default) {
            const componentName = key.split('/').pop().replace('.js', '');
            Alpine.data(componentName, () => module.default(Alpine));
          }
          // 🔁 Fallback: pola lama export { init } tetap didukung
          else if (module.init) {
            module.init(Alpine);
          }
        } else {
          console.warn(`Modul JS tidak ditemukan untuk path: ${key}`);
        }
      }
    } catch (err) {
      console.error(`Gagal memuat JS untuk modules: ${modulePathsString}`, err);
    }
  }

  Alpine.start();
};
startApplication();
```

> **Alur auto-registration:**
> `master-data/Category.js` dimuat → nama file `Category` diekstrak → `Alpine.data('Category', () => module.default(Alpine))` dipanggil otomatis → di Blade cukup tulis `x-data="Category"`.

### C. Skeleton Blade Memanggil Komponen

Di dalam template Blade, cukup tulis `x-data` dengan nama yang sama persis dengan nama file JS-nya:

```html
{{-- File: resources/views/backdoor/categories/index.blade.php --}}
<x-layouts.backdoor title="Kategori" js-module="master-data/Category">
  <x-slot:content>

    {{-- x-data="Category" → nama diambil dari nama file Category.js --}}
    <div x-data="Category">

      {{-- Akses state menggunakan prefiks state. --}}
      <input type="text" x-model="state.name" placeholder="Nama Kategori" />

      {{-- Panggil method langsung --}}
      <button @click="submit()">Simpan</button>
      <button @click="resetForm()">Reset</button>

      {{-- Conditional rendering --}}
      <span x-show="state.isLoading">Memuat...</span>

    </div>

  </x-slot:content>
</x-layouts.backdoor>
```

---

## 4. Standar Penulisan File JS Fitur (React-Style)

### Struktur Wajib

Setiap file JS fitur **wajib** mengikuti pola berikut:

```javascript
/**
 * [Deskripsi singkat komponen ini]
 *
 * File: resources/js/features/[fitur]/NamaFile.js
 * Penggunaan di Blade: <div x-data="NamaFile">...</div>
 *
 * @param {import('alpinejs').Alpine} Alpine
 * @returns {object}
 */
export default function NamaFile(Alpine) {
  // 1. STATE — terpusat via Alpine.reactive(), mirip useState() React
  const state = Alpine.reactive({
    // semua variabel reaktif di sini
  });

  // 2. SIDE EFFECTS — Alpine.effect() mirip useEffect() React (opsional)
  Alpine.effect(() => {
    // reaktif terhadap perubahan state
  });

  // 3. METHODS — arrow functions, tidak ada 'this'
  const namaMethod = () => {
    // akses state via state.namaVariabel (bukan this.namaVariabel)
  };

  // 4. RETURN — semua yang perlu diakses dari HTML
  return {
    state,
    namaMethod,
  };
}
```

### Contoh Lengkap: `features/master-data/Category.js`

```javascript
/**
 * Logika CRUD halaman Manajemen Kategori.
 *
 * File: resources/js/features/master-data/Category.js
 * Penggunaan di Blade: <div x-data="Category">...</div>
 *
 * @param {import('alpinejs').Alpine} Alpine
 * @returns {object}
 */
import route from "../../../lib/route";

export default function Category(Alpine) {

  // ---------------------------------------------------------------------------
  // State — terpusat, mirip useState() di React
  // ---------------------------------------------------------------------------
  const state = Alpine.reactive({
    name: '',
    slug: '',
    isLoading: false,
    errors: {},
  });

  // ---------------------------------------------------------------------------
  // Side Effects — mirip useEffect() di React
  // Auto-generate slug setiap kali state.name berubah
  // ---------------------------------------------------------------------------
  Alpine.effect(() => {
    if (state.name) {
      state.slug = state.name
        .toLowerCase()
        .replace(/\s+/g, '-')
        .replace(/[^\w-]/g, '');
    }
  });

  // ---------------------------------------------------------------------------
  // Methods — arrow functions, tidak ada 'this'
  // ---------------------------------------------------------------------------

  /** Kirim form tambah/edit kategori */
  const submit = async () => {
    state.isLoading = true;
    state.errors = {};

    try {
      await axios.post(route('backdoor.categories.store'), {
        name: state.name,
        slug: state.slug,
      });

      resetForm();
      // Reload tabel atau redirect
    } catch (error) {
      if (error.response?.status === 422) {
        state.errors = error.response.data.errors;
      }
    } finally {
      state.isLoading = false;
    }
  };

  /** Reset semua state ke nilai awal */
  const resetForm = () => {
    state.name  = '';
    state.slug  = '';
    state.errors = {};
  };

  // ---------------------------------------------------------------------------
  // Return — plain object yang dikonsumsi Alpine di HTML
  // ---------------------------------------------------------------------------
  return {
    state,
    submit,
    resetForm,
  };
}
```

### Aturan yang Wajib Diikuti

| Aturan | ✅ Lakukan | ❌ Hindari |
|---|---|---|
| Export | `export default function NamaFile(Alpine)` (PascalCase, tanpa sufiks) | `export { init }` atau `export default function()` (anonymous) |
| State | `Alpine.reactive({ key: value })` | Variabel `let` biasa (tidak reaktif) |
| Akses State | `state.name` | `this.name` |
| Method | Arrow function: `const submit = () => { }` | Method shorthand: `submit() { this.x }` |
| Watcher | `Alpine.effect(() => { })` | `this.$watch(...)` |
| Event | `document.dispatchEvent(new CustomEvent(...))` | `this.$dispatch(...)` |
| DOM ref | Pass via parameter: `@click="fn($el)"` | `this.$refs.input` |
| Tick | `queueMicrotask(fn)` | `this.$nextTick(fn)` |

### Mengapa Tidak Ada `this`?

`this` di JavaScript bergantung pada **siapa yang memanggil fungsi** (_call context_), bukan di mana fungsi ditulis. Ini menyebabkan perilaku tak terduga di dalam:
- `setTimeout` / `setInterval`
- `Promise.then()` / `async/await`
- Event listener callback

Arrow function menggunakan **lexical scope** — mereka "mengingat" konteks di mana mereka ditulis, sehingga nilainya selalu dapat diprediksi. Ini adalah alasan React, Vue 3 Composition API, dan pola modern JavaScript modern menghindari `this`.

---

## 5. Meneruskan Data dari Laravel (Blade) ke Alpine.js

### A. Rute Laravel via Ziggy (Default)

Sistem sudah mengintegrasikan **Ziggy**, yang memungkinkan Anda menggunakan fungsi `route()` bawaan Laravel langsung di dalam file JavaScript. Ini menghapus kebutuhan untuk menyuntikkan URL rute secara manual via Blade.

```javascript
// Impor fungsi route (wajib)
import route from "../../../lib/route";

export default function Category(Alpine) {
  const submit = async () => {
    // Gunakan fungsi route() persis seperti di PHP
    await axios.post(route('backdoor.categories.store'), { ... });
  };
  
  const update = async (id) => {
    // Mendukung parameter rute
    await axios.put(route('backdoor.categories.update', id), { ... });
  }

  return { submit, update };
}
```

> **Catatan:** Jangan lupa menjalankan `npm run build` jika Anda baru saja menambahkan rute baru di `web.php` atau `api.php`, agar file referensi Ziggy diperbarui!

### B. Konfigurasi Non-Rute (opsional via window.pageConfig)

Jika Anda perlu melempar data selain rute (misal: boolean status, id unik, dsb) dalam jumlah banyak, Anda masih bisa menyuntikkannya via `window.pageConfig` di `<x-slot:heads>`, lalu menghapusnya di JS menggunakan `delete window.pageConfig`. Namun sebisa mungkin, gunakan `data-*` attribute pada HTML jika datanya spesifik untuk satu komponen.

---

## 6. Mengakses Magic Alpine tanpa `this`

Saat Anda membutuhkan fungsionalitas yang biasanya diakses via `this.$...`, gunakan padanan vanilla JS berikut:

| Alpine Magic | Pengganti (No `this`) | Contoh |
|---|---|---|
| `this.$dispatch('event', data)` | `document.dispatchEvent(new CustomEvent('event', { detail: data }))` | Emit event antar komponen |
| `this.$watch('state.x', fn)` | `Alpine.effect(() => { ... })` | Watcher reaktif |
| `this.$refs.input` | Pass via `x-init="init($refs)"` atau `@click="fn($el)"` | Akses elemen DOM |
| `this.$nextTick(fn)` | `queueMicrotask(fn)` atau `requestAnimationFrame(fn)` | Eksekusi setelah render |
| `this.$el` | Pass via event: `@click="doSomething($el)"` | Elemen DOM saat ini |

```javascript
// Contoh: dispatch event ke komponen lain
const deleteCategory = (id) => {
  document.dispatchEvent(
    new CustomEvent('category:deleted', { detail: { id } })
  );
};

// Contoh: auto-generate slug (pengganti $watch)
Alpine.effect(() => {
  if (state.name) {
    state.slug = slugify(state.name);
  }
});
```

---

## 7. Komunikasi Antar Komponen ("Props" & State Sharing)

Ada 3 pola utama untuk komunikasi antar komponen (misal mem-passing "props" atau sharing state), tergantung relasi komponen:

### Pola 1 — `Alpine.store()` (Global Shared State)
> **Kapan Dipakai:** Saat data perlu diakses oleh banyak komponen di halaman berbeda (mirip React Context atau Redux).

```javascript
// Di resources/js/app.js — daftarkan store global sekali
Alpine.store('auth', {
  user: null,
  token: null,
});
```

```javascript
// Di file komponen (misal Login.js)
export default function Login(Alpine) {
  const submit = async () => {
    // ... panggil API login ...
    // ✅ Tulis ke store — komponen lain langsung bisa baca
    Alpine.store('auth').user = res.data.user;
  };
  return { submit };
}
```

```html
<!-- Di komponen mana pun di Blade -->
<div x-data="Navbar">
  {{-- $store tersedia langsung di template tanpa 'this' --}}
  <span x-text="$store.auth.user?.name"></span>
</div>
```

### Pola 2 — Custom Events (Event Bus)
> **Kapan Dipakai:** Saat 2 komponen tidak saling terkait (bukan parent-child) tapi perlu mengirim instruksi/data.

```javascript
// Komponen Pengirim (Kirim event lewat document)
const selectUser = (id) => {
  document.dispatchEvent(
    new CustomEvent('user:selected', { detail: { id } })
  );
};
```

```javascript
// Komponen Penerima
export default function UserDetail(Alpine) {
  const state = Alpine.reactive({ userId: null });

  // Dengarkan event
  document.addEventListener('user:selected', (event) => {
    state.userId = event.detail.id;
  });

  return { state };
}
```

### Pola 3 — Data Attributes & `x-init` (Parent → Child "Props")
> **Kapan Dipakai:** Saat Anda ingin mengirim nilai spesifik langsung dari Blade/PHP ke dalam komponen JS.

```html
{{-- "Props" dikirim via data attribute, elemen DOM di-pass lewat x-init --}}
<div
  x-data="UserCard"
  x-init="init($el)"
  data-user-id="{{ $user->id }}"
  data-user-name="{{ $user->name }}"
>
  <span x-text="state.userName"></span>
</div>
```

```javascript
// UserCard.js
export default function UserCard(Alpine) {
  const state = Alpine.reactive({ userId: null, userName: '' });

  // "Props" dibaca dari elemen DOM
  const init = (el) => {
    state.userId   = el.dataset.userId;
    state.userName = el.dataset.userName;
  };

  return { state, init };
}
```

---

## 8. Panduan & Best Practices

1. **Satu file JS = satu halaman**: Jangan menggabungkan logika dua halaman berbeda dalam satu file.

2. **Kapan harus membuat file JS baru?**
   - ✅ Halaman dengan form kompleks, validasi AJAX, atau library pihak ketiga berat (Chart.js, FullCalendar).
   - ❌ Halaman statis atau interaksi Alpine sangat sederhana (toggle dropdown sederhana cukup inline di HTML).

3. **Gunakan `@if` atau Blade Component untuk notifikasi flash**:
   ```html
   <x-scripts.alert-toast />
   ```

4. **Selalu tangani error**:
   Seluruh dynamic import dibungkus `try-catch`. Jika file JS hilang atau rusak, aplikasi global (termasuk Alpine.js) **tetap berjalan normal** tanpa merusak halaman lain.

5. **Jangan taruh data sensitif di `window.pageConfig`**:
   Token, password, atau secret tidak boleh pernah dikirim via mekanisme ini. Gunakan hanya untuk URL rute, ID publik, atau konfigurasi UI.

---

## 9. Kasus Nyata: Multi-Komponen & Nested Folder

### A. Dua Komponen JS di Satu Halaman Blade

Skenario: Satu halaman auth menampilkan **form Login** dan **form Register** secara bersamaan (misal berupa dua tab atau dua section di satu halaman).

#### Struktur File

```text
resources/js/features/
└── auth/
    ├── Login.js      ← Komponen 1
    └── Register.js   ← Komponen 2
```

#### File JS: `features/auth/Login.js`

```javascript
/**
 * Logika interaktif form Login.
 *
 * File: resources/js/features/auth/Login.js
 * Penggunaan di Blade: <div x-data="Login">...</div>
 *
 * @param {import('alpinejs').Alpine} Alpine
 */
export default function Login(Alpine) {
  // State — mirip useState() di React
  const state = Alpine.reactive({
    showPassword: false,
    isLoading: false,
  });

  // Toggle visibilitas field password
  const togglePassword = () => {
    state.showPassword = !state.showPassword;
  };

  // Submit form — ubah isLoading agar UI tombol berubah menjadi loading
  const submitForm = () => {
    state.isLoading = true;
    // Browser melanjutkan POST ke server (tidak ada preventDefault())
  };

  return {
    state,
    togglePassword,
    submitForm,
  };
}
```

#### File JS: `features/auth/Register.js`

```javascript
/**
 * Logika interaktif form Register.
 *
 * File: resources/js/features/auth/Register.js
 * Penggunaan di Blade: <div x-data="Register">...</div>
 *
 * @param {import('alpinejs').Alpine} Alpine
 */
export default function Register(Alpine) {
  // State — mirip useState() di React
  const state = Alpine.reactive({
    showPassword: false,
    isLoading: false,
    passwordMatch: true,
  });

  // Toggle visibilitas field password
  const togglePassword = () => {
    state.showPassword = !state.showPassword;
  };

  // Cek apakah password dan konfirmasi password sama (mirip useEffect() di React)
  Alpine.effect(() => {
    const pass = document.getElementById('reg-password')?.value ?? '';
    const confirm = document.getElementById('reg-password-confirm')?.value ?? '';
    // Hanya validasi jika kedua field sudah diisi
    if (confirm.length > 0) {
      state.passwordMatch = pass === confirm;
    }
  });

  // Submit form — ubah isLoading agar UI tombol berubah menjadi loading
  const submitRegister = () => {
    if (!state.passwordMatch) return; // Blok jika password tidak cocok
    state.isLoading = true;
    // Browser melanjutkan POST ke server (tidak ada preventDefault())
  };

  return {
    state,
    togglePassword,
    submitRegister,
  };
}
```

#### File Blade: `resources/views/auth/login/index.blade.php`

```html
{{--
  Memuat DUA modul JS secara bersamaan menggunakan koma.
  app.js akan mengimpor Login.js DAN Register.js, lalu mendaftarkan keduanya ke Alpine.
  Urutan pemuatan mengikuti urutan kiri ke kanan.
--}}
<x-layouts.auth
  title='Masuk & Daftar'
  js-module='auth/Login,auth/Register'
>
  <x-slot:content>

    {{-- ============================================================ --}}
    {{-- CAKUPAN KOMPONEN LOGIN                                       --}}
    {{-- x-data="Login" → terhubung ke features/auth/Login.js        --}}
    {{-- ============================================================ --}}
    <div x-data="Login">
      <h1>Form Login</h1>

      {{-- @submit memanggil submitForm() dari Login.js --}}
      <form method="POST" action="{{ route('login.store') }}" @submit="submitForm()">
        @csrf

        <input type="email" name="email" placeholder="Email" required>

        {{-- :type berubah dinamis sesuai state.showPassword dari Login.js --}}
        <div class="relative">
          <input :type="state.showPassword ? 'text' : 'password'" name="password" required>
          {{-- @click memanggil togglePassword() dari Login.js --}}
          <button type="button" @click="togglePassword()">👁</button>
        </div>

        {{-- :disabled & x-text bereaksi terhadap state.isLoading dari Login.js --}}
        <button type="submit" :disabled="state.isLoading">
          <span x-text="state.isLoading ? 'Memproses...' : 'Login'"></span>
        </button>
      </form>
    </div>

    {{-- ============================================================ --}}
    {{-- CAKUPAN KOMPONEN REGISTER                                    --}}
    {{-- x-data="Register" → terhubung ke features/auth/Register.js  --}}
    {{-- State Login.js dan Register.js SEPENUHNYA TERPISAH           --}}
    {{-- ============================================================ --}}
    <div x-data="Register">
      <h2>Form Register</h2>

      {{-- @submit memanggil submitRegister() dari Register.js --}}
      <form method="POST" action="{{ route('register') }}" @submit="submitRegister()">
        @csrf

        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="name" placeholder="Nama Lengkap" required>

        {{-- :type berubah dinamis sesuai state.showPassword dari Register.js --}}
        <div class="relative">
          <input id="reg-password" :type="state.showPassword ? 'text' : 'password'" name="password" required>
          <input id="reg-password-confirm" :type="state.showPassword ? 'text' : 'password'" name="password_confirmation" required>
          <button type="button" @click="togglePassword()">👁</button>
        </div>

        {{-- Pesan error jika password tidak cocok --}}
        <p x-show="!state.passwordMatch" class="text-red-500">Password tidak cocok!</p>

        {{-- :disabled bereaksi terhadap state.isLoading DAN state.passwordMatch dari Register.js --}}
        <button type="submit" :disabled="state.isLoading || !state.passwordMatch">
          <span x-text="state.isLoading ? 'Mendaftarkan...' : 'Daftar'"></span>
        </button>
      </form>
    </div>

  </x-slot:content>
</x-layouts.auth>
```

> **⚡ Poin Penting:**
> - `x-data="Login"` dan `x-data="Register"` adalah dua **scope Alpine yang sepenuhnya terpisah**. State `isLoading` di Login tidak akan bocor ke Register, dan sebaliknya. Persis seperti dua `useState()` di dua komponen React yang berbeda.
> - File `Login.js` dan `Register.js` masing-masing di-*lazy load* oleh Vite sebagai *chunk* terpisah — hanya diunduh sekali dan di-*cache* oleh browser.

---

### B. Nested Folder yang Lebih Dalam

Sistem loader menggunakan `import.meta.glob('./features/**/*.js')` — glob pattern `**` artinya **rekursif tanpa batas kedalaman**. Tidak ada konfigurasi tambahan yang diperlukan di `app.js`.

Selama path yang Anda tulis di `js-module` cocok dengan path file aslinya, loader akan bekerja secara otomatis.

#### Contoh Struktur Nested

```text
resources/js/features/
└── auth/
    └── pages/           ← subfolder tambahan
        ├── Login.js
        └── Register.js
```

#### Di File Blade

```html
{{--
  Cukup sesuaikan js-module dengan path folder yang sesungguhnya.
  Loader secara otomatis menemukan file karena glob pattern '**' bersifat rekursif.
--}}
<x-layouts.auth
  title='Masuk'
  js-module='auth/pages/Login,auth/pages/Register'
>
  <x-slot:content>

    {{-- x-data tetap menggunakan NAMA FILE (PascalCase), bukan path folder --}}
    <div x-data="Login">
      {{-- ... form login ... --}}
    </div>

    <div x-data="Register">
      {{-- ... form register ... --}}
    </div>

  </x-slot:content>
</x-layouts.auth>
```

> **📌 Aturan Kunci Nested Folder:**
> | Yang berubah | Yang tidak berubah |
> |---|---|
> | `js-module="auth/pages/Login"` (path mengikuti lokasi file) | `x-data="Login"` (tetap nama file PascalCase) |
> | Lokasi file: `features/auth/pages/Login.js` | Isi file: tetap `export default function Login(Alpine)` |
>
> **Nama komponen Alpine selalu diambil dari nama FILE-nya saja**, bukan dari path folder-nya. Jadi meski file disimpan sedalam apapun, `x-data` di Blade tidak pernah berubah.

---

## 10. Prop Drilling & Solusinya (Pengganti React Custom Hook)

### Masalah: Prop Drilling

Di React, ketika komponen punya banyak lapisan (`Parent → Child → GrandChild → GreatGrandChild`), dan data dari Parent dibutuhkan oleh GreatGrandChild, Anda harus meneruskan prop melewati setiap lapisan — inilah yang disebut **prop drilling**, dan sangat melelahkan.

Di Alpine.js + arsitektur ini, ada **3 solusi** tergantung situasinya:

| Situasi | Solusi yang Tepat |
|---|---|
| Elemen anak masih dalam satu `x-data` yang sama | **Scope Inheritance** — otomatis, tidak perlu apa-apa |
| Data perlu diakses komponen-komponen **tidak berhubungan** di seluruh aplikasi | **`Alpine.store()`** — seperti `React.createContext()` |
| Banyak komponen punya **logika yang serupa** (loading, toggle, validasi) | **Composable / Custom Hook** — seperti `useCustomHook()` |

---

### Solusi 1 — Scope Inheritance (Bawaan Alpine)

Alpine.js **secara otomatis mewariskan scope** dari elemen `x-data` ke seluruh elemen turunannya di dalam DOM, seberapa pun dalamnya. Elemen anak **tidak perlu menerima prop**; mereka langsung bisa mengakses `state` dan method dari parent.

```html
{{-- Parent: mendefinisikan scope via x-data="Login" --}}
<div x-data="Login">

  <div class="wrapper">
    <div class="inner-wrapper">
      <div class="deep-child">

        {{--
          ✅ Tidak ada prop drilling!
          state.showPassword dan togglePassword() diakses langsung dari Login.js
          meskipun elemen ini 3 lapisan di bawah x-data
        --}}
        <input :type="state.showPassword ? 'text' : 'password'" name="password">
        <button type="button" @click="togglePassword()">👁</button>

      </div>
    </div>
  </div>

</div>
```

> **Kapan cukup pakai ini:** Semua elemen masih berada di dalam **satu** `x-data` yang sama, seberapa pun dalamnya. Ini adalah solusi paling sederhana.

---

### Solusi 2 — `Alpine.store()` sebagai Pengganti React Context

Ini padanan **`React.createContext()` + `useContext()`**. Cocok ketika data perlu dibaca/ditulis oleh komponen-komponen Alpine yang **berbeda `x-data`** dan **tidak saling berhubungan** secara DOM — misalnya antara form login dan navbar.

#### Daftarkan store sekali di `resources/js/app.js`

```javascript
// resources/js/app.js — sebelum Alpine.start()
Alpine.store('auth', {
  user: null,
  isLoggedIn: false,
});
```

#### Tulis ke store dari komponen manapun

```javascript
// features/auth/Login.js
export default function Login(Alpine) {
  const submitForm = async () => {
    const res = await axios.post('/login', { /* ... */ });

    // ✅ Tulis ke store global — komponen lain langsung bisa baca
    Alpine.store('auth').user = res.data.user;
    Alpine.store('auth').isLoggedIn = true;
  };

  return { submitForm };
}
```

#### Baca dari store di komponen manapun di Blade

```html
{{-- Komponen Login: menulis ke store --}}
<div x-data="Login">
  <button @click="submitForm()">Login</button>
</div>

{{-- Komponen Navbar: membaca dari store — tidak ada hubungan DOM dengan Login --}}
{{-- $store tersedia sebagai Alpine magic di seluruh template tanpa 'this' --}}
<div x-data="Navbar">
  <span x-show="$store.auth.isLoggedIn" x-text="'Halo, ' + $store.auth.user?.name"></span>
  <a x-show="!$store.auth.isLoggedIn" href="/login">Masuk</a>
</div>
```

> **Kapan pakai ini:** Data bersifat global dan perlu diakses oleh banyak komponen di banyak tempat — mirip Redux atau React Context di level aplikasi.

---

### Solusi 3 — Composable / Custom Hook (Yang Paling Mirip React ✨)

Ini adalah solusi paling elegan untuk **logika yang berulang di banyak komponen**. Prinsipnya persis sama dengan `useCustomHook()` di React: buat satu fungsi terpisah yang menggabungkan state + logic, lalu impor di mana saja yang membutuhkan.

> Di React: `const { isLoading, toggle } = useAuthForm()`
> Di sini: `const { state, togglePassword } = useAuthForm(Alpine)`

#### Struktur File

```text
resources/js/features/
└── auth/
    ├── useAuthForm.js  ← 🪝 Composable / Custom Hook (bukan komponen Alpine)
    ├── Login.js        ← Komponen Alpine (menggunakan hook)
    └── Register.js     ← Komponen Alpine (menggunakan hook yang sama)
```

#### `features/auth/useAuthForm.js` — Composable

```javascript
/**
 * Composable: Logika bersama untuk form-form Auth.
 *
 * File: resources/js/features/auth/useAuthForm.js
 *
 * PENTING:
 * - File ini BUKAN komponen Alpine (tidak ada export default)
 * - File ini TIDAK didaftarkan ke Alpine.data()
 * - File ini hanya berisi fungsi helper yang di-import oleh komponen lain
 * - Setiap pemanggilan useAuthForm() menghasilkan instance state BARU (tidak shared)
 *   Persis seperti custom hook di React.
 *
 * @param {import('alpinejs').Alpine} Alpine
 */
export function useAuthForm(Alpine) {
  // ---------------------------------------------------------------------------
  // Shared State — setiap komponen yang import hook ini mendapat salinan sendiri
  // ---------------------------------------------------------------------------
  const state = Alpine.reactive({
    isLoading: false,
    showPassword: false,
    errors: {},
  });

  // ---------------------------------------------------------------------------
  // Shared Methods — bisa langsung dipakai oleh komponen yang mengimport
  // ---------------------------------------------------------------------------

  const togglePassword = () => {
    state.showPassword = !state.showPassword;
  };

  const setLoading = (value) => {
    state.isLoading = value;
  };

  const setErrors = (errors) => {
    state.errors = errors;
  };

  const clearErrors = () => {
    state.errors = {};
  };

  return {
    state,
    togglePassword,
    setLoading,
    setErrors,
    clearErrors,
  };
}
```

#### `features/auth/Login.js` — Menggunakan Hook

```javascript
// ✅ Import composable — seperti 'import { useAuthForm } from './useAuthForm'' di React
import { useAuthForm } from './useAuthForm.js';

/**
 * Komponen Login — menggunakan useAuthForm() untuk shared logic.
 *
 * @param {import('alpinejs').Alpine} Alpine
 */
export default function Login(Alpine) {
  // ✅ Memanggil hook — persis seperti: const { state, toggle } = useAuthForm()
  // Destructuring langsung — state dari hook ini TERPISAH dari Register
  const { state, togglePassword, setLoading, setErrors, clearErrors } = useAuthForm(Alpine);

  // Logic spesifik Login — diletakkan di sini, bukan di hook
  const submitForm = async (event) => {
    clearErrors();
    setLoading(true);

    try {
      await axios.post('/login', {
        email: document.getElementById('email').value,
        password: document.getElementById('password').value,
      });

      window.location.href = '/dashboard'; // redirect setelah login berhasil
    } catch (error) {
      if (error.response?.status === 422) {
        setErrors(error.response.data.errors); // tampilkan error validasi
      }
    } finally {
      setLoading(false);
    }
  };

  return {
    state,          // dari hook: { isLoading, showPassword, errors }
    togglePassword, // dari hook
    submitForm,     // spesifik Login
  };
}
```

#### `features/auth/Register.js` — Menggunakan Hook yang Sama

```javascript
import { useAuthForm } from './useAuthForm.js'; // hook yang SAMA

/**
 * Komponen Register — menggunakan useAuthForm() untuk shared logic.
 *
 * @param {import('alpinejs').Alpine} Alpine
 */
export default function Register(Alpine) {
  // ✅ Instance state Register SEPENUHNYA TERPISAH dari Login
  // Mengubah isLoading di Register tidak akan mempengaruhi isLoading di Login
  const { state, togglePassword, setLoading, setErrors, clearErrors } = useAuthForm(Alpine);

  // Logic spesifik Register
  const submitRegister = async (event) => {
    clearErrors();
    setLoading(true);

    try {
      await axios.post('/register', {
        name: document.getElementById('name').value,
        email: document.getElementById('reg-email').value,
        password: document.getElementById('reg-password').value,
      });

      window.location.href = '/dashboard';
    } catch (error) {
      if (error.response?.status === 422) {
        setErrors(error.response.data.errors);
      }
    } finally {
      setLoading(false);
    }
  };

  return {
    state,
    togglePassword,
    submitRegister, // spesifik Register
  };
}
```

#### File Blade — Menggunakan Kedua Komponen

```html
{{-- Kedua komponen dimuat via js-module --}}
<x-layouts.auth title='Auth' js-module='auth/Login,auth/Register'>
  <x-slot:content>

    {{-- SCOPE LOGIN — state.isLoading milik Login, tidak terpengaruh Register --}}
    <div x-data="Login">
      <form method="POST" action="{{ route('login.store') }}" @submit.prevent="submitForm($event)">
        @csrf
        <input type="email" id="email" name="email" required>

        <div class="relative">
          {{-- state.showPassword dari hook Login, terpisah dari Register --}}
          <input :type="state.showPassword ? 'text' : 'password'" id="password" name="password" required>
          <button type="button" @click="togglePassword()">👁</button>
        </div>

        {{-- Tampilkan error dari hook Login --}}
        <template x-if="state.errors.email">
          <small x-text="state.errors.email[0]" class="text-red-500"></small>
        </template>

        <button type="submit" :disabled="state.isLoading">
          <span x-text="state.isLoading ? 'Memproses...' : 'Login'"></span>
        </button>
      </form>
    </div>

    {{-- SCOPE REGISTER — state.isLoading milik Register, sepenuhnya independen --}}
    <div x-data="Register">
      <form method="POST" action="{{ route('register') }}" @submit.prevent="submitRegister($event)">
        @csrf
        <input type="text" id="name" name="name" required>
        <input type="email" id="reg-email" name="email" required>

        <div class="relative">
          {{-- state.showPassword dari hook Register, terpisah dari Login --}}
          <input :type="state.showPassword ? 'text' : 'password'" id="reg-password" name="password" required>
          <button type="button" @click="togglePassword()">👁</button>
        </div>

        <template x-if="state.errors.email">
          <small x-text="state.errors.email[0]" class="text-red-500"></small>
        </template>

        <button type="submit" :disabled="state.isLoading">
          <span x-text="state.isLoading ? 'Mendaftarkan...' : 'Daftar'"></span>
        </button>
      </form>
    </div>

  </x-slot:content>
</x-layouts.auth>
```

> **🔑 Analogi dengan React:**
>
> | React | Alpine.js (arsitektur ini) |
> |---|---|
> | `const { state } = useAuthForm()` | `const { state } = useAuthForm(Alpine)` |
> | `export function useAuthForm() { }` | `export function useAuthForm(Alpine) { }` |
> | Setiap komponen mendapat instance hook sendiri | Setiap komponen mendapat instance state sendiri |
> | Custom hook bisa dicompose dengan hook lain | Composable bisa di-import dan digabung bebas |
> | File hook tidak di-render langsung | File composable tidak didaftarkan ke `Alpine.data()` |

