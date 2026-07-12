import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";
import axiosInstance from "@/lib/axiosInstance";

export default function useCheckout({ state, buildAddonsPayload }) {
  const triggerCheckout = async () => {
    state.isProcessing = true;
    try {
      const addons = buildAddonsPayload();
      const payload = {
        package_variant_id: state.selectedVariantId,
        background_id: state.selectedBackgroundId,
        booking_date: state.selectedDate,
        start_time: state.selectedSlot?.start_time,
        payment_scheme: state.paymentScheme,
        keterangan: state.keterangan,
        ...(addons.length && { addons }),
      };

      const { data } = await axiosInstance.post(route("frontdoor.booking.checkout"), payload);

      state.bookingCode = data.booking_code;

      const finishCheckout = (icon, title) => {
        Toast.fire({ icon, title });
        state.isProcessing = false;
        window.location.href = route("frontdoor.dashboard.index");
      };

      window.snap.pay(data.snap_token, {
        onSuccess: () =>
          (window.location.href = route("frontdoor.booking.success", { bookingCode: data.booking_code })),
        onPending: () => finishCheckout("info", "Menunggu pembayaran diselesaikan."),
        onError: () => finishCheckout("error", "Pembayaran gagal. Silakan coba lagi."),
        onClose: () => finishCheckout("warning", "Pembayaran dibatalkan. Slot masih tersimpan."),
      });
    } catch (err) {
      const msg = err?.response?.data?.message || err.message || "Terjadi kesalahan.";
      Toast.fire({ icon: "error", title: msg });
      state.isProcessing = false;
      console.error(msg);
    }
  };

  return { triggerCheckout };
}
