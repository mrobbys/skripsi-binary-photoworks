import route from "@/lib/route";
import { Toast, confirmModal } from "@/lib/sweetalert";
import axiosInstance from "@/lib/axiosInstance";
import { z } from "zod";

const gdriveLinkSchema = z.object({
  gdriveLink: z.string().min(1, "Link Google Drive wajib diisi.").url("Format link tidak valid."),
});

export default function useBookingActions({ state, table }) {
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

  const openGdrive = (bookingCode) => {
    state.gdriveBookingCode = bookingCode;
    state.gdriveLink = "";
    state.isGdriveOpen = true;
  };

  const closeGdrive = () => {
    state.isGdriveOpen = false;
    state.gdriveBookingCode = null;
    state.gdriveLink = "";
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
      return;
    }

    try {
      const res = await axiosInstance.patch(route("backdoor.booking-management.gdrive", state.gdriveBookingCode), {
        gdrive_link: parsed.data.gdriveLink,
      });

      if (!res.data.success) {
        throw new Error(res.data.message || "Gagal menyimpan link GDrive.");
      }

      Toast.fire({ icon: "success", title: res.data.message });
      closeGdrive();
      table.reload();
    } catch (err) {
      if (err.response?.status === 422) {
        state.gdriveErrors = err.response.data.errors;
        return;
      }
      const msg = err?.response?.data?.message || err.message;
      Toast.fire({ icon: "error", title: msg || "Terjadi kesalahan." });
    } finally {
      state.isGdriveLoading = false;
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

  return { settle, openGdrive, closeGdrive, submitGdrive, cancel };
}
