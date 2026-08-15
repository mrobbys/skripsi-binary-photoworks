import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";
import { z } from "zod";
import { getFieldError } from "@/lib/zodHelper";
import axiosInstance from "@/lib/axiosInstance";

const categorySchema = z.object({
  category_code: z.string().min(1, "Kode kategori wajib diisi.").max(3, "Maksimal 3 karakter."),
  name: z.string().min(1, "Nama kategori wajib diisi.").max(100, "Maksimal 100 karakter."),
  is_active: z.boolean(),
});

export default function useForm({ Alpine, state, table }) {
  Alpine.effect(() => {
    const allFilled = Boolean(state.form.category_code && state.form.name);
    const noErrors = !state.errors.category_code && !state.errors.name;
    state.isFormValid = Boolean(allFilled && noErrors);
  });

  const resetForm = () => {
    state.isEdit = false;
    state.categoryId = null;
    state.form.category_code = "";
    state.form.name = "";
    state.form.is_active = true;
    state.errors = {};
    state.dismissedErrors = {};
  };

  const openDrawer = () => {
    resetForm();
    state.isDrawerOpen = true;
  };

  const closeDrawer = () => {
    state.isDrawerOpen = false;
    setTimeout(() => resetForm(), 500);
  };

  const editCategory = (category) => {
    resetForm();
    state.isEdit = true;
    state.categoryId = category.slug;
    state.form.category_code = category.category_code;
    state.form.name = category.name;
    state.form.is_active = category.is_active;
    state.isDrawerOpen = true;
  };

  // Validasi individual per-field
  const validateField = (field) => {
    state.dismissedErrors[field] = true;
    const result = categorySchema.safeParse(state.form);
    if (!result.success) {
      state.errors[field] = getFieldError(result, field);
    } else {
      state.errors[field] = null;
    }
  };

  const submitCategory = async () => {
    // Validasi seluruh schema sebelum request
    const result = categorySchema.safeParse(state.form);
    if (!result.success) {
      state.errors = {
        category_code: getFieldError(result, "category_code"),
        name: getFieldError(result, "name"),
      };
      return;
    }

    state.isLoading = true;

    const url = state.isEdit
      ? route("backdoor.data-master.category.update", state.categoryId)
      : route("backdoor.data-master.category.store");
    const method = state.isEdit ? "put" : "post";

    try {
      const response = await axiosInstance[method](url, state.form);
      closeDrawer();
      table.reload();

      Toast.fire({
        icon: "success",
        title: response.data.message,
      });
    } catch (error) {
      if (error.response?.status === 422) {
        state.errors = error.response.data.errors ?? {};
      } else {
        Toast.fire({
          icon: "error",
          title: "Terjadi kesalahan pada server. Silahkan coba beberapa saat lagi.",
        });
        console.error(error);
      }
    } finally {
      state.isLoading = false;
    }
  };

  return {
    openDrawer,
    closeDrawer,
    editCategory,
    validateField,
    submitCategory,
  };
}
