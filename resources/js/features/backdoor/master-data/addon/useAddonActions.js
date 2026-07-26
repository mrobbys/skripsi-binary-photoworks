import route from "@/lib/route";
import { Modal, Toast, confirmModal } from "@/lib/sweetalert";
import axiosInstance from "@/lib/axiosInstance";

export default function useAddonActions({ state, table }) {
  const toggleAddonStatus = async (id) => {
    state.isLoading = true;

    try {
      await axiosInstance.patch(route("backdoor.data-master.addon.toggle", id));
      table.reload();
      Toast.fire({ icon: "success", title: "Status add-on berhasil diperbarui" });
    } catch (error) {
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
