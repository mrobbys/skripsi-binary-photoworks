import { z } from "zod";
import { getFieldError } from "@/lib/zodHelper";

export default function ForgotPassword(Alpine) {
  const schema = z.object({
    email: z
      .string()
      .min(1, "Email harus diisi")
      .max(255, "Email maksimal 255 karakter")
      .email("Format email tidak valid"),
  });

  // ambil old input
  const oldEmailInput = document.querySelector('input[name="email"]');

  const state = Alpine.reactive({
    form: {
      email: oldEmailInput ? oldEmailInput.value : "",
    },
    isLoading: false,
    errors: {},
    dismissedErrors: {},
    isFormValid: false,
  });

  // cek form is valid
  Alpine.effect(() => {
    const allFilled = state.form.email;
    const noErrors = !state.errors.email;
    state.isFormValid = Boolean(allFilled && noErrors);
  });

  const validateField = (field) => {
    state.dismissedErrors[field] = true;
    const result = schema.safeParse(state.form);

    if (!result.success) {
      state.errors[field] = getFieldError(result, field);
    } else {
      state.errors[field] = null;
    }
  };

  const validateForm = () => {
    const result = schema.safeParse(state.form);

    if (!result.success) {
      state.errors = {
        email: getFieldError(result, 'email'),
      };
      return false;
    }

    state.errors = {};
    return true;
  };

  const submitForm = (e) => {
    const isValid = validateForm();

    if (!isValid) {
      e.preventDefault();
      return;
    }

    state.isLoading = true;
  };

  return { state, validateField, submitForm };
}
