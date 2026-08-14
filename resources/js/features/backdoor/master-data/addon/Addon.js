import { tableActionDropdown, tooltipDirective } from "@/lib/tippy";
import useDatatable from "@/lib/useDatatable";
import useState from "./useState";
import useAddonForm from "./useAddonForm";
import useAddonActions from "./useAddonActions";
import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";
import formatRupiah from "@/utils/formatRupiah";

export default function Addon(Alpine) {
  Alpine.data("tableActionDropdown", tableActionDropdown);
  tooltipDirective(Alpine);
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
  } = useDatatable(Alpine, route("backdoor.data-master.addon.data"), {
    useHistory: true,
    onSuccess: (res) => {
      if (res.total_addons !== undefined) state.totalAddons = res.total_addons;
      if (res.total_active_addons !== undefined) state.totalActiveAddons = res.total_active_addons;
    },
    onError: () => Toast.fire({ icon: "error", title: "Gagal memuat data add-on." }),
  });

  Object.assign(table, { fetch, setSearch, nextPage, prevPage, goToPage, reload, getPages });

  const init = () => fetch();

  const { openDrawer, openEditDrawer, closeDrawer, submitAddon } = useAddonForm({
    state,
    table,
  });

  const { toggleAddonStatus, destroyAddon } = useAddonActions({ state, table });

  return {
    state,
    table,
    init,

    // Drawer Form
    openDrawer,
    openEditDrawer,
    closeDrawer,
    submitAddon,

    // Actions
    toggleAddonStatus,
    destroyAddon,
    formatRupiah,
  };
}
