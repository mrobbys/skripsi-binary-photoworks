export default function usePagination({ state, fetchCallback }) {
  if (state.currentPage === undefined) {
    state.currentPage = 1;
    state.lastPage = 1;
    state.total = 0;
  }

  const nextPage = async () => {
    if (state.currentPage < state.lastPage && !state.isLoading) {
      state.currentPage++;
      await fetchCallback(true);
    }
  };

  const prevPage = async () => {
    if (state.currentPage > 1 && !state.isLoading) {
      state.currentPage--;
      await fetchCallback(true);
    }
  };

  const goToPage = async (page) => {
    if (page === "...") return;
    const targetPage = parseInt(page, 10);
    if (targetPage >= 1 && targetPage <= state.lastPage && !state.isLoading) {
      state.currentPage = targetPage;
      await fetchCallback(true);
    }
  };

  const getPages = () => {
    const current = state.currentPage;
    const last = state.lastPage;
    
    // Jika total halaman sedikit, tampilkan semuanya
    if (last <= 7) {
      return Array.from({ length: last }, (_, i) => i + 1);
    }

    // Jika di awal
    if (current <= 4) {
      return [1, 2, 3, 4, 5, "...", last];
    }

    // Jika di akhir
    if (current >= last - 3) {
      return [1, "...", last - 4, last - 3, last - 2, last - 1, last];
    }

    // Jika di tengah
    return [1, "...", current - 1, current, current + 1, "...", last];
  };

  return {
    nextPage,
    prevPage,
    goToPage,
    getPages,
  };
}
