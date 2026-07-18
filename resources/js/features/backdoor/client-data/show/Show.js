import useDatatable from "@/lib/useDatatable";
import route from "@/lib/route";

export default function Show(Alpine) {
  const getUserUuid = () => document.getElementById("show-root")?.dataset?.userUuid ?? "";

  const { state: table, ...methods } = useDatatable(Alpine, route("backdoor.client-data.bookings", getUserUuid()));

  Object.assign(table, methods);

  table.fetch();

  return {
    table,
  };
}
