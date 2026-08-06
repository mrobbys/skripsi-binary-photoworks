export default function useDashboardState(Alpine) {
  return Alpine.reactive({
    // semua data booking dari api
    appointments: [],
    // 'upcoming' | 'past'
    activeTab: "upcoming",
    // loading state list
    isLoading: false,
    // data booking yang sedang ditampilkan / dipilih
    selectedAppointment: null,
    // proses pembayaran midtrans
    isProcessingPayment: null,

    // apakah sedang memproses pembatalan
    isCancelling: null,

    // hari aktif studio (diisi dari blade via x-init)
    activeDays: [],
    // pagination — dikelola oleh useFrontdoorPagination
    currentPage: 1,
    lastPage: 1,
    total: 0,

    // apakah drawer reschedule terbuka
    isRescheduleOpen: false,
    // booking yang sedang dalam proses reschedule
    rescheduleTarget: null,
    // tanggal baru yang dipilih user (format: 'Y-m-d')
    selectedRescheduleDate: null,
    // slot waktu baru yang dipilih user
    selectedRescheduleSlot: null,
    // daftar slot waktu tersedia untuk tanggal yang dipilih
    rescheduleSlots: [],
    // sedang fetch slot waktu ke server
    isFetchingRescheduleSlots: false,
    // sedang submit reschedule ke server
    isRescheduling: false,
    // label tanggal reschedule yang diformat (diisi oleh useReschedule)
    formattedDate: null,
  });
}
