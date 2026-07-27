import useDatatable from "@/lib/useDatatable";
import useState from "./useState";
import useBookingActions from "./useBookingActions";
import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";
import formatRupiah from "@/utils/formatRupiah";
import formatDate from "@/utils/formatDate";

export default function Index(Alpine) {
  const state = useState(Alpine);

  const {
    state: table,
    fetch,
    setSearch,
    nextPage,
    prevPage,
    goToPage,
    reload,
    getPages,
  } = useDatatable(Alpine, route("backdoor.booking-management.data"), {
    useHistory: true,
    onSuccess: (res) => {
      if (res.total_revenue !== undefined) state.totalRevenue = res.total_revenue;
      if (res.count_success !== undefined) state.countSuccess = res.count_success;
      if (res.count_dp_paid !== undefined) state.countDpPaid = res.count_dp_paid;
    },
    onError: () => Toast.fire({ icon: "error", title: "Gagal memuat data pemesanan." }),
  });

  Object.assign(table, { fetch, setSearch, nextPage, prevPage, goToPage, reload, getPages });

  const init = () => fetch();

  const { settle, cancel } = useBookingActions({ table });

  return {
    state,
    table,
    init,
    settle,
    cancel,

    formatRupiah,
    formatDate,
  };
}
