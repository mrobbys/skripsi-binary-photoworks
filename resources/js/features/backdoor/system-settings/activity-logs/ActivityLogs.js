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
    setTimeout(() => modal.properties = null, 500);
  };

  const formatProperties = (properties) => {
    if (!properties) return "Tidak ada data perubahan";
    if (typeof properties === "string") {
      try {
        return JSON.stringify(JSON.parse(properties), null, 2);
      } catch {
        return properties;
      }
    }
    return JSON.stringify(properties, null, 2);
  };

  const badgeClass = (description) => {
    const map = {
      created: "text-lime-800 bg-lime-100",
      updated: "text-yellow-800 bg-yellow-100",
      deleted: "text-red-800 bg-red-100",
    };
    return map[description] ?? "text-stone-700 bg-stone-200";
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
