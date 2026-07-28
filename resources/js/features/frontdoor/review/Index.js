import { Toast, Modal, confirmModal } from "@/lib/sweetalert";
import route from "@/lib/route";
import axiosInstance from "@/lib/axiosInstance";
import useFrontdoorPagination from "@/lib/useFrontdoorPagination";

export default function Index(Alpine) {
  const state = Alpine.reactive({
    items: [],
    userReview: null,
    stats: {
      average_rating: 0,
      total_reviews: 0,
      breakdown: { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 },
      breakdown_percentage: { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 },
    },

    // loading state
    isLoading: false,
    isSubmitting: false,

    // sort — diubah oleh select Choices.js
    sort: "newest",

    // modal form state
    openModal: false,
    rating: 0,
    hoverRating: 0,
    comment: "",
    maxComment: 500,
  });

  const fetchReviews = async () => {
    state.isLoading = true;

    try {
      const res = await axiosInstance.get(route("frontdoor.reviews.data"), {
        params: { page: state.currentPage, sort: state.sort, limit: 10 },
      });

      state.items = res.data.data;
      state.userReview = res.data.user_review;
      state.lastPage = res.data.last_page;
      state.total = res.data.total;
      state.stats = res.data.stats;
    } catch (err) {
      console.error("Gagal memuat ulasan:", err);
    } finally {
      state.isLoading = false;
    }
  };

  const { goToPage, prevPage, nextPage, resetPage, getPages } = useFrontdoorPagination({
    state,
    onPageChange: fetchReviews,
  });

  const init = () => {
    fetchReviews();
  };

  const onSortChange = (value) => {
    state.sort = value;
    resetPage();
    fetchReviews();
  };

  const setRating = (n) => {
    state.rating = n;
  };

  const setHover = (n) => {
    state.hoverRating = n;
  };

  const clearHover = () => {
    state.hoverRating = 0;
  };

  const resetForm = () => {
    state.openModal = false;
    state.rating = 0;
    state.hoverRating = 0;
    state.comment = "";
  };

  const submitReview = async () => {
    if (!state.rating || !state.comment.trim()) return;

    state.isSubmitting = true;

    try {
      const res = await axiosInstance.post(route("frontdoor.reviews.store"), {
        rating: state.rating,
        comment: state.comment,
      });

      Toast.fire({ icon: "success", title: res.data.message });
      resetForm();
      await fetchReviews();
    } catch (err) {
      const msg = err.response?.data?.message ?? "Gagal mengirim ulasan.";
      Modal.fire({ icon: "error", title: "Gagal mengirim ulasan.", text: msg });
    } finally {
      state.isSubmitting = false;
    }
  };

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
      const msg = err.response?.data?.message ?? "Gagal menghapus ulasan.";
      Toast.fire({ icon: "error", title: msg });
    } finally {
      state.isLoading = false;
    }
  };

  return {
    state,
    init,
    onSortChange,
    setRating,
    setHover,
    clearHover,
    resetForm,
    submitReview,
    deleteReview,
    goToPage,
    prevPage,
    nextPage,
    getPages,
  };
}
