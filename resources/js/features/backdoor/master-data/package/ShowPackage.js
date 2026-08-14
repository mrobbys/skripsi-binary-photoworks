import { tableActionDropdown } from "@/lib/tippy";
import axiosInstance from "@/lib/axiosInstance";
import route from "@/lib/route";
import useDatatable from "@/lib/useDatatable";
import useVariantForm from "./useVariantForm";
import useVariantActions from "./useVariantActions";
import { Toast } from "@/lib/sweetalert";
import formatRupiah from "@/utils/formatRupiah";
import useState from "./useState";
import usePackageForm from "./usePackageForm";

export default function ShowPackage(Alpine) {
  Alpine.data("tableActionDropdown", tableActionDropdown);
  const state = useState(Alpine);

  state.packageSlug = null;

  const {
    state: table,
    fetch,
    nextPage,
    prevPage,
    goToPage,
    reload,
    getPages,
  } = useDatatable(Alpine, () => route("backdoor.data-master.package.variants.index", state.packageSlug ?? ""), {
    onError: () => Toast.fire({ icon: "error", title: "Gagal memuat data varian." }),
  });

  Object.assign(table, { fetch, nextPage, prevPage, goToPage, reload, getPages });

  const initData = async function (slug) {
    state.packageSlug = slug;
    fetch(); // Load variant table
    await fetchPackageInfo(); // Load package details
  };

  const fetchPackageInfo = async () => {
    try {
      const res = await axiosInstance.get(route("backdoor.data-master.package.info", state.packageSlug));
      state.packageInfo = res.data.data;
    } catch (error) {
      Toast.fire({ icon: "error", title: "Gagal memuat detail paket" });
      console.error(error);
    }
  };

  const { openEditDrawer, closeDrawer, addFeature, removeFeature, submitPackage } = usePackageForm({
    state,
    table,
  });

  const { openVariantDrawer, closeVariantDrawer, editVariant, addVariantFeature, removeVariantFeature, submitVariant } =
    useVariantForm({ state, table });

  const { toggleVariantStatus, destroyVariant } = useVariantActions({ state, table });

  return {
    state,
    table,
    initData,

    // Package Drawer
    openEditDrawer,
    closeDrawer,
    addFeature,
    removeFeature,
    submitPackage,

    // Variant Drawer
    openVariantDrawer,
    closeVariantDrawer,
    editVariant,
    addVariantFeature,
    removeVariantFeature,
    submitVariant,

    // Variant Actions
    toggleVariantStatus,
    destroyVariant,

    formatRupiah,
  };
}
