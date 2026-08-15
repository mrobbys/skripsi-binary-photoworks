import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";
import { z } from "zod";
import { getFieldError } from "@/lib/zodHelper";
import axiosInstance from "@/lib/axiosInstance";

const addonSchema = z.object({
  name: z.string().min(1, "Nama add-on wajib diisi").max(100, "Nama add-on maksimal 100 karakter"),
  price: z
    .union([z.string(), z.number()])
    .refine((val) => !isNaN(parseInt(val)) && parseInt(val) >= 0, "Harga harus berupa angka dan tidak boleh negatif"),
  description: z.string().min(1, "Deskripsi wajib diisi").max(255, "Deskripsi maksimal 255 karakter"),
  has_quantity: z.boolean(),
  is_active: z.boolean(),
});

export default function useAddonForm({ Alpine, state, table }) {
  Alpine.effect(() => {
    const allFilled = Boolean(
      state.form.name &&
      state.form.price !== "" &&
      state.form.price !== null &&
      state.form.description &&
      state.form.has_quantity !== undefined &&
      state.form.is_active !== undefined
    );
    const noErrors =
      !state.errors.name &&
      !state.errors.price &&
      !state.errors.description &&
      !state.errors.has_quantity &&
      !state.errors.is_active;

    state.isFormValid = Boolean(allFilled && noErrors);
  });

  const resetForm = () => {
    state.isEdit = false;
    state.addonId = null;
    state.form.name = "";
    state.form.price = "";
    state.form.description = "";
    state.form.has_quantity = false;
    state.form.is_active = true;
    state.errors = {};
    state.dismissedErrors = {};
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
    state.form.has_quantity = Boolean(addon.has_quantity);
    state.form.is_active = Boolean(addon.is_active);
    state.isDrawerOpen = true;
  };

  const closeDrawer = () => {
    state.isDrawerOpen = false;
    setTimeout(() => resetForm(), 500);
  };

  const validateField = (field) => {
    state.dismissedErrors[field] = true;
    const result = addonSchema.safeParse(state.form);
    state.errors[field] = result.success ? null : getFieldError(result, field);
  };

  const submitAddon = async () => {
    const result = addonSchema.safeParse(state.form);
    if (!result.success) {
      state.errors = {
        name: getFieldError(result, "name"),
        price: getFieldError(result, "price"),
        description: getFieldError(result, "description"),
        has_quantity: getFieldError(result, "has_quantity"),
        is_active: getFieldError(result, "is_active"),
      };
      return;
    }

    state.isLoading = true;

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
      const response = await axiosInstance[method](url, payload);

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

  return { openDrawer, openEditDrawer, closeDrawer, submitAddon, validateField };
}
