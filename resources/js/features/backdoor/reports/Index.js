import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
import { Toast } from "@/lib/sweetalert";

export default function Index() {
  let fpInstances = [];

  const flatpickrConfig = {
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "d-m-Y",
    disableMobile: true,
  };

  const initFlatpickr = (containerEl) => {
    const reportCards = containerEl.querySelectorAll(".report-card");
    if (!reportCards.length) return;

    reportCards.forEach((card) => {
      const startDateInput = card.querySelector("input[name='start_date']");
      const endDateInput = card.querySelector("input[name='end_date']");
      const singleDateInput = card.querySelector("input[name='date']");
      const dropdownStatus = card.querySelector("select[name='status']");
      const form = card.querySelector("form");
      const btnSubmitForm = form.querySelector("button[type='submit']");

      if (!form || !btnSubmitForm) return;

      // cek apakah ada input date
      const hasInputs = Boolean(startDateInput || endDateInput || singleDateInput);

      if (hasInputs) {
        btnSubmitForm.disabled = true;
      }

      const updateButtonState = () => {
        if (!hasInputs) return;

        if (startDateInput && endDateInput) {
          btnSubmitForm.disabled = !startDateInput.value || !endDateInput.value;
        } else if (singleDateInput) {
          btnSubmitForm.disabled = !singleDateInput.value;
        }
      };

      let fpStart = null;
      let fpEnd = null;
      let fpSingle = null;

      if (startDateInput && endDateInput) {
        fpEnd = flatpickr(endDateInput, {
          ...flatpickrConfig,
          onChange(selectedDates, dateStr) {
            if (fpStart) fpStart.set("maxDate", dateStr);
            updateButtonState();
          },
        });

        fpStart = flatpickr(startDateInput, {
          ...flatpickrConfig,
          onChange(selectedDates) {
            if (selectedDates[0]) {
              // set min date agar tidak bisa memilih sebelum start_date
              fpEnd.set("minDate", selectedDates[0]);

              // Set tampilan kalender memiliki bulan yang sama dengan start_date
              // Contoh: start_date bulan juli 2026, maka end_date tampilan awal akan juli 2026
              fpEnd.jumpToDate(selectedDates[0]);
            }

            updateButtonState();

            if (startDateInput.value && fpEnd) {
              setTimeout(() => fpEnd.open(), 100);
            }
          },
        });

        fpInstances.push(fpStart, fpEnd);
      } else if (singleDateInput) {
        fpSingle = flatpickr(singleDateInput, {
          ...flatpickrConfig,
          onChange() {
            updateButtonState();
          },
        });

        fpInstances.push(fpSingle);
      }

      form.addEventListener("submit", (e) => {
        e.preventDefault();

        if (hasInputs && btnSubmitForm.disabled) {
          Toast.fire({
            icon: "error",
            title: "Silahkan pilih tanggal terlebih dahulu.",
          });
          return;
        }
        form.submit();

        // Reset input
        setTimeout(() => {
          if (fpStart) fpStart.clear();
          if (fpEnd) {
            fpEnd.clear();
            fpEnd.close();
          }
          if (fpSingle) fpSingle.clear();
          if (dropdownStatus && dropdownStatus._choices) {
            dropdownStatus._choices.setChoiceByValue("");
          }
          updateButtonState();
        }, 100);
      });
    });
  };

  return {
    init() {
      initFlatpickr(this.$el);
    },
    destroy() {
      fpInstances.forEach((fp) => {
        if (fp && typeof fp.destroy === "function") fp.destroy();
      });
      fpInstances = [];
    },
  };
}
