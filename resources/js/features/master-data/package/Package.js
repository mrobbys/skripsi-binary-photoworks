import route from "../../../lib/route";
import useDatatable from "../../../lib/useDatatable";
import usePackageForm from "./usePackageForm";
import useVariantForm from "./useVariantForm";
import useActions from "./useActions";
import useState from "./useState";
import { Toast } from "../../../lib/sweetalert";

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

  const {
    openDrawer,
    closeDrawer,
    editPackage,
    addFeature,
    removeFeature,
    submitPackage,
  } = usePackageForm({ state, table });

  const {
    openVariantDrawer,
    closeVariantDrawer,
    editVariant,
    addVariantFeature,
    removeVariantFeature,
    submitVariant,
  } = useVariantForm({ state, table });
  
  const { togglePackageStatus, toggleVariantStatus, destroyPackage, destroyVariant } = useActions({ state, table });

  return {
    state,
    table,
    init,
    openDrawer,
    closeDrawer,
    editPackage,
    addFeature,
    removeFeature,
    submitPackage,
    openVariantDrawer,
    closeVariantDrawer,
    editVariant,
    addVariantFeature,
    removeVariantFeature,
    submitVariant,
    togglePackageStatus,
    toggleVariantStatus,
    destroyPackage,
    destroyVariant,
  };
}
