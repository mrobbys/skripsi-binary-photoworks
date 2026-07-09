export default function useState(Alpine) {
  return Alpine.reactive({
    // Page Stats
    totalActiveBackgrounds: 0,
    totalBackgrounds: 0,

    // General
    isLoading: false,

    // Drawer & Form
    isDrawerOpen: false,
    isEdit: false,
    backgroundId: null,
    form: {
      name: "",
      description: "",
      is_active: true,
    },
    errors: {},

    // FilePond — file yang dipilih user sebelum disubmit
    pendingImageFile: null,

    // Preview Modal
    isPreviewOpen: false,
    previewImageUrl: "",
    previewImageName: "",
  });
}
