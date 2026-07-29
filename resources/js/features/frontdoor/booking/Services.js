import Choices from "choices.js";
import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";
import axiosInstance from "@/lib/axiosInstance";
import useFrontdoorPagination from "@/lib/useFrontdoorPagination";

export default function Services(Alpine) {
  const state = Alpine.reactive({
    activeCategory: "Semua",
    isLoading: false,
    data: [],
  });

  let _choices = null;
  let _selectRef = null;

  const fetchGrid = async () => {
    state.isLoading = true;
    try {
      const response = await axiosInstance.get(route("frontdoor.services.api"), {
        params: {
          category: state.activeCategory,
          page: state.currentPage,
        },
      });

      state.data = response.data.data;
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

  const scrollToTop = () => {
    _selectRef?.parentElement?.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  };

  const withScroll =
    (fn) =>
    async (...args) => {
      await fn(...args);
      scrollToTop();
    };

  const { goToPage, prevPage, nextPage, resetPage, getPages } = useFrontdoorPagination({
    state,
    onPageChange: withScroll(fetchGrid),
  });

  const initServices = (selectRef) => {
    _selectRef = selectRef;

    _choices = new Choices(selectRef, {
      searchEnabled: false,
      shouldSort: false,
      itemSelectText: "",
    });

    selectRef.addEventListener("change", async (e) => {
      state.activeCategory = e.target.value;
      resetPage();
      await fetchGrid();
    });

    fetchGrid();
  };

  const destroyServices = () => {
    _choices?.destroy();
    _choices = null;
  };

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
