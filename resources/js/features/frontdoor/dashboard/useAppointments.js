import route from "@/lib/route";

export default function useAppointments({ state }) {
  // Ambil data booking dari backend berdasarkan tab dan halaman (page)
  const fetchAppointments = async () => {
    state.isLoading = true;
    state.appointments = [];
    try {
      const res = await window.axios.get(route("frontdoor.dashboard.appointments"), {
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
      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  };

  // Ganti tab aktif, reset ke halaman 1, lalu fetch ulang
  const switchTab = (tab) => {
    if(state.activeTab === tab) return;
    state.activeTab = tab;
    state.selectedAppointment = null;
    state.appointments = [];
    state.currentPage = 1;

    fetchAppointments();
  };

  return {
    fetchAppointments,
    switchTab,
  };
}
