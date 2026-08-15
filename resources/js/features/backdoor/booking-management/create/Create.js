import useState from "./useState";
import useCreateForm from "./useCreateForm";
import useChoices from "@/lib/useChoices";
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";

export default function Create(Alpine) {
  if (Alpine) {
    Alpine.data("choices", useChoices);
  }

  const state = useState(Alpine);
  const formActions = useCreateForm({ state });

  Alpine.effect(() => {
    if (state.packageId) {
      const pkg = state.allPackages.find((p) => p.id == state.packageId);
      state.variants = pkg ? pkg.variants : [];
      state.variantId = null;
      state.timeSlots = [];
      state.startTime = "";
    }
  });

  Alpine.effect(() => {
    state.addons.forEach((item) => {
      if (!item.addon_id) return;
      const addon = state.allAddons.find((a) => a.id == item.addon_id);
      if (addon && !addon.has_quantity && item.quantity !== 1) {
        item.quantity = 1;
      }
    });
  });

  Alpine.effect(() => {
    const variant = state.variants.find((v) => v.id == state.variantId);
    const basePrice = variant ? variant.price : 0;
    const addonTotal = state.addons.reduce((sum, item) => {
      const addon = state.allAddons.find((a) => a.id == item.addon_id);
      if (!addon) return sum;
      const qty = addon.has_quantity ? (item.quantity || 1) : 1;
      return sum + addon.price * qty;
    }, 0);
    state.totalPrice = basePrice + addonTotal;
  });

  const initDatePicker = (el) => {
    flatpickr(el, {
      dateFormat: "Y-m-d",
      minDate: "today",
      onChange: (selectedDates, dateStr) => {
        state.bookingDate = dateStr;
        formActions.loadTimeSlots();
      },
    });
  };

  function init(packages, addons) {
    state.allPackages = packages;
    state.allAddons = addons;
  }

  return { state, init, initDatePicker, ...formActions };
}
