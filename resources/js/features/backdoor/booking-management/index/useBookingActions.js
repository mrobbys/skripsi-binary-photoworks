import route from "@/lib/route";
import { Toast, confirmModal } from "@/lib/sweetalert";
import axiosInstance from "@/lib/axiosInstance";

export default function useBookingActions({  table }) {
  const settle = async (bookingCode) => {
    const confirmed = await confirmModal(
      "Tandai Lunas?",
      `Booking ${bookingCode} ubah status menjadi Lunas.`,
      "warning",
      "Ya, Tandai Lunas",
    );
    if (!confirmed.isConfirmed) return;

    try {
      const res = await axiosInstance.patch(route("backdoor.booking-management.settle", bookingCode));
      Toast.fire({ icon: "success", title: res.data.message });
      table.reload();
    } catch (err) {
      Toast.fire({ icon: "error", title: err?.response?.data?.message ?? "Gagal memproses pelunasan." });
    }
  };


  const cancel = async (bookingCode) => {
    if (!bookingCode) return;

    const confirmed = await confirmModal(
      "Batalkan Booking?",
      `Booking ${bookingCode} akan dibatalkan secara permanen.`,
      "warning",
      "Ya, Batalkan",
    );
    if (!confirmed.isConfirmed) return;

    try {
      const res = await axiosInstance.patch(route("backdoor.booking-management.cancel", bookingCode));
      Toast.fire({ icon: "success", title: res.data.message });
      table.reload();
    } catch (err) {
      if (err.response?.status === 422) {
        Toast.fire({ icon: "error", title: err?.response?.data?.message ?? "Gagal membatalkan booking." });
        return;
      }

      const msg = err?.response?.data?.message || err.message;
      Toast.fire({ icon: "error", title: msg || "Terjadi kesalahan." });
    }
  };

  return { settle, cancel };
}
