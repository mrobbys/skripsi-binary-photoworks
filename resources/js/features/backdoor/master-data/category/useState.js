export default function useState(Alpine) {
  return Alpine.reactive({
    totalCategory: 0,
    activeCount: 0,
    isDrawerOpen: false,
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
