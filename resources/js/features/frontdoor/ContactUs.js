import route from "@/lib/route";
import axiosInstance from "@/lib/axiosInstance";
import { Toast } from "@/lib/sweetalert";
import { z } from "zod";
import { getFieldError } from "@/lib/zodHelper";

export default function ContactUs(Alpine) {
  // Ambil old input dari DOM (jika ada nilai dari sesi sebelumnya)
  const oldNamaInput = document.querySelector('input[name="nama"]');
  const oldEmailInput = document.querySelector('input[name="email"]');
  const oldSubjekInput = document.querySelector('input[name="subjek"]');
  const oldPesanInput = document.querySelector('textarea[name="pesan"]');

  const state = Alpine.reactive({
    form: {
      nama: oldNamaInput ? oldNamaInput.value : '',
      email: oldEmailInput ? oldEmailInput.value : '',
      subjek: oldSubjekInput ? oldSubjekInput.value : '',
      pesan: oldPesanInput ? oldPesanInput.value : '',
    },
    isLoading: false,
    errors: {},
    dismissedErrors: {},
  });

  const schema = z.object({
    nama: z.string().min(1, 'Nama lengkap wajib diisi.').max(100, 'Maksimal 100 karakter.'),
    email: z.string().min(1, 'Email wajib diisi.').email('Format email tidak valid.').max(150, 'Maksimal 150 karakter.'),
    subjek: z.string().min(1, 'Subjek wajib diisi.').max(150, 'Maksimal 150 karakter.'),
    pesan: z.string().min(1, 'Pesan wajib diisi.').max(2000, 'Maksimal 2000 karakter.'),
  });

  // Validasi individual per field (dipanggil saat onInput)
  const validateField = (field) => {
    state.dismissedErrors[field] = true;
    const result = schema.safeParse(state.form);

    if (!result.success) {
      state.errors[field] = getFieldError(result, field);
    } else {
      state.errors[field] = null;
    }
  };

  const submit = async () => {
    // Validasi seluruh form sebelum submit
    const result = schema.safeParse(state.form);
    if (!result.success) {
      state.errors = {
        nama: getFieldError(result, 'nama'),
        email: getFieldError(result, 'email'),
        subjek: getFieldError(result, 'subjek'),
        pesan: getFieldError(result, 'pesan'),
      };
      return;
    }

    state.isLoading = true;
    
    try {
      const res = await axiosInstance.post(route('contact.store'), state.form);

      Toast.fire({ icon: "success", title: res.data.message });
      resetForm();
    } catch (error) {
      if (error.response?.status === 422) {
        state.errors = error.response.data.errors;
      }
    } finally {
      state.isLoading = false;
    }
  };

  const resetForm = () => {
    state.form.nama = '';
    state.form.email = '';
    state.form.subjek = '';
    state.form.pesan = '';
    state.errors = {};
  };

  return { state, submit, validateField };
}
