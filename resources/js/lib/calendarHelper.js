import flatpickr from "flatpickr";
export const formatIdDate = (dateStr) => {
  if (!dateStr) return "";
  // Pecah YYYY-MM-DD untuk menghindari Timezone Bug (Mundur 1 hari)
  const [y, m, d] = dateStr.split('-');
  
  /**
   * Di JS bulan dimulai dari 0 (Januari = 0)
   * Data dari backend laravel dimulai dari 1 (Januari = 1)
   */
  return new Date(y, m - 1, d).toLocaleDateString("id-ID", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
  });
};

export const initBaseFlatpickr = (element, customOptions = {}) => {
  return flatpickr(element, {
    inline: true,
    dateFormat: "Y-m-d",
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
    ...customOptions,
  });
};
