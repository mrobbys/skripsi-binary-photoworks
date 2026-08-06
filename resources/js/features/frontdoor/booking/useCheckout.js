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
        notes: state.notes,
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
      if (err.response?.status === 422) {
        Toast.fire({
          icon: "error",
          title:
            err?.response?.data?.message ??
            "Terjadi kesalahan koneksi atau server bermasalah. Silakan coba beberapa saat lagi.",
        });
      } else {
        Toast.fire({
          icon: "error",
          title: "Terjadi kesalahan koneksi atau server bermasalah. Silakan coba beberapa saat lagi.",
        });
      }
      console.error(err);
      state.isProcessing = false;
    }
  };

  return { triggerCheckout };
}
