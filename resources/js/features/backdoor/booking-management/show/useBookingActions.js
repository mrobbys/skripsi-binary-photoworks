import route from "@/lib/route";
import { Toast, confirmModal } from "@/lib/sweetalert";
import axiosInstance from "@/lib/axiosInstance";
import { z } from "zod";
import { getFieldError } from "@/lib/zodHelper";

const gdriveLinkSchema = z.object({
  gdrive_link: z
    .string({ required_error: "Link Google Drive wajib diisi" })
    .min(1, "Link Google Drive wajib diisi")
    .url("Format tautan tidak valid")
    .refine((val) => val.startsWith("http://") || val.startsWith("https://"), {
      message: "Tautan harus diawali dengan http:// atau https://",
    }),
});

export default function useBookingActions({ state, fetchBooking }) {
  const validateField = (field) => {
    state.dismissedErrors[field] = true;
    const result = gdriveLinkSchema.safeParse({
      gdrive_link: state.gdriveLink,
    });

    if (!result.success) {
      state.errors[field] = getFieldError(result, field);
    } else {
      state.errors[field] = null;
    }
  };

  const settle = async () => {
    if (!state.bookingCode) return;

    const confirmed = await confirmModal(
      "Tandai Lunas?",
      `Booking ${state.bookingCode} ubah status menjadi Lunas`,
      "warning",
      "Ya, Tandai Lunas"
    );
    if (!confirmed.isConfirmed) return;

    try {
      const res = await axiosInstance.patch(route("backdoor.booking-management.settle", state.bookingCode));
      Toast.fire({ icon: "success", title: res.data?.message || "Status booking berhasil diubah menjadi Lunas" });
      await fetchBooking();
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

  const submitGdrive = async () => {
    validateField("gdrive_link");
    if (state.errors.gdrive_link) return;

    state.isGdriveLoading = true;

    try {
      const res = await axiosInstance.patch(route("backdoor.booking-management.gdrive", state.bookingCode), {
        gdrive_link: state.gdriveLink,
        send_wa_notification: state.sendWaNotificationGdrive,
      });
      Toast.fire({ icon: "success", title: res.data?.message || "Tautan Google Drive berhasil disimpan" });
      await fetchBooking();
    } catch (err) {
      if (err.response?.status === 422) {
        Toast.fire({
          icon: "error",
          title: err.response?.data?.message || "Data tidak valid untuk tautan Google Drive",
        });
      } else {
        Toast.fire({
          icon: "error",
          title: "Terjadi kesalahan sistem saat menyimpan tautan Google Drive",
        });
      }
    } finally {
      state.isGdriveLoading = false;
    }
  };

  const refund = async () => {
    if (!state.bookingCode) return;

    const overpaymentAmount = Number(state.summary?.overpayment || 0).toLocaleString("id-ID");
    const confirmed = await confirmModal(
      "Catat Refund?",
      `Kelebihan bayar booking ${state.bookingCode} akan dicatat sebagai refund sebesar Rp ${overpaymentAmount}`,
      "warning",
      "Ya, Catat Refund"
    );
    if (!confirmed.isConfirmed) return;

    try {
      const res = await axiosInstance.post(route("backdoor.booking-management.refund", state.bookingCode));
      Toast.fire({ icon: "success", title: res.data?.message || "Refund berhasil dicatat" });
      await fetchBooking();
    } catch (err) {
      if (err.response?.status === 422) {
        Toast.fire({
          icon: "error",
          title: err.response?.data?.message || "Data tidak valid untuk mencatat refund",
        });
      } else {
        Toast.fire({
          icon: "error",
          title: "Terjadi kesalahan sistem saat mencatat refund",
        });
      }
    }
  };

  return { settle, submitGdrive, refund, validateField };
}
