import route from "../../../lib/route";
import useDatatable from "../../../lib/useDatatable";
import useForm from "./useForm";
import useActions from "./useActions";
import useState from "./useState";

export default function Category(Alpine) {
  // panggil useDatatable
  const table = useDatatable(Alpine, route("backdoor.data-master.category.index"));

  // otomatis mengambil activeCount dari response server setiap tabel di-load/reload
  const originalFetch = table.fetch.bind(table);
  table.fetch = async () => {
    const res = await originalFetch();
    if (res && res.active_count !== undefined) {
      state.activeCount = res.active_count;
    }
    return res;
  };

  // State
  const state = useState(Alpine);

  const init = function () {
    // fetch tabel
    table.fetch();

    // Watch search input to trigger fetch with reset page
    if (this && typeof this.$watch === "function") {
      this.$watch("table.search", () => {
        table.pagination.current_page = 1;
        table.fetch();
      });
    }
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
