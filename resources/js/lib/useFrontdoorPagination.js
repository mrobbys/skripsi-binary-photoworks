/* global URLSearchParams */

export default function useFrontdoorPagination({ state, onPageChange }) {
  const params = new URLSearchParams(window.location.search);
  const pageFromUrl = parseInt(params.get("page"), 10);

  state.currentPage = pageFromUrl > 0 ? pageFromUrl : 1;
  state.lastPage = 1;
  state.total = 0;

  const syncUrl = () => {
    const p = new URLSearchParams();
    if (state.currentPage > 1) p.set("page", state.currentPage);
    const qs = p.toString();
    window.history.replaceState(null, "", qs ? `?${qs}` : window.location.pathname);
  };

  const goToPage = async (page) => {
    if (page === "..." || page === state.currentPage || state.isLoading) return;
    state.currentPage = parseInt(page, 10);
    syncUrl();
    await onPageChange?.();
  };

  const prevPage = async () => {
    if (state.currentPage > 1 && !state.isLoading) {
      state.currentPage--;
      syncUrl();
      await onPageChange?.();
    }
  };

  const nextPage = async () => {
    if (state.currentPage < state.lastPage && !state.isLoading) {
      state.currentPage++;
      syncUrl();
      await onPageChange?.();
    }
  };

  const resetPage = () => {
    state.currentPage = 1;
    syncUrl();
  };

  const getPages = () => {
    const current = state.currentPage;
    const last = state.lastPage;

    if (last <= 7) {
      return Array.from({ length: last }, (_, i) => i + 1);
    }

    if (current <= 4) {
      return [1, 2, 3, 4, 5, "...", last];
    }

    if (current >= last - 3) {
      return [1, "...", last - 4, last - 3, last - 2, last - 1, last];
    }

    return [1, "...", current - 1, current, current + 1, "...", last];
  };

  return {
    goToPage,
    prevPage,
    nextPage,
    resetPage,
    getPages,
  };
}
