import route from "../../../lib/route";
import { Modal, Toast } from "../../../lib/sweetalert";
import { z } from "zod";

const packageSchema = z.object({
  category_id: z.union([z.string().min(1, "Kategori wajib dipilih."), z.number().min(1, "Kategori wajib dipilih.")]),
  name: z.string().min(3, "Nama paket minimal 3 karakter.").max(100, "Maksimal 100 karakter."),
  is_active: z.boolean(),
  features: z.array(z.string()).optional(),
});

export default function usePackageForm({ state, table }) {
  const resetPackageForm = () => {
    state.isEdit = false;
    state.packageId = null;
    state.form.category_id = "";
    state.form.name = "";
    state.form.is_active = true;
    state.form.features = ["", ""];
    state.errors = {};
  };

  const openDrawer = () => {
    resetPackageForm();
    state.isDrawerOpen = true;
  };
  const closeDrawer = () => {
    state.isDrawerOpen = false;
    setTimeout(() => resetPackageForm(), 500);
  };

  const editPackage = (pkg) => {
    resetPackageForm();
    state.isEdit = true;
    state.packageId = pkg.slug;
    state.form.category_id = pkg.category_id;
    state.form.name = pkg.name;
    state.form.is_active = pkg.is_active;
    state.form.features = pkg.features?.map((f) => f.description) ?? ["", ""];
    if (state.form.features.length === 0) state.form.features = ["", ""];
    state.isDrawerOpen = true;
  };

  const addFeature = () => state.form.features.push("");
  const removeFeature = (index) => {
    state.form.features.splice(index, 1);
    if (state.form.features.length === 0) state.form.features.push("");
  };

  const submitPackage = async () => {
    state.isLoading = true;
    state.errors = {};
    const filteredFeatures = state.form.features.filter((f) => f.trim() !== "");
    const validation = packageSchema.safeParse({ ...state.form, features: filteredFeatures });
    if (!validation.success) {
      validation.error.issues.forEach((issue) => {
        if (!state.errors[issue.path[0]]) state.errors[issue.path[0]] = issue.message;
      });
      state.isLoading = false;
      return;
    }
    const payload = { ...state.form, features: filteredFeatures };
    const url = state.isEdit
      ? route("backdoor.data-master.package.update", state.packageId)
      : route("backdoor.data-master.package.store");
    const method = state.isEdit ? "put" : "post";
    try {
      const response = await window.axios[method](url, payload);
      closeDrawer();
      table.reload();
      Toast.fire({ icon: "success", title: response.data.message });
    } catch (error) {
      if (error.response?.status === 422) {
        const errs = error.response.data.errors;
        for (const key in errs) state.errors[key] = errs[key][0];
      } else {
        Modal.fire({
          icon: "error",
          title: "Gagal menyimpan paket",
          text: error.response?.data?.message ?? "Terjadi kesalahan server.",
        });
      }
    } finally {
      state.isLoading = false;
    }
  };

  return {
    openDrawer,
    closeDrawer,
    editPackage,
    addFeature,
    removeFeature,
    submitPackage,
  };
}
