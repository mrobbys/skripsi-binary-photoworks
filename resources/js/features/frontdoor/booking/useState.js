export default function useState(Alpine) {
  return Alpine.reactive({
    // Wizard
    currentStep: 1,
    packageName: null,

    // Step 1
    allVariants: [],
    selectedVariantId: null,
    selectedVariant: null,
    selectedBackgroundId: null,

    // Step 2
    activeDays: [],
    selectedDate: null,
    formattedDate: null,
    availableSlots: [],
    selectedSlot: null,
    isFetchingSlots: false,

    // Step 3
    allAddons: [],
    selectedAddons: {}, // { addon_id: quantity }

    // Step 4
    paymentScheme: "lunas",
    isProcessing: false,
    bookingCode: null,
    notes: null,
  });
}
