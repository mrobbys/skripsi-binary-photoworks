export default function useState(Alpine) {
  return Alpine.reactive({
    selectedYear: new Date().getFullYear(),
    isLoadingCharts: false,
    chartData: {
      bookingTrend: null,
      revenueTrend: null,
      package: null,
      addon: null,
    },
  });
}
