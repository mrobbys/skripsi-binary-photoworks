import axiosInstance from "@/lib/axiosInstance";
import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";
import { z } from "zod";
import { getFieldError } from "@/lib/zodHelper";

const profileSchema = z.object({
  name: z.string().trim().min(3, "Nama minimal 3 karakter.").max(255, "Maksimal 255 karakter."),
  email: z.string().email("Format email tidak valid.").max(255, "Maksimal 255 karakter."),
  phone: z
    .string()
    .trim()
    .regex(/^62[0-9]+$/, "Nomor telepon harus berawalan 62.")
    .min(10, "Nomor telepon minimal 10 karakter.")
    .max(14, "Nomor telepon maksimal 14 karakter."),
});

export default function useUpdateProfile({ state }) {
  const validateField = (field) => {
    state.dismissedErrors[field] = true;
    const result = profileSchema.safeParse({
      name: state.name,
      email: state.email,
      phone: state.phone,
    });
    
    if (!result.success) {
      state.errors[field] = getFieldError(result, field);
    } else {
      state.errors[field] = null;
    }
  };

  const submitUpdateProfile = async () => {
    // Cegah submit jika data profile tidak berubah
    if (!state.hasChanges) return;

    state.isUpdatingProfile = true;
    state.errors = {};

    // Validasi zod
    const parsed = profileSchema.safeParse({
      name: state.name,
      email: state.email,
      phone: state.phone,
    });

    if (!parsed.success) {
      state.errors = z.flattenError(parsed.error).fieldErrors;
      state.isUpdatingProfile = false;
      return;
    }

    try {
      const res = await axiosInstance.patch(route("frontdoor.dashboard.profile.update"), parsed.data);

      if (!res.data.success) {
        throw new Error(res.data.message || "Gagal memperbarui data.");
      }

      Toast.fire({ icon: "success", title: res.data.message });

      // Sinkronisasi data asli agar tombol simpan dinonaktifkan
      state.originalData = { ...parsed.data };
    } catch (err) {
      if (err?.response?.status === 422) {
        state.errors = err.response.data.errors;
        Toast.fire({ icon: "error", title: err?.response?.data?.message ?? "Gagal memperbarui profil." });
      } else {
        Toast.fire({ icon: "error", title: "Gagal memperbarui profil. Silakan coba lagi." });
      }
    } finally {
      state.isUpdatingProfile = false;
    }
  };

  return { submitUpdateProfile, validateField };
}
