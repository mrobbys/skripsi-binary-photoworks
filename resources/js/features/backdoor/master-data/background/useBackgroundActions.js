import route from "@/lib/route";
import { Modal, Toast, confirmModal } from "@/lib/sweetalert";
import axiosInstance from "@/lib/axiosInstance";

export default function useBackgroundActions({ state, table }) {
  const toggleBackgroundStatus = async (id, currentStatus) => {
    state.isLoading = true;

    const item = table.data.find((bg) => bg.id === id);
    if (item) item.is_active = !currentStatus;

    try {
      const response = await axiosInstance.patch(route("backdoor.data-master.background.toggle", id));

      if (response.data.total_active_backgrounds !== undefined) {
        state.totalActiveBackgrounds = response.data.total_active_backgrounds;
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

  const destroyBackground = async (id, name) => {
    const result = await confirmModal(
      "Hapus Background?",
      `Background "${name}" beserta gambarnya akan dihapus permanen dari storage.`,
      "warning",
      "Ya, Hapus",
    );

    if (!result.isConfirmed) return;

    state.isLoading = true;

    try {
      const response = await axiosInstance.delete(route("backdoor.data-master.background.destroy", id));
      table.reload();
      Toast.fire({ icon: "success", title: response.data.message });
    } catch (error) {
      Modal.fire({
        icon: "error",
        title: "Gagal menghapus background",
        text: error.response?.data?.message ?? "Terjadi kesalahan server.",
      });
    } finally {
      state.isLoading = false;
    }
  };

  const openImagePreview = (imageUrl, name) => {
    state.previewImageUrl = imageUrl;
    state.previewImageName = name;
    state.isPreviewOpen = true;
  };

  const closeImagePreview = () => {
    state.isPreviewOpen = false;
    state.previewImageUrl = "";
    state.previewImageName = "";
  };

  return { toggleBackgroundStatus, destroyBackground, openImagePreview, closeImagePreview };
}
