export default function useState(Alpine) {
  return Alpine.reactive({
    activeCount: 0,
    isModalOpen: false,
    isEdit: false,
    isLoading: false,
    categoryId: null,
    form: {
      category_code: "",
      name: "",
      is_active: true,
    },
    errors: {},
  });
}
