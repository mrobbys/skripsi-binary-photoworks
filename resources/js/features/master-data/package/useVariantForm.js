import route from "../../../lib/route";
import { Modal, Toast } from "../../../lib/sweetalert";
import { z } from "zod";

const variantSchema = z.object({
  name: z.string().min(3, "Nama varian minimal 3 karakter.").max(100, "Maksimal 100 karakter."),
  price: z
    .union([z.string(), z.number()])
    .transform((v) => Number(v))
    .refine((v) => v >= 1, "Harga minimal Rp 1."),
  duration: z
    .union([z.string(), z.number()])
    .transform((v) => Number(v))
    .refine((v) => v >= 1, "Durasi minimal 1 menit."),
  is_whatsapp_only: z.boolean(),
  is_active: z.boolean(),
  features: z.array(z.string()).optional(),
});

export default function useVariantForm({ state, table }) {
  const resetVariantForm = () => {
    state.isVariantEdit = false;
    state.variantId = null;
    state.variantForm.name = "";
    state.variantForm.price = "";
    state.variantForm.duration = "";
    state.variantForm.is_whatsapp_only = false;
    state.variantForm.is_active = true;
    state.variantForm.features = ["", ""];
    state.variantErrors = {};
  };

  const openVariantDrawer = (packageSlug) => {
    resetVariantForm();
    state.currentPackageSlug = packageSlug ?? window.__packageSlug;
    state.isVariantDrawerOpen = true;
  };
  const closeVariantDrawer = () => {
    state.isVariantDrawerOpen = false;
    setTimeout(() => resetVariantForm(), 500);
  };

  const editVariant = (variant, packageSlug) => {
    resetVariantForm();
    state.isVariantEdit = true;
    state.variantId = variant?.id;
    state.currentPackageSlug = packageSlug ?? window.__packageSlug;
    state.variantForm.name = variant?.name;
    state.variantForm.price = variant?.price;
    state.variantForm.duration = variant?.duration;
    state.variantForm.is_whatsapp_only = variant?.is_whatsapp_only;
    state.variantForm.is_active = variant?.is_active;
    state.variantForm.features = variant?.features?.map((f) => (typeof f === "string" ? f : f.description)) ?? [""];
    if (state.variantForm.features.length === 0) state.variantForm.features.push("");
    state.isVariantDrawerOpen = true;
  };

  const addVariantFeature = () => state.variantForm.features.push("");
  const removeVariantFeature = (index) => {
    state.variantForm.features.splice(index, 1);
    if (state.variantForm.features.length === 0) state.variantForm.features.push("");
  };

  const submitVariant = async () => {
    state.isLoading = true;
    state.variantErrors = {};
    const filteredFeatures = state.variantForm.features.filter((f) => f.trim() !== "");
    const validation = variantSchema.safeParse({ ...state.variantForm, features: filteredFeatures });
    if (!validation.success) {
      validation.error.issues.forEach((issue) => {
        if (!state.variantErrors[issue.path[0]]) state.variantErrors[issue.path[0]] = issue.message;
      });
      state.isLoading = false;
      return;
    }
    const payload = validation.data;
    const url = state.isVariantEdit
      ? route("backdoor.data-master.package.variants.update", {
          package: state.currentPackageSlug,
          variant: state.variantId,
        })
      : route("backdoor.data-master.package.variants.store", state.currentPackageSlug);
    const method = state.isVariantEdit ? "put" : "post";
    try {
      const response = await window.axios[method](url, payload);
      closeVariantDrawer();
      table.reload();
      Toast.fire({ icon: "success", title: response.data.message });
    } catch (error) {
      if (error.response?.status === 422) {
        const errs = error.response.data.errors;
        for (const key in errs) state.variantErrors[key] = errs[key][0];
      } else {
        Modal.fire({
          icon: "error",
          title: "Gagal menyimpan varian",
          text: error.response?.data?.message ?? "Terjadi kesalahan server.",
        });
      }
    } finally {
      state.isLoading = false;
    }
  };

  return {
    openVariantDrawer,
    closeVariantDrawer,
    editVariant,
    addVariantFeature,
    removeVariantFeature,
    submitVariant,
  };
}
