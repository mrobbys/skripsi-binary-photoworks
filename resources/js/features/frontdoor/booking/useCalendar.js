import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";
import { initBaseFlatpickr, formatIdDate } from "@/lib/calendarHelper";
import axiosInstance from "@/lib/axiosInstance";

let _fp = null;

export default function useCalendar({ state }) {
  const initCalendar = (calendarRef) => {

    _fp = initBaseFlatpickr(calendarRef, {
      minDate: "today",
      disable: [
        (date) => {
          const dbDay = date.getDay() === 0 ? 7 : date.getDay();
          return !state.activeDays.includes(dbDay);
        },
      ],
      onChange: (_, dateStr) => {
        if (!dateStr) return;

        state.selectedDate = dateStr;
        state.formattedDate = formatIdDate(dateStr);
        state.selectedSlot = null;

        fetchSlots(dateStr);
      },
    });
  };

  const destroyCalendar = () => {
    if (_fp) {
      _fp.destroy();
      _fp = null;
    }
  };

  // ambil slot waktu berdasarkan tanggal terpilih
  const fetchSlots = async (dateStr) => {
    state.isFetchingSlots = true;

    // disabled flatpickr saat fetching data
    if (_fp?.calendarContainer) {
      _fp.calendarContainer.classList.add("pointer-events-none", "opacity-50", "select-none");
    }

    try {
      const res = await axiosInstance.get(route("frontdoor.booking.api.slots"), {
        params: {
          date: dateStr,
          duration: state.selectedVariant?.duration || 30,
        },
      });
      state.availableSlots = res.data?.slots ?? [];
    } catch (err){
      Toast.fire({ icon: "error", title: "Gagal memuat jadwal tersedia." });
      state.availableSlots = [];
      console.error(err);
    } finally {
      state.isFetchingSlots = false;
      if (_fp?.calendarContainer) {
        _fp.calendarContainer.classList.remove("pointer-events-none", "opacity-50", "select-none");
      }
    }
  };

  return { initCalendar, destroyCalendar };
}
