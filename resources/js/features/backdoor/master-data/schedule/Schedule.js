import useDatatable from "@/lib/useDatatable";
import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";
import useState from "./useState";
import useActions from "./useActions";

export default function Schedule(Alpine) {
  const state = useState(Alpine);

  const {
    state: table,
    fetch,
    reload,
  } = useDatatable(Alpine, route("backdoor.data-master.schedule.index"), {
    onError: () => Toast.fire({ icon: "error", title: "Gagal memuat jadwal." }),
  });

  Object.assign(table, { fetch, reload });

  const init = () => fetch();

  const { saveScheduleTime, toggleScheduleStatus } = useActions({ state, table });

  return {
    state,
    table,
    init,
    saveScheduleTime,
    toggleScheduleStatus,
  };
}
