/**
 * Logika interaktif halaman Login.
 *
 * File: resources/js/features/auth/Login.js
 * - State dikelola via Alpine.reactive() — mirip useState()
 * - Semua method adalah Arrow Function — tidak ada 'this'
 * - Auto-didaftarkan ke Alpine.data('Login') oleh app.js
 *
 * Penggunaan di Blade: <div x-data="Login">...</div>
 *
 * @param {import('alpinejs').Alpine} Alpine
 * @returns {object}
 */
export default function Login(Alpine) {
  // ---------------------------------------------------------------------------
  // State — terpusat, mirip useState() di React
  // ---------------------------------------------------------------------------
  const state = Alpine.reactive({
    showPassword: false,
    isLoading: false,
  });

  // ---------------------------------------------------------------------------
  // Methods — arrow functions, tidak ada 'this'
  // ---------------------------------------------------------------------------

  /** Toggle visibilitas field password */
  const togglePassword = () => {
    state.showPassword = !state.showPassword;
  };

  /**
   * Menangani submit form untuk menunjukkan state loading.
   * Karena ini form submit HTML biasa (non-AJAX), kita biarkan form submit secara normal
   * setelah mengubah state isLoading menjadi true.
   *
   * @param {SubmitEvent} event
   */
  const submitForm = (event) => {
    state.isLoading = true;
    // Kita tidak memanggil event.preventDefault() agar form tetap ter-submit ke server Laravel
  };

  // ---------------------------------------------------------------------------
  // Return — plain object yang dikonsumsi Alpine di HTML
  // ---------------------------------------------------------------------------
  return {
    state,
    togglePassword,
    submitForm,
  };
}
