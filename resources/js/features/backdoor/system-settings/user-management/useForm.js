import { z } from "zod";
import { Toast, Modal } from "@/lib/sweetalert";
import route from "@/lib/route";
import axios from "@/lib/axiosInstance";

const schema = z.object({
  name: z.string().min(1, "Nama lengkap wajib diisi.").min(3, "Nama minimal 3 karakter.").max(255),
  email: z.string().min(1, "Email wajib diisi.").email("Format email tidak valid."),
  phone: z
    .string()
    .min(1, "Nomor HP wajib diisi.")
    .regex(/^62[0-9]+$/, "Nomor telepon harus berawalan 62.")
    .min(10, "Nomor HP minimal 10 digit.")
    .max(14, "Nomor HP maksimal 14 digit."),
  role: z.preprocess(
    (val) => {
      if (typeof val === "object" && val !== null) {
        return val.value || "";
      }
      return String(val || "");
    },
    z.string().min(1, "Role wajib dipilih."),
  ),
});

export default function useForm({ state, table }) {
  const resetForm = () => {
    state.isEdit = false;
    state.userId = null;
    state.errors = {};
    state.form = { name: "", email: "", phone: "", role: "" };
  };

  const openDrawer = () => {
    resetForm();
    state.isDrawerOpen = true;
  };

  const closeDrawer = () => {
    state.isDrawerOpen = false;
    resetForm();
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

  const submitUser = async () => {
    state.errors = {};
    state.isLoading = true;

    // Validasi Zod client-side
    const parsed = schema.safeParse({
      name: state.form.name,
      email: state.form.email,
      phone: state.form.phone,
      role: state.form.role,
    });

    if (!parsed.success) {
      state.errors = z.flattenError(parsed.error).fieldErrors;
      state.isLoading = false;
      return;
    }

    const url = state.isEdit
      ? route("backdoor.system-settings.users.update", state.userId)
      : route("backdoor.system-settings.users.store");
    const method = state.isEdit ? "put" : "post";

    try {
      const res = await axios[method](url, parsed.data);

      if (res.data.status !== 'success') {
        throw new Error(res.data.message || "Gagal menyimpan user.");
      }

      closeDrawer();
      table.reload();
      Toast.fire({ icon: "success", title: res.data.message });
    } catch (error) {
      if (error.response?.status === 422) {
        state.errors = error.response.data.errors ?? {};
        return;
      }
      Modal.fire({
        icon: "error",
        title: "Gagal menyimpan user",
        text: error.response?.data?.message ?? "Terjadi kesalahan server.",
      });
    } finally {
      state.isLoading = false;
    }
  };

  return { openDrawer, closeDrawer, editUser, submitUser };
}
