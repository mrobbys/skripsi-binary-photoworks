import useState from "./useState";
import useBookingActions from "./useBookingActions";
import useUpsellAddon from "./useUpsellAddon";
import route from "@/lib/route";
import axiosInstance from "@/lib/axiosInstance";
import { Toast } from "@/lib/sweetalert";

export default function Show(Alpine) {
  const state = useState(Alpine);

  const fetchBooking = async () => {
    try {
      const res = await axiosInstance.get(route("backdoor.booking-management.show-data", state.bookingCode));
      const data = res.data.data;
      state.booking = data.booking;
      state.summary = data.summary;
      state.gdriveLink = data.booking.gdrive_link ?? "";
    } catch (err) {
      console.error(err);
      Toast.fire({ icon: "error", title: "Gagal memuat data." });
    } finally {
      state.isPageLoading = false;
    }
  };

  const mount = (bookingId, bookingCode, addons) => {
    state.bookingId = bookingId;
    state.bookingCode = bookingCode;
    state.allAddons = addons;
    fetchBooking();
  };

  const { settle, submitGdrive, refund } = useBookingActions({ state, fetchBooking });
  const { submitUpsell, onUpsellAddonChange, removeAddon } = useUpsellAddon({ state, fetchBooking });

  return {
    fetchBooking,
    state,
    mount,
    settle,
    submitGdrive,
    refund,
    submitUpsell,
    onUpsellAddonChange,
    removeAddon,
    formatRupiah: (num) => "Rp " + Number(num).toLocaleString("id-ID"),
  };
}
