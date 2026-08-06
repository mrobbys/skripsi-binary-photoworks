export default function useProfileState(Alpine) {
  return Alpine.reactive({
    // profile user, diisi dari Blade via x-init
    name: "",
    email: "",
    phone: "",
    originalData: null,

    // update profil
    isUpdatingProfile: false,

    // diperlukan untuk x-shared.input.error
    errors: {},
    dismissedErrors: {},

    // ganti password
    isPasswordDrawerOpen: false,
    isChangingPassword: false,
    passwordErrors: [],
    passwordForm: {
      old_password: "",
      password: "",
      password_confirmation: "",
    },

    // cek apakah data profile berubah
    get hasChanges() {
      if (!this.originalData) return false;
      return (
        this.name !== this.originalData.name ||
        this.email !== this.originalData.email ||
        this.phone !== this.originalData.phone
      );
    },
  });
}
