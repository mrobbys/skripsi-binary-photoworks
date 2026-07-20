import useDatatable from "@/lib/useDatatable";
import { confirmModal, Toast } from "@/lib/sweetalert";
import route from "@/lib/route";
import axios from "@/lib/axiosInstance";

export default function Index(Alpine) {
  const { state: table, ...methods } = useDatatable(Alpine, route("backdoor.system-settings.roles.data"), {
    debounceMs: 400,
  });
  Object.assign(table, methods);

  const deleteRole = async (id, name) => {
    const result = await confirmModal(
      `Hapus Role "${name}"?`,
      "Role yang dihapus tidak dapat dikembalikan dan seluruh pengguna yang memiliki role ini akan kehilangan aksesnya.",
      "warning",
      "Ya, Hapus",
    );

    if (!result.isConfirmed) return;

    try {
      await axios.delete(route("backdoor.system-settings.roles.destroy", id));
      Toast.fire({ icon: "success", title: `Role "${name}" berhasil dihapus.` });
      table.reload();
    } catch (error) {
      const msg = error.response?.data?.message ?? "Gagal menghapus role.";
      Toast.fire({ icon: "error", title: msg });
    }
  };

  return {
    table,
    deleteRole,
    init() {
      table.fetch();
    },
  };
}
