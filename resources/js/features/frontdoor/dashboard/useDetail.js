export default function useDetail({ state }) {
  /**
   * Tampilkan detail booking yang diklik
   * Perbarui URL tanpa refresh halaman
   */
  const showDetail = (appointment) => {
    state.selectedAppointment = appointment;

    const newUrl = window.location.pathname + "?booking=" + appointment.booking_code;
    window.history.replaceState({}, "", newUrl);
  };

  // tutup / reset panel detail & kembalikan URL ke kondisi bersih
  const clearDetail = () => {
    state.selectedAppointment = null;
    window.history.replaceState({}, "", window.location.pathname);
  };

  // apakah ada detail yang sedang ditampilkan?
  const hasDetail = () => state.selectedAppointment !== null;

  return {
    showDetail,
    clearDetail,
    hasDetail,
  };
}
