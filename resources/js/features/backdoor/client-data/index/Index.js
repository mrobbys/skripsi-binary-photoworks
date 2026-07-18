import useDatatable from "@/lib/useDatatable";
import route from "@/lib/route";

export default function Index(Alpine) {
  const { state: table, ...methods } = useDatatable(Alpine, route("backdoor.client-data.data"));

  Object.assign(table, methods);

  table.fetch();
  
  return {
    table,
  };
}
