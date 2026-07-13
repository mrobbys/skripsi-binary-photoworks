import axiosInstance from "@/lib/axiosInstance";
import route from "@/lib/route";

export default function useAppointments({ state }) {
  /**
   * Ambil data booking dari backend berdasarkan tab dan halaman (page)
   * Parameter shouldScrollToTop menentukan apakah layar harus gulir ke atas saat fetchAppointments dipanggil
   */
  const fetchAppointments = async (shouldScrollToTop = false) => {
    state.isLoading = true;
    state.appointments = [];
    try {
      const res = await axiosInstance.get(route("frontdoor.dashboard.appointments"), {
        params: {
          tab: state.activeTab,
          page: state.currentPage || 1,
        },
      });

      state.appointments = res.data.data;

      // Update meta paginasi
      state.currentPage = res.data.current_page;
      state.lastPage = res.data.last_page;
      state.total = res.data.total;
    } catch (err) {
      console.error("Gagal memuat riwayat booking:", err);
    } finally {
      state.isLoading = false;

      // Gulir layar perlahan ke atas saat pindah halaman
      if (shouldScrollToTop) {
        window.scrollTo({ top: 0, behavior: "smooth" });
      }
    }
  };

  // Ganti tab aktif, reset ke halaman 1, lalu fetch ulang
  const switchTab = (tab) => {
    if (state.activeTab === tab) return;
    state.activeTab = tab;
    state.selectedAppointment = null;
    state.appointments = [];
    state.currentPage = 1;

    fetchAppointments(true);
  };

  return {
    fetchAppointments,
    switchTab,
  };
}
