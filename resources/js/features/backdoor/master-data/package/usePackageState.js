/**
 * State Management untuk Fitur Master Data Paket
 * @returns {Object} Plain object state paket
 */
export default function usePackageState() {
  return {
    // Package Page & Detail States (Stats & Info)
    totalPackages: 0,
    totalActivePackages: 0,
    totalActiveVariants: 0,
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

    // Upload & Image States
    pendingImageFile: null,

    // Preview image modal
    isPreviewOpen: false,
    previewImageUrl: "",
    previewImageName: "",
  };
}
