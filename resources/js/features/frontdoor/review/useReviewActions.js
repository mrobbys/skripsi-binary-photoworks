import { Toast, confirmModal } from "@/lib/sweetalert";
import route from "@/lib/route";
import axiosInstance from "@/lib/axiosInstance";

export default function useReviewActions({ state, resetPage }) {
  const scrollToTop = () => {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  };

  const fetchReviews = async (options = {}) => {
    const { scrollToTop: shouldScroll = false } = options;
    state.isLoading = true;

    try {
      const res = await axiosInstance.get(route("frontdoor.reviews.data"), {
        params: { page: state.currentPage, sort: state.sort, limit: 10 },
      });

      const result = res.data;

      state.items = result.data;
      state.userReview = result.user_review;
      state.lastPage = result.last_page;
      state.total = result.total;
      state.stats = result.stats;

      if (shouldScroll) {
        scrollToTop();
      }
    } catch (err) {
      console.error("Gagal memuat ulasan:", err);
      Toast.fire({ icon: "error", title: "Gagal memuat ulasan." });
    } finally {
      state.isLoading = false;
    }
  };

  // sort data review
  const onSortChange = (value) => {
    state.sort = value;
    resetPage();
    fetchReviews({ scrollToTop: true });
  };

  // delete data review
  const deleteReview = async (id) => {
    const result = await confirmModal(
      "Hapus Ulasan?",
      "Apakah Anda yakin ingin menghapus ulasan ini? Tindakan ini tidak dapat dibatalkan.",
      "warning",
      "Ya, Hapus",
    );

    if (!result.isConfirmed) return;

    state.isLoading = true;

    try {
      const res = await axiosInstance.delete(route("frontdoor.reviews.destroy", id));
      Toast.fire({ icon: "success", title: res.data.message });
      await fetchReviews();
    } catch (err) {
      if (err.response?.status === 422) {
        Toast.fire({ icon: "error", title: err?.response?.data?.message ?? "Gagal menghapus ulasan." });
      }
      Toast.fire({ icon: "error", title: "Gagal menghapus ulasan." });
      console.log(err);
    } finally {
      state.isLoading = false;
    }
  };

  return {
    fetchReviews,
    onSortChange,
    deleteReview,
  };
}
