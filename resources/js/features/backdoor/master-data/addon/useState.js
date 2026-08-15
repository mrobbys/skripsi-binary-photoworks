export default function useState(Alpine) {
  return Alpine.reactive({
    // Page Stats
    totalAddons: 0,
    totalActiveAddons: 0,

    // General
    isLoading: false,

    // Drawer & Form
    isDrawerOpen: false,
    isEdit: false,
    addonId: null,
    form: {
      name: "",
      price: "",
      description: "",
      has_quantity: false,
      is_active: true,
    },
    errors: {},
    dismissedErrors: {},
    isFormValid: false,
  });
}
