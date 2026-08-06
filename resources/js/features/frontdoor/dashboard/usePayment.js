import axiosInstance from "@/lib/axiosInstance";
import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";

export default function usePayment({ state, fetchAppointments }) {
  // Trigger "Bayar Sekarang" dari dashboard
  const triggerRepay = async (bookingCode) => {
    state.isProcessingPayment = bookingCode;

    try {
      const res = await axiosInstance.post(route("frontdoor.dashboard.repay"), {
        booking_code: bookingCode,
      });

      if (!res.data.success) {
        throw new Error(res.data.message || "Gagal mendapatkan token pembayaran.");
      }

      const snapToken = res.data.snap_token;

      if (typeof window.snap === "undefined") {
        Toast.fire({
          icon: "error",
          title: "Sistem pembayaran gagal. Silakan muat ulang halaman.",
        });
        state.isProcessingPayment = null;
        return;
      }

      window.snap.pay(snapToken, {
        onSuccess: () => {
          Toast.fire({ icon: "success", title: "Pembayaran berhasil!" });
          state.isProcessingPayment = null;
          state.selectedAppointment = null;
          fetchAppointments();
        },
        onPending: () => {
          Toast.fire({ icon: "info", title: "Menunggu pembayaran diselesaikan." });
          state.isProcessingPayment = null;
          state.selectedAppointment = null;
          fetchAppointments();
        },
        onError: () => {
          Toast.fire({ icon: "error", title: "Pembayaran gagal. Silakan coba lagi." });
          state.isProcessingPayment = null;
        },
        onClose: () => {
          Toast.fire({ icon: "warning", title: "Pembayaran dibatalkan. Tagihan masih tersimpan." });
          state.isProcessingPayment = null;
        },
      });
    } catch (err) {
      if (err?.response?.status === 422) {
        Toast.fire({ icon: "error", title: err.response.data.message ?? "Data tidak valid." });
      } else {
        Toast.fire({ icon: "error", title: "Gagal mendapatkan token pembayaran. Silakan coba lagi." });
      }
      state.isProcessingPayment = null;
      console.error(err);
    }
  };

  return {
    triggerRepay,
  };
}
