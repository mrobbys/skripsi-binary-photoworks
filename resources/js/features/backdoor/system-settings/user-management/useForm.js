import { z } from "zod";
import { Toast } from "@/lib/sweetalert";
import route from "@/lib/route";
import axiosInstance from "@/lib/axiosInstance";
import { getFieldError } from "@/lib/zodHelper";

const userSchema = z.object({
  name: z
    .string()
    .min(1, "Nama lengkap wajib diisi")
    .min(3, "Nama minimal 3 karakter")
    .max(255, "Nama maksimal 255 karakter")
    .regex(/^[a-zA-Z\s.,']+$/, "Nama hanya boleh mengandung huruf, spasi, titik, koma, dan petik"),
  email: z
    .string()
    .min(1, "Email wajib diisi")
    .max(255, "Email maksimal 255 karakter")
    .email("Format email tidak valid"),
  phone: z
    .string()
    .min(1, "Nomor HP wajib diisi")
    .regex(/^62[0-9]+$/, "Nomor telepon harus berawalan 62")
    .min(10, "Nomor HP minimal 10 digit")
    .max(14, "Nomor HP maksimal 14 digit"),
  role: z
    .string()
    .min(1, "Role wajib dipilih"),
});

export default function useForm({ Alpine, state, table }) {
  Alpine.effect(() => {
    const allFilled = Boolean(
      state.form.name &&
      state.form.email &&
      state.form.phone &&
      state.form.role
    );
    const noErrors = !state.errors.name && !state.errors.email && !state.errors.phone && !state.errors.role;
    state.isFormValid = Boolean(allFilled && noErrors);
  });

  const resetForm = () => {
    state.isEdit = false;
    state.userId = null;
    state.form = { name: "", email: "", phone: "", role: "" };
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

  const editUser = (user) => {
    resetForm();
    state.isEdit = true;
    state.userId = user.id;
    state.form.name = user.name;
    state.form.email = user.email;
    state.form.phone = user.phone === "-" ? "" : user.phone;
    state.form.role = user.role;
    state.isDrawerOpen = true;
  };

  const validateField = (field, overrideValue = undefined) => {
    state.dismissedErrors[field] = true;
    const formToValidate = {
      ...state.form,
      ...(overrideValue !== undefined ? { [field]: overrideValue } : {}),
    };
    const result = userSchema.safeParse(formToValidate);
    if (!result.success) {
      state.errors[field] = getFieldError(result, field);
    } else {
      state.errors[field] = null;
    }
  };

  const submitUser = async () => {
    const result = userSchema.safeParse(state.form);
    if (!result.success) {
      state.errors = {
        name: getFieldError(result, "name"),
        email: getFieldError(result, "email"),
        phone: getFieldError(result, "phone"),
        role: getFieldError(result, "role"),
      };
      return;
    }

    state.isLoading = true;

    const url = state.isEdit
      ? route("backdoor.system-settings.users.update", state.userId)
      : route("backdoor.system-settings.users.store");
    const method = state.isEdit ? "put" : "post";

    try {
      const res = await axiosInstance[method](url, result.data);
      closeDrawer();
      table.reload();
      Toast.fire({ icon: "success", title: res.data.message });
    } catch (error) {
      if (error.response?.status === 422) {
        state.errors = error.response.data.errors ?? {};
      } else {
        Toast.fire({
          icon: "error",
          title: "Terjadi kesalahan pada server, silakan coba beberapa saat lagi",
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
    editUser,
    validateField,
    submitUser,
  };
}
