import dayjs from "dayjs";
import "dayjs/locale/id";

dayjs.locale("id");

export default function formatDate(date, format = "DD MMMM YYYY") {
  if (!date) return "-";
  return dayjs(date).format(format);
}
