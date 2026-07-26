/* eslint-disable no-undef */

import axios from "@/lib/axiosInstance";

/**
 * useDatatable — Composable untuk tabel data dengan pagination & search.
 *
 * @param {object} Alpine - Instance Alpine.js
 * @param {string} fetchUrl - URL endpoint untuk mengambil data
 * @param {object} options - Opsi konfigurasi
 * @param {function} options.onSuccess - Callback dipanggil setelah fetch berhasil, menerima (responseData)
 * @param {function} options.onError - Callback dipanggil setelah fetch gagal, menerima (error)
 * @param {number} options.debounceMs - Delay debounce untuk search
 * @param {function|object} options.extraParams - Params tambahan yang di-merge ke request (function atau object)
 *
 * @returns {{ state, fetch, setSearch, nextPage, prevPage, goToPage, reload, getPages }}
 */
export default function useDatatable(Alpine, fetchUrl, options = {}) {
  const { onSuccess, onError, debounceMs = 500, extraParams, useHistory } = options;

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

  if (useHistory) {
    const params = new URLSearchParams(window.location.search);
    const page = params.get("page");
    const search = params.get("search");
    if (page) state.pagination.current_page = parseInt(page, 10);
    if (search) state.search = search;
  }

  // AbortController untuk membatalkan request sebelumnya (cegah race condition)
  let abortController = null;

  const fetch = async ({ showLoading = true } = {}) => {
    // Batalkan request yang sedang berjalan
    if (abortController) {
      abortController.abort();
    }
    abortController = new AbortController();

    if (showLoading) state.isLoading = true;
    state.error = null;

    try {
      const url = typeof fetchUrl === "function" ? fetchUrl() : fetchUrl;
      const extra = typeof extraParams === "function" ? extraParams() : (extraParams ?? {});
      const response = await axios.get(url, {
        signal: abortController.signal,
        params: {
          page: state.pagination.current_page,
          search: state.search,
          limit: state.pagination.per_page,
          ...extra,
        },
      });

      state.data = response.data.data;
      state.pagination.current_page = response.data.current_page;
      state.pagination.last_page = response.data.last_page;
      state.pagination.total = response.data.total;

      if (useHistory) {
        const params = new URLSearchParams();
        if (state.pagination.current_page > 1) params.set("page", state.pagination.current_page);
        if (state.search) params.set("search", state.search);
        const qs = params.toString();
        window.history.replaceState(null, "", qs ? `?${qs}` : window.location.pathname);
      }

      onSuccess?.(response.data);

      return response.data;
    } catch (error) {
      // Abaikan error dari request yang sengaja dibatalkan (saat debounce)
      if (error.code === "ERR_CANCELED") return;

      state.error = error.response?.data?.message ?? "Gagal memuat data.";
      onError?.(error);
    } finally {
      if (showLoading) state.isLoading = false;
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

  const reload = ({ showLoading = false } = {}) => fetch({ showLoading });

  const getPages = () => {
    const { current_page: current, last_page: last } = state.pagination;
    
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
