import useState from "./useState";
import useBookingActions from "./useBookingActions";
import useUpsellAddon from "./useUpsellAddon";
import route from "@/lib/route";
import axiosInstance from "@/lib/axiosInstance";
import { Toast } from "@/lib/sweetalert";
import useChoices from "@/lib/useChoices";
import formatRupiah from "@/utils/formatRupiah";

export default function Show(Alpine) {
  if (Alpine) {
    Alpine.data("choices", useChoices);
  }

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
      Toast.fire({ icon: "error", title: "Gagal memuat data booking" });
    } finally {
      state.isPageLoading = false;
    }
  };

  Alpine.effect(() => {
    if (state.upsell.addonId) {
      const addon = state.allAddons.find((a) => a.id == state.upsell.addonId);
      if (addon && !addon.has_quantity && state.upsell.quantity !== 1) {
        state.upsell.quantity = 1;
      }
    }
  });

  const mount = (bookingId, bookingCode, addons) => {
    state.bookingId = bookingId;
    state.bookingCode = bookingCode;
    state.allAddons = addons;
    fetchBooking();
  };

  const { settle, submitGdrive, refund, validateField } = useBookingActions({ state, fetchBooking });
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
    formatRupiah,
    validateField,
  };
}
