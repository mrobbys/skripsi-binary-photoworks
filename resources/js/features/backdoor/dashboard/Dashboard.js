import route from "@/lib/route";
import axiosInstance from "@/lib/axiosInstance";
import { Toast } from "@/lib/sweetalert";
import useState from "./useState";
import useDashboardCharts from "./useDashboardCharts";

export default function Dashboard(Alpine) {
  const state = useState(Alpine);
  const { hasChartData, renderBookingTrendChart, renderRevenueTrendChart, renderPackageChart, renderAddonChart } =
    useDashboardCharts();

  let fetchDebounceTimer = null;

  const fetchChartData = async () => {
    state.isLoadingCharts = true;

    try {
      const res = await axiosInstance.get(route("backdoor.dashboard.analytics"), {
        params: { year: state.selectedYear },
      });

      const data = res.data;
      state.chartData.bookingTrend = data.booking_trend;
      state.chartData.revenueTrend = data.revenue_trend;
      state.chartData.package = data.package_proportion;
      state.chartData.addon = data.addon_proportion;

      Alpine.nextTick(() => {
        if (hasChartData(state.chartData.bookingTrend)) renderBookingTrendChart(state.chartData.bookingTrend);
        if (hasChartData(state.chartData.revenueTrend)) renderRevenueTrendChart(state.chartData.revenueTrend);
        if (hasChartData(state.chartData.package)) renderPackageChart(state.chartData.package);
        if (hasChartData(state.chartData.addon)) renderAddonChart(state.chartData.addon);
      });
    } catch (err) {
      console.error(err);
      Toast.fire({ icon: "error", title: "Gagal memuat data." });
    } finally {
      state.isLoadingCharts = false;
    }
  };

  const debouncedFetchCharts = () => {
    clearTimeout(fetchDebounceTimer);
    fetchDebounceTimer = setTimeout(() => fetchChartData(), 400);
  };

  return {
    state,
    fetchChartData,
    debouncedFetchCharts,
    hasChartData,
  };
}
