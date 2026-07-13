import route from "@/lib/route";
import { Modal, Toast, confirmModal } from "@/lib/sweetalert";
import axiosInstance from "@/lib/axiosInstance";

export default function useAddonActions({ state, table }) {
  const toggleAddonStatus = async (id, currentStatus) => {
    state.isLoading = true;

    // Optimistic update
    const item = table.data.find((a) => a.id === id);
    if (item) item.is_active = !currentStatus;

    try {
      const response = await axiosInstance.patch(route("backdoor.data-master.addon.toggle", id));

      if (response.data.total_active_addons !== undefined) {
        state.totalActiveAddons = response.data.total_active_addons;
      }

      Toast.fire({ icon: "success", title: response.data.message });
    } catch (error) {
      // Rollback optimistic update
      if (item) item.is_active = currentStatus;

      Toast.fire({
        icon: "error",
        title: error.response?.data?.message ?? "Terjadi kesalahan server.",
      });
    } finally {
      state.isLoading = false;
    }
  };

  const destroyAddon = async (id, name) => {
    const result = await confirmModal(
      "Hapus Add-On?",
      `Add-on "${name}" akan dihapus secara permanen.`,
      "warning",
      "Ya, Hapus",
    );

    if (!result.isConfirmed) return;

    state.isLoading = true;

    try {
      const response = await axiosInstance.delete(route("backdoor.data-master.addon.destroy", id));
      table.reload();
      Toast.fire({ icon: "success", title: response.data.message });
    } catch (error) {
      Modal.fire({
        icon: "error",
        title: "Gagal menghapus add-on",
        text: error.response?.data?.message ?? "Terjadi kesalahan server.",
      });
    } finally {
      state.isLoading = false;
    }
  };

  return { toggleAddonStatus, destroyAddon };
}
