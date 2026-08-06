import axiosInstance from "@/lib/axiosInstance";
import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";
import { z } from "zod";
import { getFieldError } from "@/lib/zodHelper";
import { passwordRules } from "@/utils/passwordRules";

const passwordSchema = z
  .object({
    old_password: z.string().min(1, "Password lama wajib diisi."),
    password: z.string().min(1, "Password baru wajib diisi."),
    password_confirmation: z.string(),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: "Konfirmasi password tidak cocok.",
    path: ["password_confirmation"],
  });

export default function useChangePassword({ state }) {
  const clearPasswordState = () => {
    state.passwordForm = { old_password: "", password: "", password_confirmation: "" };
    state.errors = {};
    state.passwordErrors = [];
  };

  const openPasswordDrawer = () => {
    clearPasswordState();
    state.isPasswordDrawerOpen = true;
  };

  const closePasswordDrawer = () => {
    state.isPasswordDrawerOpen = false;
    setTimeout(clearPasswordState, 500);
  };

  // Validasi real-time per field
  const validateField = (field) => {
    state.dismissedErrors[field] = true;

    if (field === "password") {
      state.passwordErrors = passwordRules
        .filter((rule) => !rule.test(state.passwordForm.password))
        .map((rule) => rule.msg);

      // Re-validasi konfirmasi jika sudah terisi
      if (state.passwordForm.password_confirmation) {
        validateField("password_confirmation");
      }
      return;
    }

    const result = passwordSchema.safeParse(state.passwordForm);
    state.errors[field] = !result.success ? getFieldError(result, field) : null;
  };

  const submitChangePassword = async () => {
    // Validasi strength sebelum submit
    state.passwordErrors = passwordRules
      .filter((rule) => !rule.test(state.passwordForm.password))
      .map((rule) => rule.msg);

    const result = passwordSchema.safeParse(state.passwordForm);

    if (!result.success || state.passwordErrors.length > 0) {
      state.errors = {
        old_password: getFieldError(result, "old_password"),
        password_confirmation: getFieldError(result, "password_confirmation"),
      };
      return;
    }

    state.isChangingPassword = true;
    state.errors = {};

    try {
      const res = await axiosInstance.patch(route("frontdoor.dashboard.profile.password"), result.data);

      if (!res.data.success) {
        throw new Error(res.data.message || "Gagal mengganti password.");
      }

      Toast.fire({ icon: "success", title: res.data.message });
      closePasswordDrawer();
    } catch (err) {
      if (err?.response?.status === 422) {
        state.errors = err.response.data.errors;
      } else {
        Toast.fire({ icon: "error", title: "Gagal mengganti password. Silakan coba lagi." });
      }
    } finally {
      state.isChangingPassword = false;
    }
  };

  return { openPasswordDrawer, closePasswordDrawer, submitChangePassword, validateField };
}
