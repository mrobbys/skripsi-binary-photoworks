import axiosInstance from "@/lib/axiosInstance";
import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";
import { z } from "zod";

const passwordSchema = z
  .object({
    old_password: z.string().min(1, "Password lama wajib diisi."),
    password: z
      .string()
      .min(8, "Password minimal 8 karakter.")
      .regex(/[a-z]/, "Password harus mengandung huruf kecil.")
      .regex(/[A-Z]/, "Password harus mengandung huruf besar.")
      .regex(/[0-9]/, "Password harus mengandung angka."),
    password_confirmation: z.string(),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: "Konfirmasi password tidak cocok.",
    path: ["password_confirmation"],
  });

export default function useChangePassword({ state }) {
  const clearPasswordState = () => {
    state.passwordForm = { old_password: "", password: "", password_confirmation: "" };
    state.passwordErrors = {};
  };

  const openPasswordDrawer = () => {
    clearPasswordState();
    state.isPasswordDrawerOpen = true;
  };

  const closePasswordDrawer = () => {
    state.isPasswordDrawerOpen = false;
    setTimeout(clearPasswordState, 500);
  };

  const submitChangePassword = async () => {
    state.isChangingPassword = true;
    state.passwordErrors = {};

    // Validasi zod
    const parsed = passwordSchema.safeParse({
      old_password: state.passwordForm.old_password,
      password: state.passwordForm.password,
      password_confirmation: state.passwordForm.password_confirmation,
    });
    
    if (!parsed.success) {
      state.passwordErrors = z.flattenError(parsed.error).fieldErrors;
      state.isChangingPassword = false;
      return;
    }

    try {
      const res = await axiosInstance.patch(route("frontdoor.dashboard.profile.password"), parsed.data);

      if (!res.data.success) {
        throw new Error(res.data.message || "Gagal mengganti password.");
      }

      Toast.fire({ icon: "success", title: res.data.message });
      closePasswordDrawer();
    } catch (err) {
      if (err.response?.status === 422) {
        state.passwordErrors = err.response.data.errors;
        return;
      }
      const msg = err?.response?.data?.message || err.message;
      Toast.fire({ icon: "error", title: msg || "Terjadi kesalahan." });
    } finally {
      state.isChangingPassword = false;
    }
  };

  return { openPasswordDrawer, closePasswordDrawer, submitChangePassword };
}
