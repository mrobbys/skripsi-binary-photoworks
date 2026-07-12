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
    const delta = 1;
    const range = [];
    const result = [];
    let prev;

    for (let i = 1; i <= last; i++) {
      if (i === 1 || i === last || Math.abs(i - current) <= delta) {
        range.push(i);
      }
    }

    for (const page of range) {
      if (prev !== undefined) {
        if (page - prev === 2) result.push(prev + 1);
        else if (page - prev > 2) result.push("...");
      }
      result.push(page);
      prev = page;
    }

    return result;
  };

  return {
    nextPage,
    prevPage,
    goToPage,
    getPages,
  };
}
