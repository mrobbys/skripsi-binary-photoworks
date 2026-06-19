export default function useState(Alpine) {
  return Alpine.reactive({
    totalPackages: 0,
    totalActiveVariants: 0,
    isLoading: false,

    // Drawer Package
    isDrawerOpen: false,
    isEdit: false,
    packageId: null,

    // Drawer Variant
    isVariantDrawerOpen: false,
    isVariantEdit: false,
    variantId: null,
    currentPackageSlug: null,

    // Form Paket
    form: {
      category_id: "",
      name: "",
      is_active: true,
      features: [""],
    },

    // Form Varian
    variantForm: {
      name: "",
      price: "",
      duration: "",
      is_whatsapp_only: false,
      is_active: true,
      features: [""],
    },

    errors: {},
    variantErrors: {},
  });
}
