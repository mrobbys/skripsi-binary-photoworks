/**
 * State Management untuk Fitur Master Data Varian Sesi & Harga
 * @returns {Object} Plain object state varian
 */
export default function useVariantState() {
  return {
    // Variant Page States (Stats)
    totalActiveVariants: 0,

    // Variant Drawer & Form States
    isVariantDrawerOpen: false,
    isVariantEdit: false,
    variantId: null,
    currentPackageSlug: null,
    variantForm: {
      name: "",
      price: "",
      duration: "",
      is_whatsapp_only: false,
      is_active: true,
      features: [""],
    },
    variantErrors: {},
    dismissedVariantErrors: {},
    isVariantFormValid: false,
  };
}
