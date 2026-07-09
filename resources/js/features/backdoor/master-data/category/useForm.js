import route from "@/lib/route";
import { Modal, Toast } from "@/lib/sweetalert";
import { z } from "zod";

const categorySchema = z.object({
  category_code: z.string().min(1, "Kode kategori wajib diisi.").max(3, "Maksimal 3 karakter."),
  name: z.string().min(1, "Nama kategori wajib diisi.").max(100, "Maksimal 100 karakter."),
  is_active: z.boolean(),
});

export default function useForm({ state, table }) {
  const resetForm = () => {
    state.isEdit = false;
    state.categoryId = null;
    state.form.category_code = "";
    state.form.name = "";
    state.form.is_active = true;
    state.errors = {};
  };

  const openDrawer = () => {
    resetForm();
    state.isDrawerOpen = true;
  };

  const closeDrawer = () => {
    state.isDrawerOpen = false;
    resetForm();
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

  const submitCategory = async () => {
    state.isLoading = true;
    state.errors = {};

    // validasi zod
    const validation = categorySchema.safeParse(state.form);
    if (!validation.success) {
      validation.error.issues.forEach((issue) => {
        if (!state.errors[issue.path[0]]) {
          state.errors[issue.path[0]] = issue.message;
        }
      });
      state.isLoading = false;
      return;
    }

    const url = state.isEdit
      ? route("backdoor.data-master.category.update", state.categoryId)
      : route("backdoor.data-master.category.store");
    const method = state.isEdit ? "put" : "post";

    try {
      const response = await window.axios[method](url, state.form);
      closeDrawer();

      table.reload();

      Toast.fire({
        icon: "success",
        title: response.data.message,
      });
    } catch (error) {
      if (error.response?.status === 422) {
        const errs = error.response.data.errors;
        for (const key in errs) {
          state.errors[key] = errs[key][0];
        }
      } else {
        Modal.fire({
          icon: "error",
          title: "Gagal menyimpan kategori",
          text: error.response?.data?.message ?? "Terjadi kesalahan server.",
        });
      }
    } finally {
      state.isLoading = false;
    }
  };

  return {
    openDrawer,
    closeDrawer,
    editCategory,
    submitCategory,
  };
}
