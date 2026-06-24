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

  const setPackageInfo = (pkg) => {
    state.packageInfo = pkg;
    state.packageId = pkg.slug;
  };

  const openDrawer = () => {
    resetPackageForm();
    state.isDrawerOpen = true;
  };

  const openEditDrawer = (pkg) => {
    resetPackageForm();
    state.isEdit = true;
    state.packageId = pkg?.slug ?? window.__packageSlug;
    state.form.category_id = pkg?.category_id ?? "";
    state.form.name = pkg?.name ?? "";
    state.form.is_active = pkg?.is_active ?? true;
    state.form.features = pkg.features
      ? pkg.features.map((f) => (typeof f === "object" && f !== null ? f.description : f))
      : ["", ""];
    if (state.form.features.length === 0) state.form.features = ["", ""];
    state.isDrawerOpen = true;
  };

  const closeDrawer = () => {
    state.isDrawerOpen = false;
    setTimeout(() => resetPackageForm(), 500);
  };

  const addFeature = () => state.form.features.push("");
  const removeFeature = (index) => {
    state.form.features.splice(index, 1);
    if (state.form.features.length === 0) state.form.features.push("");
  };

  const handleEditSuccess = async () => {
    if (window.__packageSlug) {
      // Jika di halaman detail paket (ShowPackage), perbarui data info paket & URL
      const response2 = await window.axios.get(route("backdoor.data-master.package.show", state.packageId));
      const pkg = response2.data.data;

      setPackageInfo({
        slug: pkg.slug,
        category_id: pkg.category_id,
        category_name: pkg.category?.name ?? "",
        name: pkg.name,
        is_active: pkg.is_active,
        features: pkg.features?.map((f) => (typeof f === "string" ? f : f.description)) ?? [],
      });

      if (pkg.slug !== state.packageId) {
        state.packageId = pkg.slug;
        window.history.replaceState(null, "", route("backdoor.data-master.package.show", pkg.slug));
        window.__packageSlug = pkg.slug; // keep global var updated
      }
    } else {
      // Jika di halaman list paket (Package), reload datatable
      table?.reload();
    }
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
      Toast.fire({ icon: "success", title: response.data.message });

      if (state.isEdit) {
        await handleEditSuccess();
      } else {
        // Jika tambah paket baru (only create), redirect ke halaman detail paket
        window.location.href = route("backdoor.data-master.package.show", response.data.data.slug);
      }
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
    openEditDrawer,
    closeDrawer,
    addFeature,
    removeFeature,
    submitPackage,
    setPackageInfo,
  };
}
