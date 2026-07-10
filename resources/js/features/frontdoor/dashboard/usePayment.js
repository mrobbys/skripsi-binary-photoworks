import route from '@/lib/route';
import { Toast } from '@/lib/sweetalert';

export default function usePayment({ state, fetchAppointments }) {
  // Trigger "Bayar Sekarang" dari dashboard
  const triggerRepay = async (bookingCode) => {
    state.isProcessingPayment = true;

    try {
      const res = await window.axios.post(route('frontdoor.dashboard.repay'), {
        booking_code: bookingCode,
      });

      if (!res.data.success) {
        throw new Error(res.data.message || 'Gagal mendapatkan token pembayaran.');
      }

      const snapToken = res.data.snap_token;

      window.snap.pay(snapToken, {
        onSuccess: () => {
          Toast.fire({ icon: 'success', title: 'Pembayaran berhasil!' });
          state.selectedAppointment = null;
          fetchAppointments();
        },
        onPending: () => {
          Toast.fire({ icon: 'info', title: 'Menunggu pembayaran diselesaikan.' });
          state.isProcessingPayment = false;
          state.selectedAppointment = null;
          fetchAppointments();
        },
        onError: () => {
          Toast.fire({ icon: 'error', title: 'Pembayaran gagal. Silakan coba lagi.' });
          state.isProcessingPayment = false;
        },
        onClose: () => {
          Toast.fire({ icon: 'warning', title: 'Pembayaran dibatalkan. Tagihan masih tersimpan.' });
          state.isProcessingPayment = false;
        },
      });
    } catch (err) {
      const msg = err?.response?.data?.message || err.message;
      console.log(msg);
      Toast.fire({ icon: 'error', title: 'Terjadi kesalahan.' });
      state.isProcessingPayment = false;
    }
  };

  return {
    triggerRepay,
  };
}