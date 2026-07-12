import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";
import { z } from "zod";
import axiosInstance from "@/lib/axiosInstance";

const scheduleTimeSchema = z
  .object({
    start_time: z.string().regex(/^\d{2}:\d{2}$/, "Format jam tidak valid."),
    end_time: z.string().regex(/^\d{2}:\d{2}$/, "Format jam tidak valid."),
  })
  .refine((data) => data.end_time > data.start_time, {
    message: "Jam tutup harus lebih besar dari jam buka.",
    path: ["end_time"],
  });

export default function useActions({ state, table }) {
  /**
   * Auto-save saat Flatpickr onClose dipanggil dengan local state update & rollback.
   */
  const saveScheduleTime = async (scheduleId, newStartTime, newEndTime) => {
    const item = table.data.find((s) => s.id === scheduleId);
    if (!item) return;

    const oldStartTime = item.start_time;
    const oldEndTime = item.end_time;

    const result = scheduleTimeSchema.safeParse({ start_time: newStartTime, end_time: newEndTime });

    if (!result.success) {
      const message = result.error.issues[0]?.message ?? "Format waktu tidak valid.";
      Toast.fire({ icon: "error", title: message });

      // Rollback Flatpickr UI ke nilai lama
      const startEl = document.getElementById("start-time-" + scheduleId);
      const endEl = document.getElementById("end-time-" + scheduleId);
      if (startEl && startEl._flatpickr) startEl._flatpickr.setDate(oldStartTime, false);
      if (endEl && endEl._flatpickr) endEl._flatpickr.setDate(oldEndTime, false);
      return;
    }

    item.start_time = newStartTime;
    item.end_time = newEndTime;

    state.savingIds = new Set([...state.savingIds, scheduleId]);

    try {
      const response = await axiosInstance.patch(route("backdoor.data-master.schedule.update", scheduleId), {
        start_time: newStartTime,
        end_time: newEndTime,
        is_active: item.is_active,
      });

      if (response.data.data) {
        item.start_time = response.data.data.start_time;
        item.end_time = response.data.data.end_time;

        const startEl = document.getElementById("start-time-" + scheduleId);
        const endEl = document.getElementById("end-time-" + scheduleId);
        if (startEl && startEl._flatpickr) startEl._flatpickr.setDate(response.data.data.start_time, false);
        if (endEl && endEl._flatpickr) endEl._flatpickr.setDate(response.data.data.end_time, false);
      }

      Toast.fire({ icon: "success", title: "Jadwal berhasil disimpan." });
    } catch (error) {
      item.start_time = oldStartTime;
      item.end_time = oldEndTime;

      const startEl = document.getElementById("start-time-" + scheduleId);
      const endEl = document.getElementById("end-time-" + scheduleId);
      if (startEl && startEl._flatpickr) startEl._flatpickr.setDate(oldStartTime, false);
      if (endEl && endEl._flatpickr) endEl._flatpickr.setDate(oldEndTime, false);

      if (error.response?.status === 422) {
        const firstError = Object.values(error.response.data.errors)[0]?.[0];
        Toast.fire({ icon: "error", title: firstError ?? "Validasi gagal." });
      } else {
        Toast.fire({
          icon: "error",
          title: error.response?.data?.message ?? "Terjadi kesalahan server.",
        });
      }
    } finally {
      const next = new Set(state.savingIds);
      next.delete(scheduleId);
      state.savingIds = next;
    }
  };

  /**
   * Toggle status aktif dengan optimistic update.
   */
  const toggleScheduleStatus = async (scheduleId, currentStatus) => {
    const item = table.data.find((s) => s.id === scheduleId);
    if (item) item.is_active = !currentStatus;

    state.savingIds = new Set([...state.savingIds, scheduleId]);

    try {
      const response = await axiosInstance.patch(route("backdoor.data-master.schedule.toggle", scheduleId));
      Toast.fire({ icon: "success", title: response.data.message });
    } catch (error) {
      if (item) item.is_active = currentStatus; // rollback
      Toast.fire({
        icon: "error",
        title: error.response?.data?.message ?? "Terjadi kesalahan server.",
      });
    } finally {
      const next = new Set(state.savingIds);
      next.delete(scheduleId);
      state.savingIds = next;
    }
  };

  return { saveScheduleTime, toggleScheduleStatus };
}
