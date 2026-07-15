import useState from "./useState";
import useCreateForm from "./useCreateForm";

export default function Create(Alpine) {
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
    const variant = state.variants.find((v) => v.id == state.variantId);
    const basePrice = variant ? variant.price : 0;
    const addonTotal = state.addons.reduce((sum, item) => {
      const addon = state.allAddons.find((a) => a.id == item.addon_id);
      return sum + (addon ? addon.price * item.quantity : 0);
    }, 0);
    state.totalPrice = basePrice + addonTotal;
  });

  function init(packages, addons) {
    state.allPackages = packages;
    state.allAddons = addons;
  }

  return { state, init, ...formActions };
}
