import { tableActionDropdown } from "@/lib/tippy";
import useDatatable from "@/lib/useDatatable";
import useState from "./useState";
import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";
import flatpickr from "flatpickr";

export default function Index(Alpine) {
  Alpine.data("tableActionDropdown", tableActionDropdown);
  const state = useState(Alpine);

  let datePickerInstance = null;

  const {
    state: table,
    fetch,
    setSearch,
    nextPage,
    prevPage,
    goToPage,
    reload,
    getPages,
  } = useDatatable(Alpine, route("backdoor.session-schedule.list.data"), {
    useHistory: true,
    extraParams: () => ({
      date: state.dateFilter || undefined,
    }),
    onSuccess: (res) => {
      if (res.total_today !== undefined) state.totalToday = res.total_today;
      if (res.done_today !== undefined) state.doneToday = res.done_today;
      if (res.upcoming_total !== undefined) state.upcomingTotal = res.upcoming_total;
    },
    onError: () => Toast.fire({ icon: "error", title: "Gagal memuat data jadwal." }),
  });

  Object.assign(table, { fetch, setSearch, nextPage, prevPage, goToPage, reload, getPages });

  const setDateFilter = (dateStr) => {
    state.dateFilter = dateStr;
    table.pagination.current_page = 1;
    fetch();
  };

  const clearDateFilter = () => {
    state.dateFilter = "";
    if (datePickerInstance) datePickerInstance.clear();
  };

  return {
    state,
    table,

    init() {
      fetch();

      this.$watch("table.isLoading", (isLoading) => {
        if (datePickerInstance && datePickerInstance.altInput) {
          datePickerInstance.altInput.disabled = isLoading;
        }
      });

      this.$nextTick(() => {
        datePickerInstance = flatpickr(this.$refs.dateFilterInput, {
          altInput: true,
          altFormat: "d M Y",
          dateFormat: "Y-m-d",
          disableMobile: true,
          onChange: (selectedDates, dateStr) => {
            setDateFilter(dateStr || "");
          },
        });
      });
    },

    setDateFilter,
    clearDateFilter,
  };
}
