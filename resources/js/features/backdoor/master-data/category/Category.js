import { tableActionDropdown } from "@/lib/tippy";
import route from "@/lib/route";
import useDatatable from "@/lib/useDatatable";
import useForm from "./useForm";
import useActions from "./useActions";
import useState from "./useState";
import { Toast } from "@/lib/sweetalert";

export default function Category(Alpine) {
  Alpine.data("tableActionDropdown", tableActionDropdown);
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
  } = useDatatable(Alpine, route("backdoor.data-master.category.data"), {
    useHistory: true,
    onSuccess: (res) => {
      if (res.total !== undefined) state.totalCategory = res.total;
      if (res.active_count !== undefined) state.activeCount = res.active_count;
    },
    onError: () => {
      Toast.fire({ icon: "error", title: "Gagal memuat data tabel." });
    },
  });

  Object.assign(table, { fetch, setSearch, nextPage, prevPage, goToPage, reload, getPages });

  const init = function () {
    fetch();
  };

  const { openDrawer, closeDrawer, editCategory, validateField, submitCategory } = useForm({ Alpine, state, table });
  const { toggleCategoryStatus, destroyCategory } = useActions({ state, table });

  return {
    state,
    table,
    init,
    openDrawer,
    closeDrawer,
    validateField,
    submitCategory,
    toggleCategoryStatus,
    editCategory,
    destroyCategory,
  };
}
