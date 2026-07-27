import route from "@/lib/route";
import { Toast, confirmModal } from "@/lib/sweetalert";
import axiosInstance from "@/lib/axiosInstance";
import { z } from "zod";

const gdriveLinkSchema = z.object({
  gdriveLink: z
    .string()
    .min(1, "Link Google Drive wajib diisi.")
    .url("Format link tidak valid.")
    .refine((val) => val.startsWith("http://") || val.startsWith("https://"), {
      message: "Link harus diawali dengan http:// atau https://",
    }),
});

export default function useBookingActions({ state, fetchBooking }) {
  const settle = async () => {
    const confirmed = await confirmModal(
      "Tandai Lunas?",
      `Booking ${state.bookingCode} ubah status menjadi lunas.`,
      "warning",
      "Ya, Tandai Lunas",
    );
    if (!confirmed.isConfirmed) return;

    try {
      const res = await axiosInstance.patch(route("backdoor.booking-management.settle", state.bookingCode));
      Toast.fire({ icon: "success", title: res.data.message });
      await fetchBooking();
    } catch (err) {
      Toast.fire({ icon: "error", title: err?.response?.data?.message ?? "Gagal memproses pelunasan." });
    }
  };

  const submitGdrive = async () => {
    state.isGdriveLoading = true;
    state.gdriveErrors = {};

    // Validasi zod
    const parsed = gdriveLinkSchema.safeParse({
      gdriveLink: state.gdriveLink,
    });
    if (!parsed.success) {
      const errors = parsed.error.flatten();
      state.gdriveErrors = {
        gdriveLink: errors.gdriveLink ? errors.gdriveLink[0] : "Link tidak valid",
      };
      state.isGdriveLoading = false;
      return;
    }

    try {
      const res = await axiosInstance.patch(route("backdoor.booking-management.gdrive", state.bookingCode), {
        gdrive_link: parsed.data.gdriveLink,
        send_wa_notification: state.sendWaNotificationGdrive,
      });
      Toast.fire({ icon: "success", title: res.data.message });
      await fetchBooking();
    } catch (err) {
      Toast.fire({ icon: "error", title: err?.response?.data?.message ?? "Gagal menyimpan link GDrive." });
    } finally {
      state.isGdriveLoading = false;
    }
  };

  const refund = async () => {
    const confirmed = await confirmModal(
      "Catat Refund?",
      `Kelebihan bayar booking ${state.bookingCode} akan dicatat sebagai refund sebesar Rp ${Number(state.summary?.overpayment || 0).toLocaleString("id-ID")}.`,
      "warning",
      "Ya, Catat Refund",
    );
    if (!confirmed.isConfirmed) return;

    try {
      const res = await axiosInstance.post(route("backdoor.booking-management.refund", state.bookingCode));
      Toast.fire({ icon: "success", title: res.data.message });
      await fetchBooking();
    } catch (err) {
      Toast.fire({ icon: "error", title: err?.response?.data?.message ?? "Gagal mencatat refund." });
    }
  };

  return { settle, submitGdrive, refund };
}
