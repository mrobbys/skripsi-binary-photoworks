import Choices from "choices.js";
import route from "../../lib/route";
import { Toast } from "../../lib/sweetalert";

export default function Services(Alpine) {
  // --- Reactive State ---
  const state = Alpine.reactive({
    activeCategory: "Semua",
    currentPage: 1,
    lastPage: 1,
    total: 0,
    isLoading: false,
    data: [],
  });

  let _choices = null;
  let _selectRef = null;

  // --- Functions / Actions ---
  const initServices = (selectRef) => {
    _selectRef = selectRef;

    // Inisialisasi Choices.js
    _choices = new Choices(selectRef, {
      searchEnabled: false,
      shouldSort: false,
      itemSelectText: "",
    });

    selectRef.addEventListener("change", async (e) => {
      state.activeCategory = e.target.value;
      state.currentPage = 1;
      await fetchGrid();
    });

    fetchGrid();
  };

  const fetchGrid = async () => {
    state.isLoading = true;
    try {
      const response = await window.axios.get(route("frontdoor.services.index"), {
        params: {
          category: state.activeCategory,
          page: state.currentPage,
        },
      });

      state.data = response.data.data;
      state.currentPage = response.data.current_page;
      state.lastPage = response.data.last_page;
      state.total = response.data.total;
    } catch (err) {
      Toast.fire({
          icon: "error",
          title: "Gagal memuat katalog layanan.",
      });
      console.error(err);
    } finally {
      state.isLoading = false;
    }
  };

  const nextPage = async () => {
    if (state.currentPage < state.lastPage && !state.isLoading) {
      state.currentPage++;
      scrollToTop();
      await fetchGrid();
    }
  };

  const prevPage = async () => {
    if (state.currentPage > 1 && !state.isLoading) {
      state.currentPage--;
      scrollToTop();
      await fetchGrid();
    }
  };

  const goToPage = async (page) => {
    if (page === "...") return;
    const targetPage = parseInt(page, 10);
    if (targetPage >= 1 && targetPage <= state.lastPage && !state.isLoading) {
      state.currentPage = targetPage;
      scrollToTop();
      await fetchGrid();
    }
  };

  // auto scroll to top
  const scrollToTop = () => {
    _selectRef?.parentElement?.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  };

  const getPages = () => {
    const { currentPage: current, lastPage: last } = state;
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

  const destroyServices = () => {
    _choices?.destroy();
    _choices = null;
  };

  // --- Return Flat Object ---
  return {
    state,
    initServices,
    destroyServices,
    nextPage,
    prevPage,
    goToPage,
    getPages,
    fetchGrid,
  };
}
