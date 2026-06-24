import route from "../../../lib/route";
import useDatatable from "../../../lib/useDatatable";
import usePackageForm from "./usePackageForm";
import usePackageActions from "./usePackageActions";
import useState from "./useState";
import { Toast } from "../../../lib/sweetalert";
import formatRupiah from "../../../utils/formatRupiah";

export default function Package(Alpine) {
  const state = useState(Alpine);

  const {
    state: tableState,
    fetch,
    setSearch,
    nextPage,
    prevPage,
    goToPage,
    reload,
    getPages,
  } = useDatatable(Alpine, route("backdoor.data-master.package.index"), {
    onSuccess: (res) => {
      if (res.total_packages !== undefined) state.totalPackages = res.total_packages;
      if (res.total_active_packages !== undefined) state.totalActivePackages = res.total_active_packages;
      if (res.total_active_variants !== undefined) state.totalActiveVariants = res.total_active_variants;
    },
    onError: () => Toast.fire({ icon: "error", title: "Gagal memuat data tabel." }),
  });

  tableState.fetch = fetch;
  tableState.setSearch = setSearch;
  tableState.nextPage = nextPage;
  tableState.prevPage = prevPage;
  tableState.goToPage = goToPage;
  tableState.reload = reload;
  tableState.getPages = getPages;

  const table = tableState;

  const init = function () {
    fetch();
  };

  const { openDrawer, closeDrawer, openEditDrawer, addFeature, removeFeature, submitPackage } =
    usePackageForm({
      state,
      table,
    });

  const { togglePackageStatus, destroyPackage } = usePackageActions({ state, table });

  return {
    state,
    table,
    init,
    openDrawer,
    closeDrawer,
    openEditDrawer,
    addFeature,
    removeFeature,
    submitPackage,
    togglePackageStatus,
    destroyPackage,
    formatRupiah,
  };
}
