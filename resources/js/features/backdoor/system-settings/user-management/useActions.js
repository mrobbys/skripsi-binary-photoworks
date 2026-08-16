import { confirmModal, Toast, Modal } from "@/lib/sweetalert";
import route from "@/lib/route";
import axiosInstance from "@/lib/axiosInstance";

export default function useActions({ table }) {
  /**
   * Reset password user ke Password123.
   */
  const resetPassword = async (id, name) => {
    const result = await confirmModal(
      `Reset Password "${name}"?`,
      "Password akan dikembalikan ke nilai default: Password123",
      "warning",
      "Ya, Reset",
    );

    if (!result.isConfirmed) return;

    try {
      const res = await axiosInstance.patch(route("backdoor.system-settings.users.reset-password", id));
      Toast.fire({ icon: "success", title: res.data.message });
    } catch (error) {
      Toast.fire({
        icon: "error",
        title: error.response?.data?.message ?? "Gagal mereset password",
      });
    }
  };

  /**
   * Hard delete user dari database.
   */
  const destroyUser = async (id, name) => {
    const result = await confirmModal(
      `Hapus User "${name}"?`,
      "User yang dihapus tidak dapat dipulihkan",
      "warning",
      "Ya, Hapus",
    );

    if (!result.isConfirmed) return;

    try {
      const res = await axiosInstance.delete(route("backdoor.system-settings.users.destroy", id));
      table.reload();
      Toast.fire({ icon: "success", title: res.data.message });
    } catch (error) {
      const msg = error.response?.data?.message ?? "Gagal menghapus user";
      Modal.fire({ icon: "error", title: "Gagal Menghapus", text: msg });
    }
  };

  return { resetPassword, destroyUser };
}
