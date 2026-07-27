import useDatatable from "@/lib/useDatatable";
import { confirmModal, Toast } from "@/lib/sweetalert";
import route from "@/lib/route";
import axiosInstance from "@/lib/axiosInstance";

export default function Index(Alpine) {
  const { state: table, ...methods } = useDatatable(Alpine, route("backdoor.client-reviews.data"), {
    useHistory: true,
  });
  Object.assign(table, methods);

  const stats = Alpine.reactive({
    average_rating: 0,
    total_reviews: 0,
    five_star_reviews: 0,
    disappointing_reviews: 0,
  });

  const fetchStats = async () => {
    const res = await axiosInstance.get(route("backdoor.client-reviews.stats"));
    Object.assign(stats, res.data);
  };

  const deleteReview = async (id) => {
    const result = await confirmModal(
      "Hapus Ulasan?",
      "Ulasan yang dihapus tidak dapat dikembalikan.",
      "warning",
      "Ya, Hapus",
    );

    if (!result.isConfirmed) return;

    try {
      await axiosInstance.delete(route("backdoor.client-reviews.destroy", id));
      Toast.fire({ icon: "success", title: "Ulasan berhasil dihapus." });

      table.reload();
      fetchStats();
    } catch {
      Toast.fire({ icon: "error", title: "Gagal menghapus ulasan." });
    }
  };

  return {
    table,
    stats,
    deleteReview,
    init() {
      table.fetch();
      fetchStats();
    },
  };
}
