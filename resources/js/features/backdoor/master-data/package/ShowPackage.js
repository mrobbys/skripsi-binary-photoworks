import route from "@/lib/route";
import useDatatable from "@/lib/useDatatable";
import useVariantForm from "./useVariantForm";
import useVariantActions from "./useVariantActions";
import { Toast } from "@/lib/sweetalert";
import formatRupiah from "@/utils/formatRupiah";
import useState from "./useState";
import usePackageForm from "./usePackageForm";

export default function ShowPackage(Alpine) {
  const state = useState(Alpine);

  const {
    state: table,
    fetch,
    nextPage,
    prevPage,
    goToPage,
    reload,
    getPages,
  } = useDatatable(Alpine, () => route("backdoor.data-master.package.variants.index", window.__packageSlug ?? ""), {
    onError: () => Toast.fire({ icon: "error", title: "Gagal memuat data varian." }),
  });

  Object.assign(table, { fetch, nextPage, prevPage, goToPage, reload, getPages });

  const init = function () {
    fetch();
  };

  const { setPackageInfo, openEditDrawer, closeDrawer, addFeature, removeFeature, submitPackage } = usePackageForm({
    state,
    table,
  });

  const { openVariantDrawer, closeVariantDrawer, editVariant, addVariantFeature, removeVariantFeature, submitVariant } =
    useVariantForm({ state, table });

  const { toggleVariantStatus, destroyVariant } = useVariantActions({ state, table });

  return {
    state,
    table,
    init,
    setPackageInfo,

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
