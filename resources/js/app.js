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
 */
const startApplication = async () => {
  // Ambil path modul dari data-module (misal: "auth/login")
  const modulePath = document.body.dataset.module;

  if (modulePath) {
    try {
      // Mendaftarkan semua file .js di dalam folder features secara rekursif
      const modules = import.meta.glob('./features/**/*.js');
      const key = `./features/${modulePath}.js`;

      if (modules[key]) {
        // Panggil loader function dari glob untuk import asinkronus
        const module = await modules[key]();

        if (module.init) {
          module.init(Alpine);
        }
      } else {
        console.warn(`Modul JS tidak ditemukan untuk path: ${key}`);
      }
    } catch (err) {
      console.error(`Gagal memuat JS untuk module: ${modulePath}`, err);
    }
  }

  Alpine.start();
};
startApplication();