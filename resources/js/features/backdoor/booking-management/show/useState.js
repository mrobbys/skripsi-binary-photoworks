export default function useState(Alpine) {
  return Alpine.reactive({
    bookingId: null,
    bookingCode: "",
    booking: null, // data booking, update via AJAX
    isPageLoading: true,

    gdriveLink: "",
    gdriveErrors: {},
    isGdriveLoading: false,
    sendWaNotificationGdrive: true,

    summary: null,
    upsell: { addonId: null, quantity: 1, isLoading: false },
    allAddons: [],
  });
}
