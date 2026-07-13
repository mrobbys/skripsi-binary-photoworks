import axios from "axios";
import { Toast } from "./sweetalert";

const axiosInstance = axios.create({
  timeout: 30000,
  headers: {
    "X-Requested-With": "XMLHttpRequest",
    Accept: "application/json",
  },
});

axiosInstance.interceptors.response.use(
  (response) => response,
  (error) => {
    // Server mati / tidak ada koneksi
    if (!error.response) {
      Toast.fire({ icon: "error", title: "Tidak dapat terhubung ke server." });
      return Promise.reject(error);
    }

    const { status, data } = error.response;

    switch (status) {
      case 401: // Belum login
        window.location.href = "/login";
        break;

      case 419: // CSRF Expired
        Toast.fire({ icon: "warning", title: "Sesi kadaluarsa. Memuat ulang..." });
        setTimeout(() => window.location.reload(), 1500);
        break;

      case 403: // Forbidden
      case 404: // Not Found
      case 500: // Server Error
        Toast.fire({ icon: "error", title: data.message || "Terjadi kesalahan sistem." });
        break;

      case 422:
      case 400:
        break;
    }

    return Promise.reject(error);
  },
);

export default axiosInstance;
