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
    const current = state.selectedAddons[k];
    if (current !== undefined) {
      state.selectedAddons = { ...state.selectedAddons, [k]: current + 1 };
    } else {
      state.selectedAddons = { ...state.selectedAddons, [k]: 1 };
    }
  };

  const decrement = (id) => {
    const k = String(id);
    const current = state.selectedAddons[k];
    if (current === undefined) return;

    if (current > 1) {
      state.selectedAddons = { ...state.selectedAddons, [k]: current - 1 };
    } else {
      toggleAddon(id);
    }
  };

  const getQty = (id) => state.selectedAddons[String(id)] ?? 0;

  const setQty = (id, val) => {
    const k = String(id);
    let num = parseInt(val);

    if (isNaN(num) || num < 1) num = 1;

    state.selectedAddons = { ...state.selectedAddons, [k]: num };
  };

  const buildAddonsPayload = () =>
    Object.entries(state.selectedAddons).map(([id, qty]) => ({
      addon_id: parseInt(id),
      quantity: qty,
    }));

  return { toggleAddon, isAddonSelected, increment, decrement, getQty, setQty, buildAddonsPayload };
}
