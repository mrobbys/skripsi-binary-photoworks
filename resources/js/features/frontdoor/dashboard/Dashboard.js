import useDashboardState from "./useDashboardState.js";
import useAppointments from "./useAppointments.js";
import useDetail from "./useDetail.js";
import usePayment from "./usePayment.js";
import usePagination from "./usePagination.js";
import useCancel from "./useCancel.js";
import useReschedule from "./useReschedule.js";

export default function Dashboard(Alpine) {
  const state = useDashboardState(Alpine);

  const { fetchAppointments, switchTab } = useAppointments({ state });
  const { showDetail, clearDetail, hasDetail } = useDetail({ state });
  const { triggerRepay } = usePayment({ state, fetchAppointments });
  const { nextPage, prevPage, goToPage, getPages } = usePagination({ state, fetchCallback: fetchAppointments });
  const { triggerCancel } = useCancel({ state, fetchAppointments, clearDetail });
  const {
    openRescheduleDrawer,
    closeRescheduleDrawer,
    fetchRescheduleSlots,
    selectRescheduleSlot,
    initRescheduleCalendar,
    submitReschedule,
  } = useReschedule({ state, fetchAppointments, clearDetail });

  const init = () => {
    fetchAppointments();
  };

  return {
    state,
    init,

    // pagination
    nextPage,
    prevPage,
    goToPage,
    getPages,

    // appointments
    fetchAppointments,
    switchTab,

    // detail
    showDetail,
    clearDetail,
    hasDetail,

    // action pembayaran
    triggerRepay,

    // action batalkan
    triggerCancel,

    // drawer reschedule
    openRescheduleDrawer,
    closeRescheduleDrawer,
    fetchRescheduleSlots,
    selectRescheduleSlot,
    initRescheduleCalendar,
    submitReschedule,
  };
}
