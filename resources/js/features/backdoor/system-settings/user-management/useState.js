export default function useState(Alpine) {
  return Alpine.reactive({
    isDrawerOpen: false,
    isEdit: false,
    isLoading: false,
    userId: null,

    form: {
      name: "",
      email: "",
      phone: "",
      role: "",
    },

    errors: {},
  });
}
