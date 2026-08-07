import { tableActionDropdown } from "@/lib/tippy";
import useDatatable from "@/lib/useDatatable";
import route from "@/lib/route";

export default function Index(Alpine) {
  Alpine.data("tableActionDropdown", tableActionDropdown);
  const { state: table, ...methods } = useDatatable(Alpine, route("backdoor.client-data.data"), {
    useHistory: true,
  });

  Object.assign(table, methods);

  table.fetch();
  
  return {
    table,
  };
}
