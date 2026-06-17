import axios from "axios";

/**
 * useDatatable — Composable untuk tabel data dengan pagination & search.
 *
 * @param {object} Alpine - Instance Alpine.js
 * @param {string} fetchUrl - URL endpoint untuk mengambil data
 * @param {object} options - Opsi konfigurasi
 * @param {function} options.onSuccess - Callback dipanggil setelah fetch berhasil, menerima (responseData)
 * @param {function} options.onError - Callback dipanggil setelah fetch gagal, menerima (error)
 * @param {number} options.debounceMs - Delay debounce untuk search 
 *
 * @returns {{ state, fetch, setSearch, nextPage, prevPage, goToPage, reload, getPages }}
 */
export default function useDatatable(Alpine, fetchUrl, options = {}) {
  const { onSuccess, onError, debounceMs = 500 } = options;

  const state = Alpine.reactive({
    data: [],
    search: "",
    isLoading: false,
    error: null,
    pagination: {
      current_page: 1,
      last_page: 1,
      total: 0,
      per_page: 10,
    },
  });

  // AbortController untuk membatalkan request sebelumnya (cegah race condition)
  let abortController = null;

  const fetch = async () => {
    // Batalkan request yang sedang berjalan
    if (abortController) {
      abortController.abort();
    }
    // eslint-disable-next-line no-undef
    abortController = new AbortController();

    state.isLoading = true;
    state.error = null;

    try {
      const response = await axios.get(fetchUrl, {
        signal: abortController.signal,
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

      onSuccess?.(response.data);

      return response.data;
    } catch (error) {
      // Abaikan error dari request yang sengaja dibatalkan (saat debounce)
      if (error.code === "ERR_CANCELED") return;

      if (error.response?.status === 419 || error.response?.status === 401) {
        window.location.reload();
        return;
      }

      state.error = error.response?.data?.message ?? "Gagal memuat data.";
      onError?.(error);
    } finally {
      state.isLoading = false;
    }
  };

  // debounce search
  let searchTimer = null;
  const setSearch = (value) => {
    state.search = value;
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      state.pagination.current_page = 1;
      fetch();
    }, debounceMs);
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

  const reload = () => fetch();

  const getPages = () => {
    const { current_page: current, last_page: last } = state.pagination;
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
    state,
    fetch,
    setSearch,
    nextPage,
    prevPage,
    goToPage,
    reload,
    getPages,
  };
}
