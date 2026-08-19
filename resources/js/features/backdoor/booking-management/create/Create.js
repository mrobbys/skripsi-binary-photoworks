import useState from "./useState";
import useCreateForm from "./useCreateForm";
import useChoices from "@/lib/useChoices";
import flatpickr from "flatpickr";
import { Indonesian } from "flatpickr/dist/l10n/id.js";
import "flatpickr/dist/flatpickr.min.css";

export default function Create(Alpine) {
  if (Alpine) {
    Alpine.data("choices", useChoices);
  }

  const state = useState(Alpine);
  const formActions = useCreateForm({ state });

  Alpine.effect(() => {
    const pkg = state.packageId ? state.allPackages.find((p) => p.id == state.packageId) : null;
    state.variants = pkg ? pkg.variants : [];
    state.variantId = null;
    state.timeSlots = [];
    state.startTime = "";
  });

  Alpine.effect(() => {
    state.addons.forEach((item) => {
      if (!item.addon_id) return;
      const addon = formActions.findAddon(item.addon_id);
      if (addon && !addon.has_quantity && item.quantity !== 1) {
        item.quantity = 1;
      }
    });
  });

  Alpine.effect(() => {
    const variant = state.variants.find((v) => v.id == state.variantId);
    const basePrice = variant ? variant.price : 0;
    const addonTotal = state.addons.reduce((sum, item) => {
      const addon = formActions.findAddon(item.addon_id);
      if (!addon) return sum;
      const qty = addon.has_quantity ? item.quantity || 1 : 1;
      return sum + addon.price * qty;
    }, 0);
    state.totalPrice = basePrice + addonTotal;
    state.dpAmount = Math.round(state.totalPrice * 0.6);
    state.remainingAmount = state.totalPrice - state.dpAmount;
  });

  const initDatePicker = (el) => {
    flatpickr(el, {
      locale: Indonesian,
      dateFormat: "Y-m-d",
      minDate: "today",
      onChange: (selectedDates, dateStr) => {
        state.bookingDate = dateStr;
        formActions.loadTimeSlots();
      },
    });
  };

  const initVariantChoices = (el, watch) => {
    watch("state.variants", (variants) => {
      if (!el._choices) return;
      if (!state.packageId || variants.length === 0) {
        el._choices.clearStore();
        el._choices.setChoices([{ value: "", label: "--- Pilih Paket Terlebih Dahulu ---", disabled: true, selected: true, placeholder: true }], "value", "label", true);
        el._choices.disable();
        return;
      }
      const mapped = variants.map((v) => ({
        value: String(v.id),
        label: v.name + " (" + v.duration + " menit) — Rp " + Number(v.price).toLocaleString("id-ID"),
      }));
      el._choices.clearStore();
      el._choices.setChoices([{ value: "", label: "--- Pilih Varian ---", disabled: true, selected: true, placeholder: true }, ...mapped], "value", "label", true);
      el._choices.enable();
    });
    setTimeout(() => {
      if (!state.packageId && el._choices) {
        el._choices.disable();
      }
    }, 60);
  };

  const initTimeSlotChoices = (el, watch) => {
    watch("state.isTimeSlotsLoading", (isLoading) => {
      if (!el._choices) return;
      if (isLoading) {
        el._choices.clearStore();
        el._choices.setChoices([{ value: "", label: "Sedang memuat slot...", disabled: true, selected: true, placeholder: true }], "value", "label", true);
        el._choices.disable();
      }
    });
    watch("state.timeSlots", (slots) => {
      if (!el._choices) return;
      if (!state.bookingDate) {
        el._choices.clearStore();
        el._choices.setChoices([{ value: "", label: "--- Pilih Tanggal Terlebih Dahulu ---", disabled: true, selected: true, placeholder: true }], "value", "label", true);
        el._choices.disable();
        return;
      }
      if (!state.variantId) {
        el._choices.clearStore();
        el._choices.setChoices([{ value: "", label: "--- Pilih Varian Terlebih Dahulu ---", disabled: true, selected: true, placeholder: true }], "value", "label", true);
        el._choices.disable();
        return;
      }
      if (slots.length === 0) {
        el._choices.clearStore();
        el._choices.setChoices([{ value: "", label: "Tidak ada slot waktu tersedia", disabled: true, selected: true, placeholder: true }], "value", "label", true);
        el._choices.disable();
        return;
      }
      const mapped = slots.map((s) => ({
        value: s.start_time,
        label: s.start_time + " – " + s.end_time,
      }));
      el._choices.clearStore();
      el._choices.setChoices([{ value: "", label: "--- Pilih Slot Waktu ---", disabled: true, selected: true, placeholder: true }, ...mapped], "value", "label", true);
      el._choices.enable();
    });
    watch("state.bookingDate", (date) => {
      if (!el._choices) return;
      if (!date) {
        el._choices.clearStore();
        el._choices.setChoices([{ value: "", label: "--- Pilih Tanggal Terlebih Dahulu ---", disabled: true, selected: true, placeholder: true }], "value", "label", true);
        el._choices.disable();
      }
    });
    setTimeout(() => {
      if ((!state.bookingDate || !state.variantId) && el._choices) {
        el._choices.disable();
      }
    }, 60);
  };

  const initAddonChoices = (el, watch, item) => {
    const updateChoices = (newAddons) => {
      if (!el._choices) return;
      const mapped = state.allAddons
        .filter((a) => {
          const isSelectedByOther = newAddons.some((row) => String(row.addon_id) === String(a.id) && String(row.id) !== String(item.id));
          return !isSelectedByOther;
        })
        .map((a) => ({
          value: String(a.id),
          label: a.name + " — Rp " + Number(a.price).toLocaleString("id-ID"),
          selected: String(item.addon_id) === String(a.id),
        }));
      el._choices.clearStore();
      el._choices.setChoices([{ value: "", label: "--- Pilih Layanan ---", placeholder: true }, ...mapped], "value", "label", true);
    };
    setTimeout(() => updateChoices(state.addons), 50);
    watch("state.addons", (newAddons) => updateChoices(newAddons), { deep: true });
    watch("item.addon_id", () => formActions.onAddonChange(item));
  };

  function init(packages, addons) {
    state.allPackages = packages;
    state.allAddons = addons;
  }

  return { state, init, initDatePicker, initVariantChoices, initTimeSlotChoices, initAddonChoices, ...formActions };
}
