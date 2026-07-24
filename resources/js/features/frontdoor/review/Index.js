import { Toast, Modal, confirmModal } from "@/lib/sweetalert";
import route from "@/lib/route";
import axiosInstance from "@/lib/axiosInstance";

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

    // pagination
    currentPage: 1,
    lastPage: 1,
    total: 0,

    // sort — reaktif, diubah oleh select Choices.js
    sort: "newest",

    // loading state
    isLoading: false,
    isSubmitting: false,

    // modal form state
    openModal: false,
    rating: 0,
    hoverRating: 0,
    comment: "",
    maxComment: 500,
  });

  let isMounted = false;
  Alpine.effect(() => {
    // eslint-disable-next-line no-unused-vars
    const currentSort = state.sort; // akses agar reaktif

    if (isMounted) {
      // Re-fetch dari halaman 1 setiap kali sort berubah
      fetchReviews();
    }
  });

  // Fetch data awal setelah komponen pertama kali dimuat
  const init = () => {
    isMounted = true;
    fetchReviews();
  };

  // ---------------------------------------------------------------------------
  // Methods — arrow functions, tanpa 'this'
  // ---------------------------------------------------------------------------

  /** Ambil data ulasan dari backend via AJAX */
  const fetchReviews = async (page = 1) => {
    state.isLoading = true;

    try {
      const res = await axiosInstance.get(route("frontdoor.reviews.data"), {
        params: { page, sort: state.sort, limit: 10 },
      });

      state.items = res.data.data;
      state.userReview = res.data.user_review;
      state.currentPage = res.data.current_page;
      state.lastPage = res.data.last_page;
      state.total = res.data.total;
      state.stats = res.data.stats;
    } catch (err) {
      console.error("Gagal memuat ulasan:", err);
    } finally {
      state.isLoading = false;
    }
  };

  /** Set nilai rating dari klik bintang di modal */
  const setRating = (n) => {
    state.rating = n;
  };

  /** Set hover rating saat mouse di atas bintang */
  const setHover = (n) => {
    state.hoverRating = n;
  };

  /** Clear hover rating saat mouse keluar dari area bintang */
  const clearHover = () => {
    state.hoverRating = 0;
  };

  /** Reset seluruh state modal form ke kondisi awal */
  const resetForm = () => {
    state.openModal = false;
    state.rating = 0;
    state.hoverRating = 0;
    state.comment = "";
  };

  /** Submit ulasan baru ke backend */
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
      Modal.fire({ icon: "error", title: 'Gagal mengirim ulasan.', text: msg });
    } finally {
      state.isSubmitting = false;
    }
  };

  /** Hapus ulasan milik sendiri (hanya dari card "Ulasan Anda") */
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

  // ---------------------------------------------------------------------------
  // Pagination helpers — dibutuhkan oleh <x-frontdoor.shared.pagination />
  // ---------------------------------------------------------------------------

  const goToPage = (page) => {
    if (page !== "..." && page !== state.currentPage) {
      fetchReviews(page);
    }
  };

  const prevPage = () => {
    if (state.currentPage > 1) fetchReviews(state.currentPage - 1);
  };

  const nextPage = () => {
    if (state.currentPage < state.lastPage) fetchReviews(state.currentPage + 1);
  };

  const getPages = () => {
    const current = state.currentPage;
    const last = state.lastPage;
    const pages = [];

    for (let i = 1; i <= last; i++) {
      if (i === 1 || i === last || (i >= current - 1 && i <= current + 1)) {
        pages.push(i);
      } else if (pages.at(-1) !== "...") {
        pages.push("...");
      }
    }

    return pages;
  };

  // ---------------------------------------------------------------------------
  // Return — dikonsumsi oleh x-data="Index" di Blade
  // ---------------------------------------------------------------------------
  return {
    state,
    init,
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
