import useState from "./useState.js";
import useCalendar from "./useCalendar.js";
import useAddons from "./useAddons.js";
import useCheckout from "./useCheckout.js";
import formatRupiah from "../../utils/formatRupiah.js";

export default function Booking(Alpine) {
  const state = useState(Alpine);

  const { initCalendar, destroyCalendar } = useCalendar({ state });

  const { toggleAddon, isAddonSelected, increment, decrement, getQty, buildAddonsPayload } = useAddons({ state });

  const { triggerCheckout } = useCheckout({ state, buildAddonsPayload });

  // ─── Computed Getters ───────────────────────────────────────────
  const totalPrice = () => {
    if (!state.selectedVariant) return 0;
    const addonTotal = Object.entries(state.selectedAddons).reduce((sum, [id, qty]) => {
      const addon = state.allAddons.find((a) => a.id === parseInt(id));
      return sum + (addon ? addon.price * qty : 0);
    }, 0);
    return state.selectedVariant.price + addonTotal;
  };

  const dpAmount = () => Math.round(totalPrice() * 0.6);

  const dpRemaining = () => totalPrice() - dpAmount();

  const grossAmount = () => (state.paymentScheme === "dp" ? dpAmount() : totalPrice());

  const remainingAmount = () => totalPrice() - grossAmount();

  // ─── Wizard Navigation ──────────────────────────────────────────
  const init = () => {}; // placeholder, state di-inject via x-init di Blade

  const nextStep = () => {
    if (!canProceed()) return;
    state.currentStep++;
  };

  const prevStep = () => {
    if (state.currentStep === 2) destroyCalendar();
    state.currentStep = Math.max(1, state.currentStep - 1);
  };

  const canProceed = () => {
    if (state.currentStep === 1) return !!state.selectedVariantId;
    if (state.currentStep === 2) return !!state.selectedDate && !!state.selectedSlot;
    if (state.currentStep === 3) return true;
    return false;
  };

  const selectVariant = (variant) => {
    state.selectedVariantId = variant.id;
    state.selectedVariant = variant;
    state.selectedDate = null;
    state.selectedSlot = null;
  };

  const selectSlot = (slot) => {
    state.selectedSlot = slot;
  };

  return {
    state,
    init,

    // Computed (dipanggil sebagai fungsi di Blade)
    totalPrice,
    grossAmount,
    remainingAmount,
    dpAmount,
    dpRemaining,
    formatRupiah,

    // Wizard
    nextStep,
    prevStep,
    canProceed,
    selectVariant,
    selectSlot,
    initCalendar,
    destroyCalendar,

    // Addons
    toggleAddon,
    isAddonSelected,
    increment,
    decrement,
    getQty,

    // Checkout
    triggerCheckout,
  };
}
