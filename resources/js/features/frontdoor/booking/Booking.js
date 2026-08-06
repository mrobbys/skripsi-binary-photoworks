import useState from "./useState.js";
import useCalendar from "./useCalendar.js";
import useAddons from "./useAddons.js";
import useCheckout from "./useCheckout.js";
import formatRupiah from "@/utils/formatRupiah";

export default function Booking(Alpine) {
  const state = useState(Alpine);

  const { initCalendar, destroyCalendar } = useCalendar({ state });

  const { toggleAddon, isAddonSelected, increment, decrement, getQty, setQty, buildAddonsPayload } = useAddons({
    state,
  });

  const { triggerCheckout } = useCheckout({ state, buildAddonsPayload });

  // hitung total nilai keseluruhan
  const totalPrice = () => {
    if (!state.selectedVariant) return 0;
    const addonTotal = Object.entries(state.selectedAddons).reduce((sum, [id, qty]) => {
      const addon = state.allAddons.find((a) => a.id === parseInt(id));
      return sum + (addon ? addon.price * qty : 0);
    }, 0);
    return state.selectedVariant.price + addonTotal;
  };

  // hitung untuk nominal dp sebesar 60%
  const dpAmount = () => Math.round(totalPrice() * 0.6);

  // hitung sisa pembayaran yang harus dilunasi
  const dpRemaining = () => totalPrice() - dpAmount();

  // total nominal tagihan yang harus dibayar
  const grossAmount = () => (state.paymentScheme === "dp" ? dpAmount() : totalPrice());

  const init = () => {}; // placeholder, state di-inject via x-init di Blade

  // next step form
  const nextStep = () => {
    if (!canProceed()) return;
    state.currentStep++;
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  // previous step form
  const prevStep = () => {
    if (state.currentStep === 2) destroyCalendar();
    state.currentStep = Math.max(1, state.currentStep - 1);
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  // check apakah bisa melanjutkan ke step selanjutnya
  const canProceed = () => {
    if (state.currentStep === 1) return !!state.selectedVariantId;
    if (state.currentStep === 2) return !!state.selectedDate && !!state.selectedSlot;
    if (state.currentStep === 3) return true;
    return false;
  };

  // select variant di form step 1
  const selectVariant = (variant) => {
    state.selectedVariantId = variant.id;
    state.selectedVariant = variant;
    state.selectedDate = null;
    state.selectedSlot = null;
  };

  // pilih slot waktu di form step 2
  const selectSlot = (slot) => {
    state.selectedSlot = slot;
  };

  return {
    state,
    init,

    totalPrice,
    grossAmount,
    dpAmount,
    dpRemaining,
    formatRupiah,

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
    setQty,

    // Checkout
    triggerCheckout,
  };
}
