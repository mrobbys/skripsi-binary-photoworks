import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";
import { z } from "zod";
import { getFieldError } from "@/lib/zodHelper";
import axiosInstance from "@/lib/axiosInstance";

const variantSchema = z.object({
  name: z.string().min(3, "Nama varian minimal 3 karakter").max(100, "Maksimal 100 karakter"),
  price: z
    .union([z.string(), z.number()])
    .transform((v) => Number(v))
    .refine((v) => v >= 1, "Harga minimal Rp 1"),
  duration: z
    .union([z.string(), z.number()])
    .transform((v) => Number(v))
    .refine((v) => v >= 1, "Durasi minimal 1 menit"),
  is_whatsapp_only: z.boolean(),
  is_active: z.boolean(),
  features: z.array(z.string()).optional(),
});

export default function useVariantForm({ Alpine, state, table }) {
  Alpine.effect(() => {
    const allFilled = Boolean(
      state.variantForm.name &&
      state.variantForm.price &&
      state.variantForm.duration
    );
    const noErrors =
      !state.variantErrors.name &&
      !state.variantErrors.price &&
      !state.variantErrors.duration;

    state.isVariantFormValid = Boolean(allFilled && noErrors);
  });

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
    state.dismissedVariantErrors = {};
  };

  const openVariantDrawer = (packageSlug) => {
    resetVariantForm();
    state.currentPackageSlug = packageSlug;
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
    state.currentPackageSlug = packageSlug;
    state.variantForm.name = variant?.name ?? "";
    state.variantForm.price = variant?.price ?? "";
    state.variantForm.duration = variant?.duration ?? "";
    state.variantForm.is_whatsapp_only = Boolean(variant?.is_whatsapp_only);
    state.variantForm.is_active = Boolean(variant?.is_active ?? true);
    state.variantForm.features =
      variant?.features?.map((f) => (typeof f === "string" ? f : f.description)) ?? ["", ""];
    if (state.variantForm.features.length === 0) state.variantForm.features = ["", ""];
    state.isVariantDrawerOpen = true;
  };

  const addVariantFeature = () => state.variantForm.features.push("");
  const removeVariantFeature = (index) => {
    state.variantForm.features.splice(index, 1);
    if (state.variantForm.features.length === 0) state.variantForm.features.push("");
  };

  const onPriceInput = (event) => {
    let val = parseInt(event.target.value.replace(/\D/g, ""), 10) || 0;
    if (val > 100000000) val = 100000000;
    state.variantForm.price = val || "";
    event.target.value = val ? new Intl.NumberFormat("id-ID").format(val) : "";
    validateVariantField("price");
  };

  const validateVariantField = (field) => {
    state.dismissedVariantErrors[field] = true;
    const filteredFeatures = state.variantForm.features.filter((f) => f.trim() !== "");
    const result = variantSchema.safeParse({ ...state.variantForm, features: filteredFeatures });
    state.variantErrors[field] = result.success ? null : getFieldError(result, field);
  };

  const submitVariant = async () => {
    const filteredFeatures = state.variantForm.features.filter((f) => f.trim() !== "");
    const validation = variantSchema.safeParse({ ...state.variantForm, features: filteredFeatures });

    if (!validation.success) {
      state.variantErrors = {
        name: getFieldError(validation, "name"),
        price: getFieldError(validation, "price"),
        duration: getFieldError(validation, "duration"),
      };
      return;
    }

    state.isLoading = true;
    const payload = validation.data;
    const url = state.isVariantEdit
      ? route("backdoor.data-master.package.variants.update", {
          package: state.currentPackageSlug,
          variant: state.variantId,
        })
      : route("backdoor.data-master.package.variants.store", state.currentPackageSlug);
    const method = state.isVariantEdit ? "put" : "post";

    try {
      const response = await axiosInstance[method](url, payload);
      closeVariantDrawer();
      table.reload();
      Toast.fire({ icon: "success", title: response.data.message });
    } catch (error) {
      if (error.response?.status === 422) {
        state.variantErrors = error.response.data.errors ?? {};
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
    openVariantDrawer,
    closeVariantDrawer,
    editVariant,
    addVariantFeature,
    removeVariantFeature,
    onPriceInput,
    validateVariantField,
    submitVariant,
  };
}
