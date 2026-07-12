import route from "@/lib/route";
import { Toast, confirmModal } from "@/lib/sweetalert";
import { initBaseFlatpickr, formatIdDate } from "@/lib/calendarHelper";

let _fp = null;

export default function useReschedule({ state, fetchAppointments, clearDetail }) {
  // buka drawer form reschedule dan simpan appointment target
  const openRescheduleDrawer = (appointment) => {
    state.rescheduleTarget = appointment;
    state.selectedRescheduleDate = null;
    state.selectedRescheduleSlot = null;
    state.rescheduleSlots = [];
    state.isRescheduleOpen = true;
  };

  // reset state reschedule
  const closeRescheduleDrawer = () => {
    state.isRescheduleOpen = false;
    setTimeout(() => {
      state.rescheduleTarget = null;
      state.selectedRescheduleDate = null;
      state.selectedRescheduleSlot = null;
      state.rescheduleSlots = [];
      state.isFetchingRescheduleSlots = false;

      if (_fp) {
        _fp.clear();
        _fp.destroy();
        _fp = null;
      }
    }, 500);
  };

  // fetch slot waktu yang tersedia, berdasarkan tanggal yang dipilih
  const fetchRescheduleSlots = async (date) => {
    if (!date || !state.rescheduleTarget) return;

    state.selectedRescheduleDate = date;
    state.selectedRescheduleSlot = null;
    state.rescheduleSlots = [];
    state.isFetchingRescheduleSlots = true;

    if (_fp?.calendarContainer) {
      _fp.calendarContainer.classList.add("pointer-events-none", "opacity-50", "select-none");
    }

    try {
      const res = await window.axios.get(route("frontdoor.booking.api.slots"), {
        params: {
          date: date,
          duration: state.rescheduleTarget.variant_duration,
        },
      });

      state.rescheduleSlots = res.data.slots ?? [];
    } catch (err) {
      console.error("Gagal memuat slot reschedule:", err);
      Toast.fire({ icon: "error", title: "Gagal memuat slot waktu." });
    } finally {
      state.isFetchingRescheduleSlots = false;
      if (_fp?.calendarContainer) {
        _fp.calendarContainer.classList.remove("pointer-events-none", "opacity-50", "select-none");
      }

      // Auto-scroll halus ke area slot waktu
      setTimeout(() => {
        const slotArea = document.getElementById("slot-waktu-area");
        if (slotArea) slotArea.scrollIntoView({ behavior: "smooth", block: "start" });
      }, 100);
    }
  };

  // pilih slot waktu untuk reschedule
  const selectRescheduleSlot = (slot) => (state.selectedRescheduleSlot = slot);

  // inisialisasi flatpickr
  const initRescheduleCalendar = (el) => {
    if (_fp) _fp.destroy();

    _fp = initBaseFlatpickr(el, {
      minDate: "today",
      disable: [
        (date) => {
          const activeDays = state.activeDays || [];
          const dbDay = date.getDay() === 0 ? 7 : date.getDay();
          return !activeDays.includes(dbDay);
        },
      ],
      onChange: (_, dateStr) => {
        if (!dateStr) return;
        state.formattedDate = formatIdDate(dateStr);
        fetchRescheduleSlots(dateStr);
      },
    });
  };

  // submit reschedule
  const submitReschedule = async () => {
    if (!state.selectedRescheduleDate || !state.selectedRescheduleSlot) return;
    const formattedDate = formatIdDate(state.selectedRescheduleDate);

    const result = await confirmModal(
      "Konfirmasi Ubah Jadwal?",
      `Jadwal akan diubah ke ${formattedDate} pukul ${state.selectedRescheduleSlot.start_time} - ${state.selectedRescheduleSlot.end_time} WITA.`,
      "question",
      "Ya, Ubah Jadwal",
    );

    if (!result.isConfirmed) return;
    state.isRescheduling = true;

    try {
      const res = await window.axios.post(route("frontdoor.dashboard.reschedule"), {
        booking_code: state.rescheduleTarget.booking_code,
        new_date: state.selectedRescheduleDate,
        new_time: state.selectedRescheduleSlot.start_time,
      });

      if (!res.data.success) {
        throw new Error(res.data.message || "Gagal mengubah jadwal.");
      }

      Toast.fire({ icon: "success", title: "Jadwal berhasil diubah!" });

      closeRescheduleDrawer();
      await fetchAppointments();
    } catch (err) {
      const msg = err?.response?.data?.message || err.message;
      Toast.fire({ icon: "error", title: msg || "Terjadi kesalahan." });
    } finally {
      state.isRescheduling = false;
      clearDetail();
    }
  };

  return {
    openRescheduleDrawer,
    closeRescheduleDrawer,
    fetchRescheduleSlots,
    selectRescheduleSlot,
    initRescheduleCalendar,
    submitReschedule,
  };
}
