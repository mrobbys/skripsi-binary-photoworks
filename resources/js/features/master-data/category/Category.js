import route from "../../../lib/route";
import useDatatable from "../../../lib/useDatatable";
import useForm from "./useForm";
import useActions from "./useActions";
import useState from "./useState";
import { Toast } from "../../../lib/sweetalert";

export default function Category(Alpine) {
  const state = useState(Alpine);

  const { state: tableState, fetch, setSearch, nextPage, prevPage, goToPage, reload, getPages } = useDatatable(
    Alpine,
    route("backdoor.data-master.category.index"),
    {
      onSuccess: (res) => {
        if (res.active_count !== undefined) {
          state.activeCount = res.active_count;
        }
      },
      onError: () => {
        Toast.fire({ icon: "error", title: "Gagal memuat data tabel." });
      },
    },
  );

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

  const { openModal, closeModal, editCategory, submitForm } = useForm({ state, table });
  const { toggleCategoryStatus, destroyCategory } = useActions({ state, table });

  return {
    state,
    table,
    init,
    openModal,
    closeModal,
    submitForm,
    toggleCategoryStatus,
    editCategory,
    destroyCategory,
  };
}
