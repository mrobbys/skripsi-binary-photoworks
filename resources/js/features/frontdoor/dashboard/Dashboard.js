import useState from "./useState.js";
import useAppointments from "./useAppointments.js";
import useDetail from "./useDetail.js";
import usePayment from "./usePayment.js";
import usePagination from "./usePagination.js";

export default function Dashboard(Alpine) {
  const state = useState(Alpine);

  const { fetchAppointments, filteredAppointments, switchTab } = useAppointments({ state });
  const { showDetail, clearDetail, hasDetail } = useDetail({ state });
  const { triggerRepay } = usePayment({ state, fetchAppointments });
  const paginationControls = usePagination({ state, fetchCallback: fetchAppointments });


  const init = () => {
    fetchAppointments();
  };

  return {
    state,
    init,

    ...paginationControls,
    
    // Appointments
    fetchAppointments,
    filteredAppointments,
    switchTab,

    // Detail
    showDetail,
    clearDetail,
    hasDetail,

    // Payment
    triggerRepay,
  };
}
