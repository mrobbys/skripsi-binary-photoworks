import route from "@/lib/route";
import { Modal, Toast } from "@/lib/sweetalert";
import { z } from "zod";

const addonSchema = z.object({
  name: z.string().min(1, "Nama add-on wajib diisi.").max(100, "Nama add-on maksimal 100 karakter."),
  price: z
    .union([z.string(), z.number()])
    .refine((val) => !isNaN(parseInt(val)) && parseInt(val) >= 0, "Harga harus berupa angka dan tidak boleh negatif."),
  description: z.string().min(1, "Deskripsi wajib diisi.").max(255, "Deskripsi maksimal 255 karakter."),
  has_quantity: z.boolean(),
  is_active: z.boolean(),
});

export default function useAddonForm({ state, table }) {
  const resetForm = () => {
    state.isEdit = false;
    state.addonId = null;
    state.form.name = "";
    state.form.price = "";
    state.form.description = "";
    state.form.has_quantity = false;
    state.form.is_active = true;
    state.errors = {};
  };

  const openDrawer = () => {
    resetForm();
    state.isDrawerOpen = true;
  };

  const openEditDrawer = (addon) => {
    resetForm();
    state.isEdit = true;
    state.addonId = addon.id;
    state.form.name = addon.name;
    state.form.price = addon.price;
    state.form.description = addon.description ?? "";
    state.form.has_quantity = addon.has_quantity;
    state.form.is_active = addon.is_active;
    state.isDrawerOpen = true;
  };

  const closeDrawer = () => {
    state.isDrawerOpen = false;
    setTimeout(() => resetForm(), 500);
  };

  const submitAddon = async () => {
    state.isLoading = true;
    state.errors = {};

    // Validasi sisi klien dengan Zod
    const validation = addonSchema.safeParse(state.form);
    if (!validation.success) {
      validation.error.issues.forEach((issue) => {
        if (!state.errors[issue.path[0]]) state.errors[issue.path[0]] = issue.message;
      });
      state.isLoading = false;
      return;
    }

    const payload = {
      name: state.form.name.trim(),
      price: parseInt(state.form.price, 10),
      description: state.form.description.trim(),
      has_quantity: state.form.has_quantity,
      is_active: state.form.is_active,
    };

    const url = state.isEdit
      ? route("backdoor.data-master.addon.update", state.addonId)
      : route("backdoor.data-master.addon.store");

    const method = state.isEdit ? "put" : "post";

    try {
      const response = await window.axios[method](url, payload);

      closeDrawer();
      table.reload();
      Toast.fire({ icon: "success", title: response.data.message });

      if (response.data.total_active_addons !== undefined) {
        state.totalActiveAddons = response.data.total_active_addons;
      }
    } catch (error) {
      if (error.response?.status === 422) {
        const errs = error.response.data.errors;
        for (const key in errs) state.errors[key] = errs[key][0];
      } else {
        Modal.fire({
          icon: "error",
          title: "Gagal menyimpan add-on",
          text: error.response?.data?.message ?? "Terjadi kesalahan server.",
        });
      }
    } finally {
      state.isLoading = false;
    }
  };

  return { openDrawer, openEditDrawer, closeDrawer, submitAddon };
}
