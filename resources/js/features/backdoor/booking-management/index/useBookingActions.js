import route from "@/lib/route";
import { Toast, confirmModal } from "@/lib/sweetalert";
import axiosInstance from "@/lib/axiosInstance";

export default function useBookingActions({ table }) {
  const settle = async (bookingCode) => {
    if (!bookingCode) return;

    const confirmed = await confirmModal(
      "Tandai Lunas?",
      `Booking ${bookingCode} ubah status menjadi Lunas`,
      "warning",
      "Ya, Tandai Lunas"
    );
    if (!confirmed.isConfirmed) return;

    try {
      const res = await axiosInstance.patch(route("backdoor.booking-management.settle", bookingCode));
      Toast.fire({ icon: "success", title: res.data?.message || "Status booking berhasil diubah menjadi Lunas" });
      table.reload();
    } catch (err) {
      if (err.response?.status === 422) {
        Toast.fire({
          icon: "error",
          title: err.response?.data?.message || "Data tidak valid untuk pelunasan",
        });
      } else {
        Toast.fire({
          icon: "error",
          title: "Terjadi kesalahan sistem saat memproses pelunasan",
        });
      }
    }
  };

  const cancel = async (bookingCode) => {
    if (!bookingCode) return;

    const confirmed = await confirmModal(
      "Batalkan Booking?",
      `Booking ${bookingCode} akan dibatalkan secara permanen`,
      "warning",
      "Ya, Batalkan"
    );
    if (!confirmed.isConfirmed) return;

    try {
      const res = await axiosInstance.patch(route("backdoor.booking-management.cancel", bookingCode));
      Toast.fire({ icon: "success", title: res.data?.message || "Booking berhasil dibatalkan" });
      table.reload();
    } catch (err) {
      if (err.response?.status === 422) {
        Toast.fire({
          icon: "error",
          title: err.response?.data?.message || "Data tidak valid untuk pembatalan",
        });
      } else {
        Toast.fire({
          icon: "error",
          title: "Terjadi kesalahan sistem saat membatalkan booking",
        });
      }
    }
  };

  return { settle, cancel };
}
