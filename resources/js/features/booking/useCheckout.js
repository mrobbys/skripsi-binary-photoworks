import route from "../../lib/route";
import { Toast } from "../../lib/sweetalert";

export default function useCheckout({ state, buildAddonsPayload }) {
  const triggerCheckout = async () => {
    state.isProcessing = true;
    try {
      const res = await window.axios.post(route("frontdoor.booking.checkout"), {
        package_variant_id: state.selectedVariantId,
        background_id: state.selectedBackgroundId,
        booking_date: state.selectedDate,
        start_time: state.selectedSlot?.start_time,
        payment_scheme: state.paymentScheme,
        keterangan: state.keterangan,
        addons: buildAddonsPayload(),
      });

      if (!res.data.success) throw new Error(res.data.message || "Checkout gagal.");

      state.bookingCode = res.data.booking_code;
      const code = state.bookingCode;

      window.snap.pay(res.data.snap_token, {
        onSuccess: () => {
          window.location.href = route("frontdoor.booking.success", { bookingCode: code });
        },
        onPending: () => {
          Toast.fire({ icon: "info", title: "Menunggu pembayaran diselesaikan." });
          state.isProcessing = false;

          // TODO: Arahkan Klien ke halaman Dasbor / Riwayat Pesanan
          // Karena ini metode asinkron (BCA VA, Indomaret, dll), klien butuh melihat 
          // panduan transfer/kode bayar di Dasbor.
          // Contoh: window.location.href = route("frontdoor.dashboard.index");
        },
        onError: () => {
          Toast.fire({ icon: "error", title: "Pembayaran gagal. Silakan coba lagi." });
          state.isProcessing = false;

          // TODO: Arahkan Klien ke halaman Dasbor / Riwayat Pesanan
          // Beri tahu klien bahwa pembayaran gagal (misal kartu ditolak), 
          // sehingga klien bisa klik tombol "Bayar Sekarang" lagi di Dasbor.
          // Contoh: window.location.href = route("frontdoor.dashboard.index");
        },
        onClose: () => {
          Toast.fire({ icon: "warning", title: "Pembayaran dibatalkan. Slot masih tersimpan." });
          state.isProcessing = false;

          // TODO: Arahkan Klien ke halaman Dasbor / Riwayat Pesanan
          // Karena klien menutup pop-up, status booking masih "Pending". 
          // Jangan hapus data, biarkan Klien melanjutkan pembayaran dari Dasbor (selama belum expired).
          // Contoh: window.location.href = route("frontdoor.dashboard.index");
        },
      });
    } catch (err) {
      const msg = err?.response?.data?.message || err.message || "Terjadi kesalahan.";
      Toast.fire({ icon: "error", title: msg });
      state.isProcessing = false;
      console.log(err);
    }
  };

  return { triggerCheckout };
}
