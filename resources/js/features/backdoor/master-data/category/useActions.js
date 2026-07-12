import route from "@/lib/route";
import { Modal, Toast, confirmModal } from "@/lib/sweetalert";

export default function useActions({ state, table }) {
  // Fungsi untuk mengubah status kategori dengan toggle
  const toggleCategoryStatus = async (slug, event) => {
    const checkbox = event.target;
    const originalChecked = !checkbox.checked; // State sebelum diubah

    state.isLoading = true;
    try {
      const response = await window.axios.patch(route("backdoor.data-master.category.toggle", slug));
      state.activeCount = response.data.active_count ?? state.activeCount;

      // Update data di table secara reaktif
      const itemIndex = table.data.findIndex((i) => i.slug === slug);
      if (itemIndex !== -1) {
        table.data[itemIndex].is_active = !originalChecked;
      }

      Toast.fire({
        icon: "success",
        title: response.data.message,
      });
    } catch (error) {
      checkbox.checked = originalChecked; // jika gagal, kembalikan state sebelum diubah
      Toast.fire({
        icon: "error",
        title: error.response?.data?.message ?? "Terjadi kesalahan server.",
      });
      console.error(error);
    } finally {
      state.isLoading = false;
    }
  };

  // Fungsi untuk menghapus kategori
  const destroyCategory = async (category) => {
    const { name, slug } = category;

    // panggil modal confirm sweetalert
    const result = await confirmModal(
      "Hapus Kategori?",
      `Kategori "${name}" akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.`,
      "warning",
      "Ya, Hapus",
    );

    // jika user menekan cancel, keluar
    if (!result.isConfirmed) return;

    try {
      const response = await window.axios.delete(route("backdoor.data-master.category.destroy", slug));
      table.reload();
      Toast.fire({
        icon: "success",
        title: response.data.message,
      });
    } catch (error) {
      Modal.fire({
        icon: "error",
        title: "Gagal menghapus kategori",
        text: error.response?.data?.message ?? "Terjadi kesalahan server.",
      });
    }
  };

  return {
    toggleCategoryStatus,
    destroyCategory,
  };
}
