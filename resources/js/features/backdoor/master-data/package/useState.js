export default function useState(Alpine) {
  return Alpine.reactive({
    // General & Shared States
    isLoading: false,

    // Package Page & Detail States (Stats & Info)
    totalPackages: 0,
    totalActivePackages: 0,
    // Menyimpan detail info paket (digunakan di ShowPackage.js)
    packageInfo: null,

    // Package Drawer & Form States
    isDrawerOpen: false,
    isEdit: false,
    packageId: null,
    form: {
      category_id: "",
      name: "",
      description: "",
      is_active: true,
      features: [""],
    },
    errors: {},
    dismissedErrors: {},
    isFormValid: false,

    pendingImageFile: null,

    // Preview image modal
    isPreviewOpen: false,
    previewImageUrl: "",
    previewImageName: "",
    
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
  });
}
