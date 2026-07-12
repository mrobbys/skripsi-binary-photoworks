import route from "@/lib/route";
import { Modal, Toast, confirmModal } from "@/lib/sweetalert";
import axiosInstance from "@/lib/axiosInstance";

export default function useVariantActions({ state, table }) {
  const toggleVariantStatus = async (packageSlug, variantId, event) => {
    const checkbox = event.target;
    const originalChecked = !checkbox.checked;
    state.isLoading = true;
    try {
      const response = await axiosInstance.patch(
        route("backdoor.data-master.package.variants.toggle", { package: packageSlug, variant: variantId }),
      );
      const idx = table.data.findIndex((v) => v.id === variantId);
      if (idx !== -1) table.data[idx].is_active = !originalChecked;
      Toast.fire({ icon: "success", title: response.data.message });
    } catch (error) {
      checkbox.checked = originalChecked;
      Toast.fire({ icon: "error", title: error.response?.data?.message ?? "Terjadi kesalahan server." });
    } finally {
      state.isLoading = false;
    }
  };

  const destroyVariant = async (packageSlug, variantId, variantName) => {
    const result = await confirmModal(
      "Hapus Varian?",
      `Varian "${variantName}" akan dihapus secara permanen.`,
      "warning",
      "Ya, Hapus",
    );
    if (!result.isConfirmed) return;
    state.isLoading = true;
    try {
      const response = await axiosInstance.delete(
        route("backdoor.data-master.package.variants.destroy", { package: packageSlug, variant: variantId }),
      );
      table.reload();
      Toast.fire({ icon: "success", title: response.data.message });
    } catch (error) {
      Modal.fire({
        icon: "error",
        title: "Gagal menghapus varian",
        text: error.response?.data?.message ?? "Terjadi kesalahan server.",
      });
    } finally {
      state.isLoading = false;
    }
  };

  return { toggleVariantStatus, destroyVariant };
}