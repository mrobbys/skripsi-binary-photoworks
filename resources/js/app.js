import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import intersect from '@alpinejs/intersect';
import mask from '@alpinejs/mask';
import persist from '@alpinejs/persist';
import Swal from 'sweetalert2';
import { Toast } from './lib/sweetalert';
import axios from 'axios';

window.axios = axios;

window.Swal = Swal;
window.Toast = Toast;

Alpine.plugin(collapse);
Alpine.plugin(focus);
Alpine.plugin(intersect);
Alpine.plugin(mask);
Alpine.plugin(persist);

window.Alpine = Alpine;

/**
 * Menginisialisasi aplikasi dengan memuat modul JavaScript spesifik fitur/halaman secara asinkron
 * (Vite Code-Splitting) sebelum mesin Alpine.js dijalankan.
 *
 * Mendukung dua pola penulisan modul:
 * - Pola baru (React-style): `export default function ComponentName(Alpine) { ... }`
 *   → Komponen otomatis didaftarkan ke Alpine.data() menggunakan nama file sebagai key.
 * - Pola lama (fallback): `export { init }` → module.init(Alpine) dipanggil langsung.
 */
const startApplication = async () => {
  const modulePathsString = document.body.dataset.module;

  if (modulePathsString) {
    // Memisah modul berdasarkan koma (misal: "auth/Login,auth/Register" -> ["auth/Login", "auth/Register"])
    const modulePaths = modulePathsString.split(',').map(path => path.trim()).filter(Boolean);

    try {
      const modules = import.meta.glob('./features/**/*.js');

      for (const modulePath of modulePaths) {
        const key = `./features/${modulePath}.js`;

        if (modules[key]) {
          const module = await modules[key]();

          // Pola baru: export default function → auto-register Alpine.data()
          // Nama komponen diambil otomatis dari nama file (tanpa ekstensi .js)
          if (module.default) {
            const componentName = key.split('/').pop().replace('.js', '');
            Alpine.data(componentName, () => module.default(Alpine));
          }
          // Fallback: pola lama export { init } tetap didukung
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