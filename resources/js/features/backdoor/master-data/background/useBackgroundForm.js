import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";
import { z } from "zod";
import { getFieldError } from "@/lib/zodHelper";
import axiosInstance from "@/lib/axiosInstance";
import useFilePond from "@/lib/useFilePond";

const backgroundSchema = z.object({
  name: z.string().min(1, "Nama background wajib diisi").max(50, "Nama background maksimal 50 karakter"),
  description: z.string().max(255, "Deskripsi maksimal 255 karakter").nullable().optional(),
  is_active: z.boolean(),
});

export default function useBackgroundForm({ Alpine, state, table }) {
  const { initFilePond, resetFilePond } = useFilePond({
    onFileChange: (file) => {
      state.pendingImageFile = file;
      validateField("image");
    },
  });

  Alpine.effect(() => {
    const hasImage = state.isEdit || Boolean(state.pendingImageFile);
    const allFilled = Boolean(state.form.name && hasImage);
    const noErrors = !state.errors.name && !state.errors.description && !state.errors.image;

    state.isFormValid = Boolean(allFilled && noErrors);
  });

  const resetForm = () => {
    state.isEdit = false;
    state.backgroundId = null;
    state.form.name = "";
    state.form.description = "";
    state.form.is_active = true;
    state.errors = {};
    state.dismissedErrors = {};
    state.pendingImageFile = null;
    resetFilePond();
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
    state.form.is_active = Boolean(bg.is_active);
    state.isDrawerOpen = true;
  };

  const closeDrawer = () => {
    state.isDrawerOpen = false;
    setTimeout(() => resetForm(), 500);
  };

  const validateField = (field) => {
    if (field === "image") {
      if (!state.isEdit && !state.pendingImageFile) {
        state.errors.image = "Gambar background wajib diunggah";
      } else {
        state.errors.image = null;
      }
      return;
    }

    state.dismissedErrors[field] = true;
    const result = backgroundSchema.safeParse(state.form);
    state.errors[field] = result.success ? null : getFieldError(result, field);
  };

  const submitBackground = async () => {
    const result = backgroundSchema.safeParse(state.form);
    const hasImage = state.isEdit || Boolean(state.pendingImageFile);

    if (!result.success || !hasImage) {
      state.errors = {
        name: getFieldError(result, "name"),
        description: getFieldError(result, "description"),
        image: !hasImage ? "Gambar background wajib diunggah" : null,
      };
      return;
    }

    state.isLoading = true;

    // eslint-disable-next-line no-undef
    const formData = new FormData();
    formData.append("name", state.form.name.trim());
    formData.append("description", state.form.description?.trim() ?? "");
    formData.append("is_active", state.form.is_active ? "1" : "0");

    if (state.pendingImageFile) {
      formData.append("image", state.pendingImageFile);
    }

    const url = state.isEdit
      ? route("backdoor.data-master.background.update", state.backgroundId)
      : route("backdoor.data-master.background.store");

    try {
      const response = await axiosInstance.post(url, formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });

      closeDrawer();
      table.reload();
      Toast.fire({ icon: "success", title: response.data.message });
    } catch (error) {
      if (error.response?.status === 422) {
        state.errors = error.response.data.errors ?? {};
      } else {
        Toast.fire({
          icon: "error",
          title: "Terjadi kesalahan pada server. Silahkan coba beberapa saat lagi",
        });
        console.error(error);
      }
    } finally {
      state.isLoading = false;
    }
  };

  return {
    openDrawer,
    openEditDrawer,
    closeDrawer,
    submitBackground,
    validateField,
    initFilePond,
  };
}
