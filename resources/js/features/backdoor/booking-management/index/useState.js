export default function useState(Alpine) {
  return Alpine.reactive({
    // stats
    totalRevenue: 0,
    countSuccess: 0,
    countDpPaid: 0,
    
    gdriveLink: "",
    isGdriveOpen: false,
    isGdriveLoading: false,

    gdriveErrors: {},
  });
}