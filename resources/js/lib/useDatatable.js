export default function useDatatable(Alpine, fetchUrl) {
  // 1. State Reaktif Terpusat
  const state = Alpine.reactive({
    data: [],
    search: "",
    isLoading: false,
    pagination: {
      current_page: 1,
      last_page: 1,
      total: 0,
      per_page: 10,
    },
  });

  // 2. Methods (Arrow Functions, No 'this')
  const fetch = async () => {
    state.isLoading = true;
    state.data = [];
    try {
      const response = await window.axios.get(fetchUrl, {
        params: {
          page: state.pagination.current_page,
          search: state.search,
          limit: state.pagination.per_page,
        },
      });

      state.data = response.data.data;
      state.pagination.current_page = response.data.current_page;
      state.pagination.last_page = response.data.last_page;
      state.pagination.total = response.data.total;

      return response.data;
    } catch (error) {
      console.error("Gagal memuat data tabel:", error);
    } finally {
      state.isLoading = false;
    }
  };

  const nextPage = () => {
    if (state.pagination.current_page < state.pagination.last_page) {
      state.pagination.current_page++;
      fetch();
    }
  };

  const prevPage = () => {
    if (state.pagination.current_page > 1) {
      state.pagination.current_page--;
      fetch();
    }
  };

  const goToPage = (page) => {
    if (page === "...") return;
    const targetPage = parseInt(page, 10);
    if (targetPage >= 1 && targetPage <= state.pagination.last_page) {
      state.pagination.current_page = targetPage;
      fetch();
    }
  };

  const reload = () => {
    fetch();
  };

  const getPages = () => {
    const current = state.pagination.current_page;
    const last = state.pagination.last_page;
    const delta = 1;
    const range = [];
    const rangeWithDots = [];
    let l;

    for (let i = 1; i <= last; i++) {
      if (i === 1 || i === last || (i >= current - delta && i <= current + delta)) {
        range.push(i);
      }
    }

    for (let i of range) {
      if (l) {
        if (i - l === 2) {
          rangeWithDots.push(l + 1);
        } else if (i - l > 2) {
          rangeWithDots.push("...");
        }
      }
      rangeWithDots.push(i);
      l = i;
    }

    return rangeWithDots;
  };

  // 3. Attach Methods ke State (agar API di Blade tidak berubah: table.fetch, table.data)
  state.fetch = fetch;
  state.nextPage = nextPage;
  state.prevPage = prevPage;
  state.goToPage = goToPage;
  state.reload = reload;
  state.getPages = getPages;

  return state;
}
