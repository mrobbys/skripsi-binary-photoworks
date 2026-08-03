import { z } from "zod";
import { getFieldError } from "@/lib/zodHelper";
import { passwordRules } from "@/utils/passwordRules";

export default function ResetPassword(Alpine) {

  const resetSchema = z
    .object({
      email: z
        .string()
        .min(1, "Email harus diisi")
        .max(255, "Email maksimal 255 karakter")
        .email("Format email tidak valid"),
      password: z.string().min(1, "Password harus diisi"),
      password_confirmation: z.string().min(1, "Konfirmasi password harus diisi"),
    })
    .superRefine((data, ctx) => {
      if (data.password !== data.password_confirmation) {
        ctx.addIssue({
          path: ["password_confirmation"],
          code: z.ZodIssueCode.custom,
          message: "Konfirmasi password tidak cocok",
        });
      }
    });

  // ambil old input email
  const oldEmailInput = document.querySelector('input[name="email"]');

  const state = Alpine.reactive({
    form: {
      email: oldEmailInput ? oldEmailInput.value : "",
      password: "",
      password_confirmation: "",
    },
    isLoading: false,
    errors: {},
    passwordErrors: [],
    dismissedErrors: {},
    isFormValid: false,
  });

  // Computed isFormValid
  Alpine.effect(() => {
    const allFilled = state.form.email && state.form.password && state.form.password_confirmation;
    const noErrors = !state.errors.email && state.passwordErrors.length === 0 && !state.errors.password_confirmation;
    state.isFormValid = Boolean(allFilled && noErrors);
  });

  const validateField = (field) => {
    state.dismissedErrors[field] = true;
    const formData = state.form;
    const result = resetSchema.safeParse(formData);

    if (field === "password") {
      // Update list error password
      state.passwordErrors = passwordRules.filter((rule) => !rule.test(state.form.password)).map((rule) => rule.msg);
      // Validasi ulang password_confirmation jika password berubah
      if (state.form.password_confirmation) {
        validateField("password_confirmation");
      }
      return;
    }

    if (!result.success) {
      state.errors[field] = getFieldError(result, field);
    } else {
      state.errors[field] = null;
    }
  };

  const validateForm = () => {
    const formData = state.form;

    // Validasi password rules
    state.passwordErrors = passwordRules.filter((rule) => !rule.test(state.form.password)).map((rule) => rule.msg);

    const result = resetSchema.safeParse(formData);
    if (!result.success) {
      state.errors = {
        email: getFieldError(result, "email"),
        password_confirmation: getFieldError(result, "password_confirmation"),
      };
      return false;
    }

    if (state.passwordErrors.length > 0) return false;

    state.errors = {};
    return true;
  };

  const submitForm = (e) => {
    if (!validateForm()) {
      e.preventDefault();
      return;
    }
    state.isLoading = true;
  };

  return { state, validateField, submitForm };
}
