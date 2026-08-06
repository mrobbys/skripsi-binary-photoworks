import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";
import axiosInstance from "@/lib/axiosInstance";
import useFrontdoorPagination from "@/lib/useFrontdoorPagination";
import useChoices from "@/lib/useChoices";

export default function Services(Alpine) {
  Alpine.data("serviceChoices", useChoices);

  const state = Alpine.reactive({
    activeCategory: "Semua",
    isLoading: false,
    data: [],
    currentPage: 1,
    lastPage: 1,
    total: 0,
  });

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
    const target = document.getElementById("servicesTop");
    if (!target) return;
    
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  };

  const withScroll =
    (fn) =>
    async (...args) => {
      scrollToTop();
      await fn(...args);
    };

  const { goToPage, prevPage, nextPage, resetPage, getPages } = useFrontdoorPagination({
    state,
    onPageChange: withScroll(fetchGrid),
  });

  const init = () => {
    fetchGrid();
  };

  const onCategoryChange = (e) => {
    state.activeCategory = e.target.value;
    resetPage();
    fetchGrid();
  };

  return {
    state,
    init,
    onCategoryChange,
    nextPage,
    prevPage,
    goToPage,
    getPages,
    fetchGrid,
  };
}
