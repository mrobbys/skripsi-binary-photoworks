import * as FilePond from "filepond";
import FilePondPluginImagePreview from "filepond-plugin-image-preview";
import FilePondPluginFileValidateType from "filepond-plugin-file-validate-type";
import FilePondPluginFileValidateSize from "filepond-plugin-file-validate-size";
import "filepond/dist/filepond.min.css";
import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css";

FilePond.registerPlugin(
  FilePondPluginImagePreview,
  FilePondPluginFileValidateType,
  FilePondPluginFileValidateSize
);

/**
 * Composable FilePond Helper untuk Form Upload Gambar
 *
 * @param {Object} options
 * @param {Function} options.onFileChange - Callback saat file ditambahkan atau dihapus (file | null)
 * @param {Object} [options.config] - Konfigurasi tambahan untuk FilePond.create
 * @returns {{ initFilePond: Function, resetFilePond: Function, destroyFilePond: Function, getPondInstance: Function }}
 */
export default function useFilePond({ onFileChange, config = {} } = {}) {
  let pondInstance = null;

  const initFilePond = (el) => {
    if (pondInstance) {
      pondInstance.destroy();
    }

    pondInstance = FilePond.create(el, {
      credits: false,
      allowMultiple: false,
      acceptedFileTypes: ["image/jpeg", "image/jpg", "image/png", "image/webp"],
      maxFileSize: "2MB",
      labelIdle: 'Seret gambar ke sini atau <span class="filepond--label-action">Pilih File</span>',
      labelMaxFileSizeExceeded: "Gambar terlalu besar",
      labelMaxFileSize: "Maks. 2 MB",
      labelFileTypeNotAllowed: "Format tidak didukung. Gunakan JPEG, PNG, atau WebP",
      imagePreviewHeight: 180,
      ...config,
    });

    pondInstance.on("addfile", (error, fileItem) => {
      if (!error && fileItem) {
        onFileChange?.(fileItem.file);
      }
    });

    pondInstance.on("removefile", () => {
      onFileChange?.(null);
    });

    return pondInstance;
  };

  const resetFilePond = () => {
    if (pondInstance) {
      pondInstance.removeFiles();
    }
  };

  const destroyFilePond = () => {
    if (pondInstance) {
      pondInstance.destroy();
      pondInstance = null;
    }
  };

  const getPondInstance = () => pondInstance;

  return {
    initFilePond,
    resetFilePond,
    destroyFilePond,
    getPondInstance,
  };
}
