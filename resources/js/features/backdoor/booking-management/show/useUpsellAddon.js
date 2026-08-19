import route from "@/lib/route";
import { Toast, confirmModal } from "@/lib/sweetalert";
import axiosInstance from "@/lib/axiosInstance";

export default function useUpsellAddon({ state, fetchBooking }) {
  const submitUpsell = async () => {
    if (!state.upsell.addonId) {
      Toast.fire({ icon: "warning", title: "Pilih layanan terlebih dahulu" });
      return;
    }
    state.upsell.isLoading = true;

    const addon = state.allAddons.find((a) => a.id == state.upsell.addonId);
    const quantity = addon && !addon.has_quantity ? 1 : state.upsell.quantity;

    try {
      const res = await axiosInstance.post(route("backdoor.booking-management.addons.upsell", state.bookingCode), {
        addon_id: state.upsell.addonId,
        quantity,
      });
      Toast.fire({ icon: "success", title: res.data?.message || "Layanan tambahan berhasil ditambahkan" });
      state.upsell.addonId = null;
      state.upsell.quantity = 1;
      await fetchBooking();
    } catch (err) {
      if (err.response?.status === 422) {
        Toast.fire({
          icon: "error",
          title: err.response?.data?.message || "Data tidak valid untuk menambah layanan",
        });
      } else {
        Toast.fire({
          icon: "error",
          title: "Terjadi kesalahan sistem saat menambahkan layanan",
        });
      }
    } finally {
      state.upsell.isLoading = false;
    }
  };

  const removeAddon = async (addonId) => {
    if (!addonId) return;

    const isConfirm = await confirmModal(
      "Hapus Layanan?",
      "Layanan akan dihapus dari pemesanan ini",
      "warning",
      "Ya, Hapus"
    );

    if (isConfirm.isConfirmed) {
      state.upsell.isLoading = true;
      try {
        const res = await axiosInstance.delete(
          route("backdoor.booking-management.addons.remove", [state.bookingCode, addonId])
        );
        Toast.fire({ icon: "success", title: res.data?.message || "Layanan tambahan berhasil dihapus" });
        await fetchBooking();
      } catch (err) {
        if (err.response?.status === 422) {
          Toast.fire({
            icon: "error",
            title: err.response?.data?.message || "Data tidak valid untuk menghapus layanan",
          });
        } else {
          Toast.fire({
            icon: "error",
            title: "Terjadi kesalahan sistem saat menghapus layanan",
          });
        }
      } finally {
        state.upsell.isLoading = false;
      }
    }
  };

  const onUpsellAddonChange = () => {
    const addon = state.allAddons.find((a) => a.id == state.upsell.addonId);
    if (addon && !addon.has_quantity) {
      state.upsell = { ...state.upsell, quantity: 1 };
    }
  };

  return { submitUpsell, onUpsellAddonChange, removeAddon };
}
