export default function useState(Alpine) {
  return Alpine.reactive({
    // Widget stats
    totalToday: 0,
    doneToday: 0,
    upcomingTotal: 0,

    dateFilter: "",
  });
}
