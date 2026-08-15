import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
import useDatatable from "@/lib/useDatatable";
import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";
import useState from "./useState";
import useActions from "./useActions";

export default function Schedule(Alpine) {
  let fpInstances = [];
  const state = useState(Alpine);

  const {
    state: table,
    fetch,
    reload,
  } = useDatatable(Alpine, route("backdoor.data-master.schedule.data"), {
    useHistory: true,
    onError: () => Toast.fire({ icon: "error", title: "Gagal memuat jadwal" }),
  });

  Object.assign(table, { fetch, reload });

  const { saveScheduleTime, toggleScheduleStatus } = useActions({ state, table });

  const initTimePicker = (el, item, type) => {
    if (el._flatpickr) {
      el._flatpickr.destroy();
    }

    const fp = flatpickr(el, {
      enableTime: true,
      noCalendar: true,
      dateFormat: "H:i",
      time_24hr: true,
      defaultDate: item[type],
      disableMobile: true,
      allowInput: false,
      onClose(selectedDates, dateStr) {
        if (dateStr && dateStr !== item[type]) {
          if (type === "start_time") {
            saveScheduleTime(item.id, dateStr, item.end_time);
          } else {
            saveScheduleTime(item.id, item.start_time, dateStr);
          }
        }
      },
    });

    fpInstances.push(fp);
  };

  return {
    state,
    table,
    init() {
      fetch();
    },
    destroy() {
      fpInstances.forEach((fp) => {
        if (fp && typeof fp.destroy === "function") fp.destroy();
      });
      fpInstances = [];
    },
    initTimePicker,
    saveScheduleTime,
    toggleScheduleStatus,
  };
}
