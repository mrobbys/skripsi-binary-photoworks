import { z } from 'zod';
import { getFieldError } from '@/lib/zodHelper';

export default function Login(Alpine) {
  // schema validasi zod
  const loginSchema = z.object({
    email: z
      .string()
      .min(1, 'Email harus diisi')
      .max(50, 'Email maksimal 50 karakter')
      .email('Format email tidak valid'),
    password: z
      .string()
      .min(1, 'Password harus diisi')
      .min(8, 'Password harus terdiri dari minimal 8 karakter')
      .max(50, 'Password maksimal 50 karakter')
      .regex(/[A-Z]/, 'Password harus mengandung setidaknya satu huruf besar')
      .regex(/[a-z]/, 'Password harus mengandung setidaknya satu huruf kecil')
      .regex(/[0-9]/, 'Password harus mengandung setidaknya satu angka'),
    remember: z.boolean().optional(),
  });

  // ambil old input
  const oldEmailInput = document.querySelector('input[name="email"]');

  // state
  const state = Alpine.reactive({
    form: {
      email: oldEmailInput ? oldEmailInput.value : '',
      password: '',
      remember: false,
    },
    isLoading: false,
    errors: {},
    dismissedErrors: {},
    isFormValid: false,
  });

  // cek form is valid
  Alpine.effect(() => {
    const allFilled = state.form.email && state.form.password;
    const noErrors = !state.errors.email && !state.errors.password;
    state.isFormValid = Boolean(allFilled && noErrors);
  });

  const validateField = (field) => {
    state.dismissedErrors[field] = true;
    const result = loginSchema.safeParse(state.form);

    if (!result.success) {
      state.errors[field] = getFieldError(result, field);
    } else {
      state.errors[field] = null;
    }
  };

  const validateForm = () => {
    const result = loginSchema.safeParse(state.form);

    if (!result.success) {
      state.errors = {
        email: getFieldError(result, 'email'),
        password: getFieldError(result, 'password'),
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

  return {
    state,
    validateField,
    submitForm,
  };
}
