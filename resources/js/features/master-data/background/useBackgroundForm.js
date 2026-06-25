import route from "../../../lib/route";
import { Modal, Toast } from "../../../lib/sweetalert";
import { z } from "zod";

const backgroundSchema = z.object({
  name: z.string().min(1, "Nama background wajib diisi.").max(50, "Nama background maksimal 50 karakter."),
  description: z.string().max(255, "Deskripsi maksimal 255 karakter.").nullable().optional(),
  is_active: z.boolean(),
});

export default function useBackgroundForm({ state, table }) {
  const resetForm = () => {
    state.isEdit = false;
    state.backgroundId = null;
    state.form.name = "";
    state.form.description = "";
    state.form.is_active = true;
    state.errors = {};
    state.pendingImageFile = null;
    // Kirim event untuk reset FilePond instance di DOM
    document.dispatchEvent(new CustomEvent("background:reset-filepond"));
  };

  const openDrawer = () => {
    resetForm();
    state.isDrawerOpen = true;
  };

  const openEditDrawer = (bg) => {
    resetForm();
    state.isEdit = true;
    state.backgroundId = bg.id;
    state.form.name = bg.name;
    state.form.description = bg.description ?? "";
    state.form.is_active = bg.is_active;
    state.isDrawerOpen = true;
  };

  const closeDrawer = () => {
    state.isDrawerOpen = false;
    setTimeout(() => resetForm(), 500);
  };

  const submitBackground = async () => {
    state.isLoading = true;
    state.errors = {};

    // Validasi form dengan Zod
    const validation = backgroundSchema.safeParse(state.form);
    if (!validation.success) {
      validation.error.issues.forEach((issue) => {
        if (!state.errors[issue.path[0]]) state.errors[issue.path[0]] = issue.message;
      });
    }

    // Validasi sisi klien khusus gambar: gambar wajib saat create
    if (!state.isEdit && !state.pendingImageFile) {
      state.errors.image = "Gambar background wajib diunggah.";
    }

    if (Object.keys(state.errors).length > 0) {
      state.isLoading = false;
      return;
    }

    // Gunakan FormData karena ada file upload
    // eslint-disable-next-line no-undef
    const formData = new FormData();
    formData.append("name", state.form.name);
    formData.append("description", state.form.description ?? "");
    formData.append("is_active", state.form.is_active ? "1" : "0");

    if (state.pendingImageFile) {
      formData.append("image", state.pendingImageFile);
    }

    const url = state.isEdit
      ? route("backdoor.data-master.background.update", state.backgroundId)
      : route("backdoor.data-master.background.store");

    try {
      const response = await window.axios.post(url, formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });

      closeDrawer();
      table.reload();
      Toast.fire({ icon: "success", title: response.data.message });

      if (response.data.total_active_backgrounds !== undefined) {
        state.totalActiveBackgrounds = response.data.total_active_backgrounds;
      }
    } catch (error) {
      if (error.response?.status === 422) {
        const errs = error.response.data.errors;
        for (const key in errs) state.errors[key] = errs[key][0];
      } else {
        Modal.fire({
          icon: "error",
          title: "Gagal menyimpan background",
          text: error.response?.data?.message ?? "Terjadi kesalahan server.",
        });
      }
    } finally {
      state.isLoading = false;
    }
  };

  return { openDrawer, openEditDrawer, closeDrawer, submitBackground };
}
