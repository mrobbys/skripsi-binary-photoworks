export default function useState(Alpine) {
  return Alpine.reactive({
    // semua data booking dari api
    appointments: [],
    // 'upcoming' | 'past'
    activeTab: 'upcoming',
    // loading state
    isLoading: false,
    // data booking yang sedang ditampilkan / dipilih
    selectedAppointment: null,
    // proses pembayaran midtrans
    isProcessingPayment: null,
  })
}