export default function useAddons({ state }) {
  const toggleAddon = (id) => {
    const key = String(id);
    if (state.selectedAddons[key] !== undefined) {
      const next = { ...state.selectedAddons };
      delete next[key];
      state.selectedAddons = next;
    } else {
      state.selectedAddons = { ...state.selectedAddons, [key]: 1 };
    }
  };

  const isAddonSelected = (id) => state.selectedAddons[String(id)] !== undefined;

  const increment = (id) => {
    const k = String(id);
    if (state.selectedAddons[k]) {
      state.selectedAddons = { ...state.selectedAddons, [k]: state.selectedAddons[k] + 1 };
    }
  };

  const decrement = (id) => {
    const k = String(id);
    if (state.selectedAddons[k] > 1) {
      state.selectedAddons = { ...state.selectedAddons, [k]: state.selectedAddons[k] - 1 };
    } else {
      toggleAddon(id, false);
    }
  };

  const getQty = (id) => state.selectedAddons[String(id)] ?? 0;

  const buildAddonsPayload = () =>
    Object.entries(state.selectedAddons).map(([id, qty]) => ({
      addon_id: parseInt(id),
      quantity: qty,
    }));

  return { toggleAddon, isAddonSelected, increment, decrement, getQty, buildAddonsPayload };
}
