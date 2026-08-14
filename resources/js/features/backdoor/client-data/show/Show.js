import { tableActionDropdown } from "@/lib/tippy";
import useDatatable from "@/lib/useDatatable";
import route from "@/lib/route";

export default function Show(Alpine) {
  Alpine.data("tableActionDropdown", tableActionDropdown);
  const getUserUuid = () => document.getElementById("show-root")?.dataset?.userUuid ?? "";

  const { state: table, ...methods } = useDatatable(Alpine, route("backdoor.client-data.bookings", getUserUuid()), {
    useHistory: true,
  });

  Object.assign(table, methods);

  table.fetch();

  return {
    table,
  };
}
