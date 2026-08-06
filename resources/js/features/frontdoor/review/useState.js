export default function useState(Alpine) {
  return Alpine.reactive({
    items: [],
    userReview: null,
    stats: {
      average_rating: 0,
      total_reviews: 0,
      breakdown: { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 },
      breakdown_percentage: { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 },
    },

    // loading state
    isLoading: false,

    // sort — diubah oleh select Choices.js
    sort: "newest",

    // modal form state
    openModal: false,
    maxComment: 500,
    hoverRating: 0,
    form: {
      rating: 0,
      comment: "",
    },

    errors: {},
    dismissedErrors: {},
  });
}
