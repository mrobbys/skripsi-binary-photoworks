import axiosInstance from "@/lib/axiosInstance";
import route from "@/lib/route";
import { Toast, confirmModal } from "@/lib/sweetalert";

export default function useCancel({ state, fetchAppointments, clearDetail }) {
  // Batalkan booking
  const triggerCancel = async (bookingCode) => {
    const result = await confirmModal(
      "Batalkan Reservasi?",
      "Tindakan ini tidak dapat dibatalkan. Slot waktu Anda akan dilepas.",
      "warning",
      "Ya, Batalkan",
    );

    if (!result.isConfirmed) return;

    state.isCancelling = bookingCode;

    try {
      const res = await axiosInstance.post(route("frontdoor.dashboard.cancel"), {
        booking_code: bookingCode,
      });

      if (!res.data.success) {
        throw new Error(res.data.message || "Gagal membatalkan booking.");
      }

      Toast.fire({ icon: "success", title: "Reservasi berhasil dibatalkan." });

      await fetchAppointments();
    } catch (err) {
      if (err?.response?.status === 422) {
        Toast.fire({ icon: "error", title: err.response.data.message ?? "Data tidak valid." });
      } else {
        Toast.fire({ icon: "error", title: "Gagal membatalkan reservasi. Silakan coba lagi." });
      }
    } finally {
      state.isCancelling = null;
      clearDetail();
    }
  };

  return { triggerCancel };
}
