import useDatatable from "@/lib/useDatatable";
import route from "@/lib/route";

export default function ActivityLogs(Alpine) {
  const { state: table, ...methods } = useDatatable(Alpine, route("backdoor.system-settings.activity-logs.data"), {
    useHistory: true,
  });
  Object.assign(table, methods);

  const modal = Alpine.reactive({
    isOpen: false,
    properties: null,
  });

  const openDetail = (properties) => {
    modal.properties = properties;
    modal.isOpen = true;
  };

  const closeDetail = () => {
    modal.isOpen = false;
    modal.properties = null;
  };

  const formatProperties = (properties) => {
    if (!properties) return "Tidak ada data perubahan.";
    return JSON.stringify(properties, null, 2);
  };

  const badgeClass = (description) => {
    const map = {
      created: "text-lime-700 bg-lime-100",
      updated: "text-yellow-700 bg-yellow-100",
      deleted: "text-red-700 bg-red-100",
    };
    return map[description] ?? "text-stone-600 bg-stone-200";
  };

  return {
    table,
    modal,
    openDetail,
    closeDetail,
    formatProperties,
    badgeClass,
    init() {
      table.fetch();
    },
  };
}
