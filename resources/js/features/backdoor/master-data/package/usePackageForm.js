import route from "@/lib/route";
import { Modal, Toast } from "@/lib/sweetalert";
import { z } from "zod";
import axiosInstance from "@/lib/axiosInstance";

const packageSchema = z.object({
  category_id: z.union([z.string().min(1, "Kategori wajib dipilih."), z.number().min(1, "Kategori wajib dipilih.")]),
  name: z.string().min(3, "Nama paket minimal 3 karakter.").max(100, "Maksimal 100 karakter."),
  description: z.string().min(3, "Deskripsi minimal 3 karakter.").max(500, "Deskripsi maksimal 500 karakter."),
  is_active: z.boolean(),
  features: z.array(z.string()).optional(),
});

export default function usePackageForm({ state, table }) {
  const resetPackageForm = () => {
    state.isEdit = false;
    state.packageId = null;
    state.form.category_id = "";
    state.form.name = "";
    state.form.description = "";
    state.form.is_active = true;
    state.form.features = ["", ""];
    state.errors = {};
    state.pendingImageFile = null;
    // Kirim event untuk reset FilePond instance di DOM
    document.dispatchEvent(new CustomEvent("package:reset-filepond"));
  };



  const openDrawer = () => {
    resetPackageForm();
    state.isDrawerOpen = true;
  };

  const openEditDrawer = (pkg) => {
    resetPackageForm();
    state.isEdit = true;
    state.packageId = pkg?.slug ?? state.packageSlug;
    state.form.category_id = pkg?.category_id ?? "";
    state.form.name = pkg?.name ?? "";
    state.form.description = pkg?.description ?? "";
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
    if (state.packageSlug) {
      // Jika di halaman detail paket (ShowPackage), perbarui data info paket & URL
      const response2 = await axiosInstance.get(route("backdoor.data-master.package.info", state.packageId));
      const pkg = response2.data.data;

      state.packageInfo = pkg;

      if (pkg.slug !== state.packageId) {
        state.packageId = pkg.slug;
        window.history.replaceState(null, "", route("backdoor.data-master.package.show", pkg.slug));
        state.packageSlug = pkg.slug; // update alpine state
      }
    } else {
      // Jika di halaman list paket (Package), reload datatable
      table?.reload();
    }
  };

  const submitPackage = async () => {
    state.isLoading = true;
    state.errors = {};

    console.log(state);
    
    // validasi input gambar wajib saat create
    if (!state.isEdit && !state.pendingImageFile) {
      state.errors.image = "Gambar paket wajib diunggah.";
    }
    
    const filteredFeatures = state.form.features.filter((f) => f.trim() !== "");
    const validation = packageSchema.safeParse({ ...state.form, features: filteredFeatures });
    if (!validation.success) {
      validation.error.issues.forEach((issue) => {
        if (!state.errors[issue.path[0]]) state.errors[issue.path[0]] = issue.message;
      });
      state.isLoading = false;
      return;
    }

    if (Object.keys(state.errors).length > 0) {
      state.isLoading = false;
      return;
    }

    // eslint-disable-next-line no-undef
    const formData = new FormData();
    formData.append("category_id", state.form.category_id);
    formData.append("name", state.form.name);
    formData.append("description", state.form.description ?? "");
    formData.append("is_active", state.form.is_active ? "1" : "0");

    // Append array features ke FormData
    filteredFeatures.forEach((feature, index) => {
      formData.append(`features[${index}]`, feature);
    });

    if (state.pendingImageFile) {
      formData.append("image", state.pendingImageFile);
    }

    if (state.isEdit) {
      formData.append("_method", "PUT");
    }

    const url = state.isEdit
      ? route("backdoor.data-master.package.update", state.packageId)
      : route("backdoor.data-master.package.store");

    try {
      // WAJIB gunakan POST untuk multipart/form-data di Laravel (dikawinkan dengan _method=PUT)
      const response = await axiosInstance.post(url, formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
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
  };
}
