import { tableActionDropdown } from "@/lib/tippy";
import useDatatable from "@/lib/useDatatable";
import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";
import useChoices from "@/lib/useChoices";
import useState from "./useState";
import useForm from "./useForm";
import useActions from "./useActions";

export default function Index(Alpine) {
  Alpine.data("tableActionDropdown", tableActionDropdown);
  Alpine.data("userChoices", useChoices);
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
  } = useDatatable(Alpine, route("backdoor.system-settings.users.data"), {
    useHistory: true,
    onError: () => Toast.fire({ icon: "error", title: "Gagal memuat data user" }),
  });

  Object.assign(table, { fetch, setSearch, nextPage, prevPage, goToPage, reload, getPages });

  const roleBadgeClass = (roleName) => {
    const map = {
      admin: "bg-sky-100 text-sky-800 border border-sky-200",
      owner: "bg-amber-100 text-amber-800 border border-amber-200",
    };
    return map[roleName] ?? "bg-stone-100 text-stone-700 border border-stone-200";
  };

  const formMethods = useForm({ Alpine, state, table });
  const actionMethods = useActions({ table });

  return {
    state,
    table,
    roleBadgeClass,
    ...formMethods,
    ...actionMethods,
    init() {
      fetch();
    },
  };
}
