export default function useState(Alpine) {
  return Alpine.reactive({
    userId: null,
    packageId: null,
    variantId: null,
    backgroundId: null,
    bookingDate: "",
    startTime: "",
    bookingStatus: "",
    addons: [],

    variants: [],
    timeSlots: [],
    totalPrice: 0,

    isLoading: false,
    isTimeSlotsLoading: false,
    isUserSearching: false,
    userSearchTerm: "",
    selectedUser: null,
    sendWaNotification: false,
    errors: {},

    allPackages: [],
    allAddons: [],
  });
}
