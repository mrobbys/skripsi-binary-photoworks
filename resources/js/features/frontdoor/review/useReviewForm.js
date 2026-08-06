import { z } from "zod";
import { getFieldError } from "@/lib/zodHelper";
import { Toast } from "@/lib/sweetalert";
import route from "@/lib/route";
import axiosInstance from "@/lib/axiosInstance";

export default function useReviewForm({ state, onSuccess }) {
  const schema = z.object({
    comment: z.string().min(1, "Ulasan wajib diisi").max(500, "Maksimal 500 karakter"),
  });

  const validateField = (field) => {
    state.dismissedErrors[field] = true;
    const result = schema.safeParse({ comment: state.form.comment });

    if (!result.success) {
      state.errors[field] = getFieldError(result, field);
    } else {
      state.errors[field] = null;
    }
  };

  const setRating = (n) => {
    state.form.rating = n;
  };

  const setHover = (n) => {
    state.hoverRating = n;
  };

  const clearHover = () => {
    state.hoverRating = 0;
  };

  const resetForm = () => {
    state.openModal = false;
    state.hoverRating = 0;
    state.form.rating = 0;
    state.form.comment = "";
    state.errors = {};
    state.dismissedErrors = {};
  };

  const submitReview = async () => {
    if (!state.form.rating || !state.form.comment.trim()) return;

    const result = schema.safeParse({ comment: state.form.comment });
    if (!result.success) {
      state.errors = {
        comment: getFieldError(result, "comment"),
      };
      return;
    }

    state.isLoading = true;

    try {
      const res = await axiosInstance.post(route("frontdoor.reviews.store"), {
        rating: state.form.rating,
        comment: state.form.comment,
      });

      Toast.fire({ icon: "success", title: res.data.message });
      resetForm();
      if (onSuccess) await onSuccess();
    } catch (err) {
      if (err.response?.status === 422) {
        state.errors = err.response.data.errors ?? {};
        Toast.fire({ icon: "error", title: err?.response?.data?.message ?? "Gagal mengirim ulasan." });
      } else {
        Toast.fire({ icon: "error", title: "Gagal mengirim ulasan." });
        console.log(err);
      }
    } finally {
      state.isLoading = false;
    }
  };

  return {
    validateField,
    setRating,
    setHover,
    clearHover,
    resetForm,
    submitReview,
  };
}
