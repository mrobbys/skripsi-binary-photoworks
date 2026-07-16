import route from "@/lib/route";
import { Toast } from "@/lib/sweetalert";
import axiosInstance from "@/lib/axiosInstance";
import { z } from "zod";

const formSchema = z.object({
  user_id: z.any().refine((val) => val, "Klien wajib dipilih."),
  package_id: z.any().refine((val) => val, "Paket utama wajib dipilih."),
  package_variant_id: z.any().refine((val) => val, "Varian wajib dipilih."),
  background_id: z.any().refine((val) => val, "Background wajib dipilih."),
  booking_date: z.any().refine((val) => val, "Tanggal sesi wajib diisi."),
  start_time: z.any().refine((val) => val, "Slot waktu wajib dipilih."),
  status: z.any().refine((val) => val, "Status booking wajib dipilih."),
});

export default function useCreateForm({ state }) {
  const loadTimeSlots = async () => {
    if (!state.bookingDate) return;

    const selectedVariant = state.variants.find(v => String(v.id) === String(state.variantId));
    if (!selectedVariant) return;

    state.isTimeSlotsLoading = true;
    try {
      const res = await axiosInstance.get(route("frontdoor.booking.api.slots"), {
          params: {
              date: state.bookingDate,
              duration: selectedVariant.duration || selectedVariant.duration_minutes
        },
      });
      state.timeSlots = Array.isArray(res.data.slots) ? res.data.slots : [];
      state.startTime = "";
    } catch {
      Toast.fire({ icon: "error", title: "Gagal memuat slot waktu." });
    } finally {
      state.isTimeSlotsLoading = false;
    }
  };

  const addAddonRow = () => state.addons.push({ id: Date.now() + Math.random(), addon_id: null, quantity: 1 });
  const removeAddonRow = (index) => state.addons.splice(index, 1);

  const onAddonChange = (item) => {
    const addonId = item.addon_id;
    const addon = state.allAddons.find((a) => a.id == addonId);
    if (addon && !addon.has_quantity) item.quantity = 1;
  };

  const isQtyDisabled = (item) => {
    const addon = state.allAddons.find((a) => a.id == item.addon_id);
    return addon ? !addon.has_quantity : false;
  };

    const hasValue = (value) => {
        return value !== null && value !== undefined && String(value).trim() !== "";
    }

    const isSubmitDisabled = () =>
      state.isLoading ||
      state.isTimeSlotsLoading ||
      !hasValue(state.userId) ||
      !hasValue(state.packageId) ||
      !hasValue(state.variantId) ||
      !hasValue(state.backgroundId) ||
      !hasValue(state.bookingDate) ||
      !hasValue(state.startTime) ||
      !hasValue(state.bookingStatus);

  const submit = async () => {
    state.isLoading = true;
    state.errors = {};
    const payload = {
      user_id: state.userId,
      package_id: state.packageId,
      package_variant_id: state.variantId,
      background_id: state.backgroundId,
      booking_date: state.bookingDate,
      start_time: state.startTime,
      status: state.bookingStatus,
      send_wa_notification: state.sendWaNotification,
      addons: state.addons.filter((a) => a.addon_id),
    };

    const parsed = formSchema.safeParse(payload);
    if (!parsed.success) {
      state.errors = parsed.error.flatten().fieldErrors;
      Toast.fire({ icon: "warning", title: "Periksa kembali isian form." });
      state.isLoading = false;
      return;
    }

    try {
      await axiosInstance.post(route("backdoor.booking-management.store"), payload);
      Toast.fire({ icon: "success", title: "Booking berhasil dibuat." });
      window.location.href = route("backdoor.booking-management.index");
    } catch (err) {
      if (err.response?.status === 422) {
          state.errors = err.response.data.errors;
        Toast.fire({ icon: "warning", title: err.response.data.message });
      } else {
        Toast.fire({ icon: "error", title: "Terjadi kesalahan." });
      }
    } finally {
      state.isLoading = false;
    }
  };

  return {
    loadTimeSlots,
    addAddonRow,
    removeAddonRow,
    onAddonChange,
    isQtyDisabled,
    isSubmitDisabled,
    submit,
  };
}
