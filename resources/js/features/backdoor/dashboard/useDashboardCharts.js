import Chart from "chart.js/auto";

export default function useDashboardCharts() {
  const charts = {
    bookingTrend: null,
    revenueTrend: null,
    package: null,
    addon: null,
  };

  const hasChartData = (chartPayload) => {
    if (!chartPayload || !Array.isArray(chartPayload.data) || chartPayload.data.length === 0) {
      return false;
    }
    return chartPayload.data.some((val) => Number(val) > 0);
  };

  const generateStonePalette = (count) => {
    const colors = [];
    for (let i = 0; i < count; i++) {
      const lightness = Math.round(15 + (i * 70) / Math.max(count - 1, 1));
      const saturation = i % 2 === 0 ? 10 : 18;
      colors.push(`hsl(30, ${saturation}%, ${lightness}%)`);
    }
    return colors;
  };

  const renderBookingTrendChart = ({ labels, data }) => {
    const canvas = document.getElementById("bookingTrendChart");
    if (!canvas) return;

    if (charts.bookingTrend) {
      charts.bookingTrend.data.labels = labels;
      charts.bookingTrend.data.datasets[0].data = data;
      charts.bookingTrend.update();
      return;
    }

    const ctx = canvas.getContext("2d");
    charts.bookingTrend = new Chart(ctx, {
      type: "bar",
      data: {
        labels,
        datasets: [{ label: "Jumlah Sesi", data, backgroundColor: "#78716C" }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
      },
    });
  };

  const renderRevenueTrendChart = ({ labels, data }) => {
    const canvas = document.getElementById("revenueTrendChart");
    if (!canvas) return;

    if (charts.revenueTrend) {
      charts.revenueTrend.data.labels = labels;
      charts.revenueTrend.data.datasets[0].data = data;
      charts.revenueTrend.update();
      return;
    }

    const ctx = canvas.getContext("2d");
    charts.revenueTrend = new Chart(ctx, {
      type: "line",
      data: {
        labels,
        datasets: [
          {
            label: "Pendapatan",
            data,
            borderColor: "#78716C",
            backgroundColor: "rgba(120,113,108,0.1)",
            fill: true,
            tension: 0.4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: (value) => {
                if (value >= 1_000_000) return `${value / 1_000_000}jt`;
                if (value >= 1_000) return `${value / 1_000}rb`;
                return value;
              },
            },
          },
        },
      },
    });
  };

  const renderPackageChart = ({ labels, data }) => {
    const canvas = document.getElementById("packageChart");
    if (!canvas) return;

    const colors = generateStonePalette(labels.length);

    if (charts.package) {
      charts.package.data.labels = labels;
      charts.package.data.datasets[0].data = data;
      charts.package.data.datasets[0].backgroundColor = colors;
      charts.package.update();
      return;
    }

    const ctx = canvas.getContext("2d");
    charts.package = new Chart(ctx, {
      type: "doughnut",
      data: {
        labels,
        datasets: [{ data, backgroundColor: colors }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: "bottom" } },
      },
    });
  };

  const renderAddonChart = ({ labels, data }) => {
    const canvas = document.getElementById("addonChart");
    if (!canvas) return;

    const colors = generateStonePalette(labels.length);

    if (charts.addon) {
      charts.addon.data.labels = labels;
      charts.addon.data.datasets[0].data = data;
      charts.addon.data.datasets[0].backgroundColor = colors;
      charts.addon.update();
      return;
    }

    const ctx = canvas.getContext("2d");
    charts.addon = new Chart(ctx, {
      type: "pie",
      data: {
        labels,
        datasets: [{ data, backgroundColor: colors }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: "bottom" } },
      },
    });
  };

  return {
    charts,
    hasChartData,
    renderBookingTrendChart,
    renderRevenueTrendChart,
    renderPackageChart,
    renderAddonChart,
  };
}
