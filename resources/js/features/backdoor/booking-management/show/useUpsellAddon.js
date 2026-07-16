import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";
import axiosInstance from "@/lib/axiosInstance";

export default function useUpsellAddon({ state, fetchBooking }) {
  const submitUpsell = async () => {
    if (!state.upsell.addonId) {
      Toast.fire({ icon: "warning", title: "Pilih layanan." });
      return;
    }
    state.upsell.isLoading = true;

    try {
      const res = await axiosInstance.post(route("backdoor.booking-management.addons.upsell", state.bookingCode), {
        addon_id: state.upsell.addonId,
        quantity: state.upsell.quantity,
      });
      Toast.fire({ icon: "success", title: res.data.message });
      state.upsell.addonId = null;
      state.upsell.quantity = 1;
      await fetchBooking();
    } catch (err) {
      Toast.fire({ icon: "error", title: err?.response?.data?.message ?? "Gagal menambahkan." });
    } finally {
      state.upsell.isLoading = false;
    }
  };

  const onUpsellAddonChange = () => {
    const addon = state.allAddons.find((a) => a.id == state.upsell.addonId);
    if (addon && !addon.has_quantity) state.upsell.quantity = 1;
  };

  return { submitUpsell, onUpsellAddonChange };
}
