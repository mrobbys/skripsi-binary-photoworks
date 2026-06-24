import route from "../../../lib/route";
import { Modal, Toast, confirmModal } from "../../../lib/sweetalert";

export default function usePackageActions({ state, table }) {
  const togglePackageStatus = async (slug, event) => {
    const checkbox = event.target;
    const originalChecked = !checkbox.checked;
    state.isLoading = true;
    try {
      const response = await window.axios.patch(route("backdoor.data-master.package.toggle", slug));
      if (response.data.total_active_variants !== undefined) {
        state.totalActivePackages = response.data.total_active_packages;
        state.totalActiveVariants = response.data.total_active_variants;
      }
      const idx = table.data.findIndex((p) => p.slug === slug);
      if (idx !== -1) table.data[idx].is_active = !originalChecked;
      Toast.fire({ icon: "success", title: response.data.message });
    } catch (error) {
      checkbox.checked = originalChecked;
      Toast.fire({ icon: "error", title: error.response?.data?.message ?? "Terjadi kesalahan server." });
    } finally {
      state.isLoading = false;
    }
  };

  const destroyPackage = async (pkg) => {
    const result = await confirmModal(
      "Hapus Paket?",
      `Paket "${pkg.name}" beserta seluruh variannya akan dihapus secara permanen.`,
      "warning",
      "Ya, Hapus",
    );
    if (!result.isConfirmed) return;
    state.isLoading = true;
    try {
      const response = await window.axios.delete(route("backdoor.data-master.package.destroy", pkg.slug));
      table.reload();
      Toast.fire({ icon: "success", title: response.data.message });
    } catch (error) {
      Modal.fire({
        icon: "error",
        title: "Gagal menghapus paket",
        text: error.response?.data?.message ?? "Terjadi kesalahan server.",
      });
    } finally {
      state.isLoading = false;
    }
  };

  return { togglePackageStatus, destroyPackage };
}
