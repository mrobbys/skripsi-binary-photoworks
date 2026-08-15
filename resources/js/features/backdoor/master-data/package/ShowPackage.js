import { tableActionDropdown } from "@/lib/tippy";
import axiosInstance from "@/lib/axiosInstance";
import route from "@/lib/route";
import useDatatable from "@/lib/useDatatable";
import useChoices from "@/lib/useChoices";
import useVariantForm from "./useVariantForm";
import useVariantActions from "./useVariantActions";
import { Toast } from "@/lib/sweetalert";
import formatRupiah from "@/utils/formatRupiah";
import usePackageState from "./usePackageState";
import useVariantState from "./useVariantState";
import usePackageForm from "./usePackageForm";

export default function ShowPackage(Alpine) {
  Alpine.data("tableActionDropdown", tableActionDropdown);
  Alpine.data("packageChoices", useChoices);
  const state = Alpine.reactive({
    isLoading: false,
    packageSlug: null,
    ...usePackageState(),
    ...useVariantState(),
  });

  const {
    state: table,
    fetch,
    nextPage,
    prevPage,
    goToPage,
    reload,
    getPages,
  } = useDatatable(Alpine, () => route("backdoor.data-master.package.variants.index", state.packageSlug ?? ""), {
    onError: () => Toast.fire({ icon: "error", title: "Gagal memuat data varian" }),
  });

  Object.assign(table, { fetch, nextPage, prevPage, goToPage, reload, getPages });

  const initData = async function (slug) {
    state.packageSlug = slug;
    fetch();
    await fetchPackageInfo();
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

  const { openEditDrawer, closeDrawer, addFeature, removeFeature, submitPackage, validateField, initFilePond } =
    usePackageForm({
      Alpine,
      state,
      table,
    });

  const {
    openVariantDrawer,
    closeVariantDrawer,
    editVariant,
    addVariantFeature,
    removeVariantFeature,
    onPriceInput,
    validateVariantField,
    submitVariant,
  } = useVariantForm({ Alpine, state, table });

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
    validateField,
    initFilePond,

    // Variant Drawer
    openVariantDrawer,
    closeVariantDrawer,
    editVariant,
    addVariantFeature,
    removeVariantFeature,
    onPriceInput,
    validateVariantField,
    submitVariant,

    // Variant Actions
    toggleVariantStatus,
    destroyVariant,

    formatRupiah,
  };
}
