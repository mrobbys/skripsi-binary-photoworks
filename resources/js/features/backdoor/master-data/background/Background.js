import { tableActionDropdown, tooltipDirective } from "@/lib/tippy";
import useDatatable from "@/lib/useDatatable";
import useState from "./useState";
import useBackgroundForm from "./useBackgroundForm";
import useBackgroundActions from "./useBackgroundActions";
import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";

export default function Background(Alpine) {
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
  } = useDatatable(Alpine, route("backdoor.data-master.background.data"), {
    useHistory: true,
    onSuccess: (res) => {
      if (res.total_active_backgrounds !== undefined) state.totalActiveBackgrounds = res.total_active_backgrounds;
      if (res.total_backgrounds !== undefined) state.totalBackgrounds = res.total_backgrounds;
    },
    onError: () => Toast.fire({ icon: "error", title: "Gagal memuat data background" }),
  });

  Object.assign(table, { fetch, setSearch, nextPage, prevPage, goToPage, reload, getPages });

  const init = () => fetch();

  const { openDrawer, openEditDrawer, closeDrawer, submitBackground, validateField, initFilePond } =
    useBackgroundForm({
      Alpine,
      state,
      table,
    });

  const { toggleBackgroundStatus, destroyBackground, openImagePreview, closeImagePreview } = useBackgroundActions({
    state,
    table,
  });

  return {
    state,
    table,
    init,
    openDrawer,
    openEditDrawer,
    closeDrawer,
    submitBackground,
    validateField,
    initFilePond,
    toggleBackgroundStatus,
    destroyBackground,
    openImagePreview,
    closeImagePreview,
  };
}
