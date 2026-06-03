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
    // Mengambil metadata identitas fitur dan halaman dari data-attribute pada tag <body>
    const feature = document.body.dataset.feature;
    const page = document.body.dataset.page;

    if (feature && page) {
        try {
            // Melakukan dynamic import berkas JS halaman berdasarkan fitur yang aktif
            const module = await import(`./features/${feature}/${page}.js`);
            
            // Jika modul memiliki fungsi init, jalankan dan kirimkan instance Alpine
            if (module.init) {
                module.init(Alpine);
            }
        } catch (err) {
            // Tangkap dan catat error secara anggun jika berkas JS halaman tidak ditemukan atau rusak
            console.error(`Gagal memuat JS: features/${feature}/${page}.js`, err);
        }
    }

    // Jalankan Alpine.js setelah semua registrasi komponen lokal selesai dilakukan
    Alpine.start();
};
startApplication();

