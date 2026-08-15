import { tableActionDropdown } from "@/lib/tippy";
import route from "@/lib/route";
import useDatatable from "@/lib/useDatatable";
import useChoices from "@/lib/useChoices";
import usePackageForm from "./usePackageForm";
import usePackageActions from "./usePackageActions";
import usePackageState from "./usePackageState";
import { Toast } from "@/lib/sweetalert";
import formatRupiah from "@/utils/formatRupiah";

export default function Package(Alpine) {
  Alpine.data("tableActionDropdown", tableActionDropdown);
  Alpine.data("packageChoices", useChoices);
  const state = Alpine.reactive({
    isLoading: false,
    ...usePackageState(),
  });

  const {
    state: table,
    fetch,
    setSearch,
    nextPage,
    prevPage,
    goToPage,
    reload,
    getPages,
  } = useDatatable(Alpine, route("backdoor.data-master.package.data"), {
    useHistory: true,
    onSuccess: (res) => {
      if (res.total !== undefined) state.totalPackages = res.total;
      if (res.total_active_packages !== undefined) state.totalActivePackages = res.total_active_packages;
      if (res.total_active_variants !== undefined) state.totalActiveVariants = res.total_active_variants;
    },
    onError: () => Toast.fire({ icon: "error", title: "Gagal memuat data paket" }),
  });

  Object.assign(table, { fetch, setSearch, nextPage, prevPage, goToPage, reload, getPages });

  const init = () => fetch();

  const {
    openDrawer,
    closeDrawer,
    openEditDrawer,
    addFeature,
    removeFeature,
    submitPackage,
    validateField,
    initFilePond,
  } = usePackageForm({
    Alpine,
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
    validateField,
    initFilePond,
    togglePackageStatus,
    destroyPackage,
    formatRupiah,
  };
}
