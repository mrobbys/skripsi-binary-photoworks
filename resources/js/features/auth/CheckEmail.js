import axiosInstance from '@/lib/axiosInstance';
import { Toast } from '@/lib/sweetalert';
import route from '@/lib/route';

export default function CheckEmail(Alpine) {
  const state = Alpine.reactive({
    isResending: false,
  });

  const resend = async () => {
    state.isResending = true;
    try {
      await axiosInstance.post(route('forgot.password.resend'));
      Toast.fire({ icon: 'success', title: 'Link berhasil dikirim ke email Anda' });
    } catch (error) {
      console.error(error);
      Toast.fire({ icon: 'error', title: 'Gagal mengirim ulang. Coba lagi.' });
    } finally {
      state.isResending = false;
    }
  };

  return { state, resend };
}
