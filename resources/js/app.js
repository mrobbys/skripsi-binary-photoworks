import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";
import focus from "@alpinejs/focus";
import intersect from "@alpinejs/intersect";
import mask from "@alpinejs/mask";
import persist from "@alpinejs/persist";
import Swal from "sweetalert2";
import { Toast, Modal, confirmModal } from "./lib/sweetalert";
import "remixicon/fonts/remixicon.css";
import tippy from "tippy.js";
import "tippy.js/dist/tippy.css";
import dayjs from "dayjs";
import currency from "currency.js";
import autoAnimate from "@formkit/auto-animate";
import Choices from "choices.js";
import "choices.js/public/assets/styles/choices.css";
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
import * as FilePond from "filepond";
import FilePondPluginImagePreview from "filepond-plugin-image-preview";
import FilePondPluginFileValidateType from "filepond-plugin-file-validate-type";
import FilePondPluginFileValidateSize from "filepond-plugin-file-validate-size";
import "filepond/dist/filepond.min.css";
import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css";

FilePond.registerPlugin(FilePondPluginImagePreview, FilePondPluginFileValidateType, FilePondPluginFileValidateSize);

import useChoices from "./lib/useChoices";

window.FilePond = FilePond;
window.currency = currency;
window.dayjs = dayjs;
window.tippy = tippy;
window.Swal = Swal;
window.Modal = Modal;
window.Toast = Toast;
window.confirmModal = confirmModal;
window.autoAnimate = autoAnimate;
window.Choices = Choices;
window.flatpickr = flatpickr;

Alpine.plugin(collapse);
Alpine.plugin(focus);
Alpine.plugin(intersect);
Alpine.plugin(mask);
Alpine.plugin(persist);

Alpine.data("choices", useChoices);

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
    const modulePaths = modulePathsString
      .split(",")
      .map((path) => path.trim())
      .filter(Boolean);

    try {
      const modules = import.meta.glob("./features/**/*.js");

      for (const modulePath of modulePaths) {
        const key = `./features/${modulePath}.js`;

        if (modules[key]) {
          const module = await modules[key]();

          // Pola baru: export default function → auto-register Alpine.data()
          // Nama komponen diambil otomatis dari nama file (tanpa ekstensi .js)
          if (module.default) {
            const componentName = key.split("/").pop().replace(".js", "");
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
