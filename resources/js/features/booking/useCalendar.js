import route from '../../lib/route';
import dayjs from 'dayjs';
import { Toast } from '../../lib/sweetalert';

let _timer = null;
let _fp = null;

export default function useCalendar({ state }) {
  const initCalendar = (calendarRef) => {
    _fp = window.flatpickr(calendarRef, {
      inline: true,
      minDate: dayjs().toDate(),
      dateFormat: 'Y-m-d',
      disable: [
        (date) => {
          const dbDay = date.getDay() === 0 ? 7 : date.getDay();
          return !state.activeDays.includes(dbDay);
        }
      ],
      onChange: (_, dateStr) => {
        state.selectedDate = dateStr;
        state.selectedSlot = null;
        debouncedFetch(dateStr);
      },
      locale: {
        firstDayOfWeek: 1,
        weekdays: {
          shorthand: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
          longhand: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
        },
        months: {
          shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
          longhand: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
        },
      },
    });
  };

  const destroyCalendar = () => {
    _fp?.destroy();
    _fp = null;
  };

  const debouncedFetch = (date) => {
    clearTimeout(_timer);
    _timer = setTimeout(() => fetchSlots(date), 300);
  };

  const fetchSlots = async (date) => {
    if (!dayjs(date, 'YYYY-MM-DD', true).isValid()) return;

    state.isFetchingSlots = true;
    state.availableSlots = [];
    try {
      const res = await window.axios.get(route('frontdoor.booking.api.slots'), { params: { date } });
      state.availableSlots = res.data.slots;
    } catch {
      Toast.fire({ icon: 'error', title: 'Gagal memuat jadwal tersedia.' });
    } finally {
      state.isFetchingSlots = false;
    }
  };

  return { initCalendar, destroyCalendar };
}
