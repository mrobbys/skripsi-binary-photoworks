import route from "@/lib/route";
import axiosInstance from "@/lib/axiosInstance";
import { Toast } from "@/lib/sweetalert";
import useState from "./useState";
import useDashboardCharts from "./useDashboardCharts";
import useChoices from "@/lib/useChoices";

export default function Dashboard(Alpine) {
  if (Alpine) {
    Alpine.data("choices", useChoices);
  }

  const state = useState(Alpine);
  const { charts, hasChartData, renderBookingTrendChart, renderRevenueTrendChart, renderPackageChart, renderAddonChart } =
    useDashboardCharts();

  let fetchDebounceTimer = null;

  const updateCharts = () => {
    Alpine.nextTick(() => {
      if (hasChartData(state.chartData.bookingTrend)) {
        renderBookingTrendChart(state.chartData.bookingTrend);
      } else {
        charts.bookingTrend?.destroy();
        charts.bookingTrend = null;
      }

      if (hasChartData(state.chartData.revenueTrend)) {
        renderRevenueTrendChart(state.chartData.revenueTrend);
      } else {
        charts.revenueTrend?.destroy();
        charts.revenueTrend = null;
      }

      if (hasChartData(state.chartData.package)) {
        renderPackageChart(state.chartData.package);
      } else {
        charts.package?.destroy();
        charts.package = null;
      }

      if (hasChartData(state.chartData.addon)) {
        renderAddonChart(state.chartData.addon);
      } else {
        charts.addon?.destroy();
        charts.addon = null;
      }
    });
  };

  const fetchChartData = async () => {
    state.isLoadingCharts = true;

    try {
      const res = await axiosInstance.get(route("backdoor.dashboard.analytics"), {
        params: { year: Number(state.selectedYear) },
      });

      const data = res.data;
      state.chartData.bookingTrend = data.booking_trend;
      state.chartData.revenueTrend = data.revenue_trend;
      state.chartData.package = data.package_proportion;
      state.chartData.addon = data.addon_proportion;
    } catch (err) {
      console.error(err);
      Toast.fire({ icon: "error", title: "Gagal memuat data" });
    } finally {
      state.isLoadingCharts = false;
    }

    updateCharts();
  };

  const debouncedFetchCharts = () => {
    clearTimeout(fetchDebounceTimer);
    fetchDebounceTimer = setTimeout(() => {
      const selectEl = document.getElementById("yearPicker");
      if (selectEl && selectEl._choices) {
        const val = selectEl._choices.getValue(true);
        if (val) {
          state.selectedYear = Number(val);
        }
      }
      fetchChartData();
    }, 300);
  };

  return {
    state,
    fetchChartData,
    debouncedFetchCharts,
    hasChartData,
  };
}

