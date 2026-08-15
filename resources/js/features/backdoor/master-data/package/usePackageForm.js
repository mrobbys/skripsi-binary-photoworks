import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";
import { z } from "zod";
import { getFieldError } from "@/lib/zodHelper";
import axiosInstance from "@/lib/axiosInstance";
import useFilePond from "@/lib/useFilePond";

const packageSchema = z.object({
  category_id: z.coerce.string().min(1, "Kategori wajib dipilih"),
  name: z.string().min(3, "Nama paket minimal 3 karakter").max(100, "Maksimal 100 karakter"),
  description: z.string().min(3, "Deskripsi minimal 3 karakter").max(500, "Deskripsi maksimal 500 karakter"),
  is_active: z.boolean(),
  features: z.array(z.string()).optional(),
});

export default function usePackageForm({ Alpine, state, table }) {
  const { initFilePond, resetFilePond } = useFilePond({
    onFileChange: (file) => {
      state.pendingImageFile = file;
      validateField("image");
    },
  });

  Alpine.effect(() => {
    const hasImage = state.isEdit || Boolean(state.pendingImageFile);
    const allFilled = Boolean(state.form.category_id && state.form.name && state.form.description && hasImage);
    const noErrors =
      !state.errors.category_id && !state.errors.name && !state.errors.description && !state.errors.image;

    state.isFormValid = Boolean(allFilled && noErrors);
  });

  const resetPackageForm = () => {
    state.isEdit = false;
    state.packageId = null;
    state.form.category_id = "";
    state.form.name = "";
    state.form.description = "";
    state.form.is_active = true;
    state.form.features = ["", ""];
    state.errors = {};
    state.dismissedErrors = {};
    state.pendingImageFile = null;
    resetFilePond();
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
    state.form.is_active = Boolean(pkg?.is_active ?? true);
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

  const validateField = (field, value = undefined) => {
    if (field === "image") {
      if (!state.isEdit && !state.pendingImageFile) {
        state.errors.image = "Gambar paket wajib diunggah";
      } else {
        state.errors.image = null;
      }
      return;
    }

    if (value !== undefined) {
      state.form[field] = value;
    }

    state.dismissedErrors[field] = true;
    const filteredFeatures = state.form.features.filter((f) => f.trim() !== "");
    const result = packageSchema.safeParse({ ...state.form, features: filteredFeatures });
    state.errors[field] = result.success ? null : getFieldError(result, field);
  };

  const handleEditSuccess = async () => {
    if (state.packageSlug) {
      const response2 = await axiosInstance.get(route("backdoor.data-master.package.info", state.packageId));
      const pkg = response2.data.data;
      state.packageInfo = pkg;

      if (pkg.slug !== state.packageId) {
        state.packageId = pkg.slug;
        window.history.replaceState(null, "", route("backdoor.data-master.package.show", pkg.slug));
        state.packageSlug = pkg.slug;
      }
    } else {
      table?.reload();
    }
  };

  const submitPackage = async () => {
    const filteredFeatures = state.form.features.filter((f) => f.trim() !== "");
    const result = packageSchema.safeParse({ ...state.form, features: filteredFeatures });
    const hasImage = state.isEdit || Boolean(state.pendingImageFile);

    if (!result.success || !hasImage) {
      state.errors = {
        category_id: getFieldError(result, "category_id"),
        name: getFieldError(result, "name"),
        description: getFieldError(result, "description"),
        image: !hasImage ? "Gambar paket wajib diunggah" : null,
      };
      return;
    }

    state.isLoading = true;

    // eslint-disable-next-line no-undef
    const formData = new FormData();
    formData.append("category_id", state.form.category_id);
    formData.append("name", state.form.name.trim());
    formData.append("description", state.form.description?.trim() ?? "");
    formData.append("is_active", state.form.is_active ? "1" : "0");

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
      const response = await axiosInstance.post(url, formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      closeDrawer();
      Toast.fire({ icon: "success", title: response.data.message });

      if (state.isEdit) {
        await handleEditSuccess();
      } else {
        window.location.href = route("backdoor.data-master.package.show", response.data.data.slug);
      }
    } catch (error) {
      if (error.response?.status === 422) {
        state.errors = error.response.data.errors ?? {};
      } else {
        Toast.fire({
          icon: "error",
          title: "Terjadi kesalahan pada server. Silahkan coba beberapa saat lagi",
        });
        console.error(error);
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
    validateField,
    initFilePond,
  };
}
