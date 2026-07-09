import route from "@/lib/route";
import dayjs from "dayjs";
import { Toast } from "@/lib/sweetalert";

let _timer = null;
let _fp = null;

const formatDate = (dateStr) => {
  if (!dateStr) return "";
  const d = dayjs(dateStr);
  const days = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
  const months = [
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "Desember",
  ];
  return `${days[d.day()]}, ${d.date()} ${months[d.month()]} ${d.year()}`;
};

export default function useCalendar({ state }) {
  const initCalendar = (calendarRef) => {
    let defaultDate = state.selectedDate ? dayjs(state.selectedDate) : null;

    if (!defaultDate) {
      defaultDate = dayjs();
      const isDayDisabled = (date) => {
        const dbDay = date.getDay() === 0 ? 7 : date.getDay();
        return !state.activeDays.includes(dbDay);
      };

      let attempts = 0;
      while (isDayDisabled(defaultDate.toDate()) && attempts < 30) {
        defaultDate = defaultDate.add(1, "day");
        attempts++;
      }
    }

    const defaultDateStr = defaultDate.format("YYYY-MM-DD");
    state.selectedDate = defaultDateStr;
    state.formattedDate = formatDate(defaultDateStr);

    _fp = window.flatpickr(calendarRef, {
      inline: true,
      defaultDate: defaultDate.toDate(),
      minDate: dayjs().toDate(),
      dateFormat: "Y-m-d",
      disable: [
        (date) => {
          const dbDay = date.getDay() === 0 ? 7 : date.getDay();
          return !state.activeDays.includes(dbDay);
        },
      ],
      onChange: (_, dateStr) => {
        if (state.selectedDate !== dateStr) {
          state.selectedDate = dateStr;
          state.formattedDate = formatDate(dateStr);
          state.selectedSlot = null;
          debouncedFetch(dateStr);
        }
      },
      locale: {
        firstDayOfWeek: 1,
        weekdays: {
          shorthand: ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"],
          longhand: ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"],
        },
        months: {
          shorthand: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
          longhand: [
            "Januari",
            "Februari",
            "Maret",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Agustus",
            "September",
            "Oktober",
            "November",
            "Desember",
          ],
        },
      },
    });

    // Auto-fetch data untuk default date saat pertama kali load
    debouncedFetch(defaultDateStr);
  };

  const destroyCalendar = () => {
    _fp?.destroy();
    _fp = null;
  };

  const debouncedFetch = (date) => {
    clearTimeout(_timer);
    _timer = setTimeout(() => fetchSlots(date), 300);
  };

  const fetchSlots = async (date, isAutoInit = false, searchAttempts = 0) => {
    if (!dayjs(date, "YYYY-MM-DD", true).isValid()) return;

    state.isFetchingSlots = true;

    // disabled flatpickr saat fetching data
    if (_fp?.calendarContainer) {
      _fp.calendarContainer.classList.add("pointer-events-none", "opacity-50", "select-none");
    }

    state.availableSlots = [];
    try {
      const res = await window.axios.get(route("frontdoor.booking.api.slots"), {
        params: {
          date,
          duration: state.selectedVariant?.duration || 30,
        },
      });
      state.availableSlots = res.data.slots;

      if (isAutoInit && state.availableSlots.length === 0 && searchAttempts < 14) {
        let nextDate = dayjs(date).add(1, "day");
        while (true) {
          const dbDay = nextDate.day() === 0 ? 7 : nextDate.day();
          if (state.activeDays.includes(dbDay)) break;
          nextDate = nextDate.add(1, "day");
        }

        const nextDateStr = nextDate.format("YYYY-MM-DD");
        state.selectedDate = nextDateStr;
        state.formattedDate = formatDate(nextDateStr);
        if (_fp) _fp.setDate(nextDate.toDate(), false);

        return await fetchSlots(nextDateStr, true, searchAttempts + 1);
      }
    } catch {
      Toast.fire({ icon: "error", title: "Gagal memuat jadwal tersedia." });
    } finally {
      state.isFetchingSlots = false;
      if (_fp?.calendarContainer) {
        _fp.calendarContainer.classList.remove("pointer-events-none", "opacity-50", "select-none");
      }
    }
  };

  return { initCalendar, destroyCalendar };
}
