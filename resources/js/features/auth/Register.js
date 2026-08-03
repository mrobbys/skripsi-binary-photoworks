import { z } from 'zod';
import { getFieldError } from '@/lib/zodHelper';
import { passwordRules } from '@/utils/passwordRules';

export default function Register(Alpine) {

  const registerSchema = z.object({
    name: z.string()
      .min(1, 'Nama Lengkap harus diisi')
      .min(3, 'Nama Lengkap minimal 3 karakter')
      .max(255, 'Nama Lengkap maksimal 255 karakter')
      .regex(/^[a-zA-Z\s.,']+$/, 'Nama hanya boleh mengandung huruf, spasi, titik, koma, dan tanda petik satu'),
    email: z.string()
      .min(1, 'Email harus diisi')
      .max(255, 'Email maksimal 255 karakter')
      .email('Format email tidak valid'),
    phone: z.string()
      .min(1, 'Nomor telepon harus diisi')
      .regex(/^62[0-9]+$/, 'Nomor telepon harus diawali 62')
      .min(10, 'Nomor telepon minimal 10 digit')
      .max(14, 'Nomor telepon maksimal 14 digit'),
    password: z.string().min(1, 'Password harus diisi'),
    password_confirmation: z.string().min(1, 'Konfirmasi password harus diisi'),
  }).superRefine((data, ctx) => {
    if (data.password !== data.password_confirmation) {
      ctx.addIssue({
        path: ['password_confirmation'],
        code: z.ZodIssueCode.custom,
        message: 'Konfirmasi password tidak cocok',
      });
    }
  });

  // ambil old input
  const oldNameInput = document.querySelector('input[name="name"]');
  const oldEmailInput = document.querySelector('input[name="email"]');
  const oldPhoneInput = document.querySelector('input[name="phone"]');

  const state = Alpine.reactive({
    form: {
      name: oldNameInput ? oldNameInput.value : '',
      email: oldEmailInput ? oldEmailInput.value : '',
      phone: oldPhoneInput ? oldPhoneInput.value : '',
      password: '',
      password_confirmation: '',
    },
    isLoading: false,
    errors: {},
    passwordErrors: [],
    dismissedErrors: {},
    isFormValid: false,
  });

  // Computed isFormValid
  Alpine.effect(() => {
    const allFilled = state.form.name && state.form.email && state.form.phone && state.form.password && state.form.password_confirmation;
    const noErrors = !state.errors.name && !state.errors.email && !state.errors.phone
      && state.passwordErrors.length === 0 && !state.errors.password_confirmation;
    state.isFormValid = Boolean(allFilled && noErrors);
  });

  const validateField = (field) => {
    state.dismissedErrors[field] = true;
    const result = registerSchema.safeParse(state.form);

    if (field === 'password') {
      // Update list error password
      state.passwordErrors = passwordRules
        .filter(rule => !rule.test(state.form.password))
        .map(rule => rule.msg);
      // Validasi ulang password_confirmation jika password berubah
      if (state.form.password_confirmation) {
          validateField('password_confirmation');
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
    // Validasi password rules
    state.passwordErrors = passwordRules
      .filter(rule => !rule.test(state.form.password))
      .map(rule => rule.msg);

    const result = registerSchema.safeParse(state.form);
    if (!result.success) {
      state.errors = {
        name: getFieldError(result, 'name'),
        email: getFieldError(result, 'email'),
        phone: getFieldError(result, 'phone'),
        password_confirmation: getFieldError(result, 'password_confirmation'),
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