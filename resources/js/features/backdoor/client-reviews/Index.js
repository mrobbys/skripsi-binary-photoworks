import useDatatable from "@/lib/useDatatable";
import { confirmModal, Toast } from "@/lib/sweetalert";
import route from "@/lib/route";
import axiosInstance from "@/lib/axiosInstance";
import tooltipDirective from "@/lib/tippy";

export default function Index(Alpine) {
  Alpine.plugin(tooltipDirective);
  
  const { state: table, ...methods } = useDatatable(Alpine, route("backdoor.client-reviews.data"), {
    useHistory: true,
  });
  Object.assign(table, methods);

  // state untuk stats 
  const stats = Alpine.reactive({
    average_rating: 0,
    total_reviews: 0,
    five_star_reviews: 0,
    disappointing_reviews: 0,
  });

  // ambil data stats
  const fetchStats = async () => {
    try {
      const res = await axiosInstance.get(route("backdoor.client-reviews.stats"));
      Object.assign(stats, res.data);
    } catch (error) {
      Toast.fire({ icon: "error", title: "Gagal mengambil statistik ulasan." });
      console.error("fetch stats: ", error);
    }
  };

  // delete data review 
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

      await Promise.allSettled([table.reload(), fetchStats()]);
    } catch (error) {
    if(error.response?.status === 422) {
        Toast.fire({ icon: "error", title: error.response.data.message ?? "Gagal menghapus ulasan." });
      } else {
        Toast.fire({ icon: "error", title: "Gagal menghapus ulasan." });
        console.error("delete review: ", error);
      }
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
