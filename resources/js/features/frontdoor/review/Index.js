import useFrontdoorPagination from "@/lib/useFrontdoorPagination";
import useState from "./useState";
import useReviewForm from "./useReviewForm";
import useReviewActions from "./useReviewActions";
import useChoices from "@/lib/useChoices";

export default function Index(Alpine) {
  if (Alpine) {
    Alpine.data("choices", useChoices);
  }

  const state = useState(Alpine);
 
  const actions = useReviewActions({
    state,
    resetPage: () => resetPage(),
  });

  const { goToPage, prevPage, nextPage, resetPage, getPages } = useFrontdoorPagination({
    state,
    onPageChange: () => actions.fetchReviews({ scrollToTop: true }),
  });

  const form = useReviewForm({
    state,
    onSuccess: () => actions.fetchReviews(),
  });

  const init = () => {
    actions.fetchReviews();
  };

  return {
    state,
    init,
    ...actions,
    ...form,
    goToPage,
    prevPage,
    nextPage,
    getPages,
  };
}
