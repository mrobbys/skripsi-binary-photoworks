<?php

namespace App\Domains\User\Http\Controllers;

use App\Domains\User\Http\Requests\ForgotPasswordRequest;
use App\Domains\User\Services\PasswordResetService;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
  public function __construct(protected PasswordResetService $passwordResetService) {}

  public function index(): View
  {
    return view('auth.forgot-password.index');
  }

  /**
   * Handle submit form forgot password
   * @param ForgotPasswordRequest $request
   */
  public function store(ForgotPasswordRequest $request): RedirectResponse
  {
    $email = $request->validated('email');
    $status = $this->passwordResetService->sendResetPasswordLink($email);

    if ($status === Password::RESET_LINK_SENT) {
      // simpan email ke session
      session()->put('forgot_email', $request->validated('email'));

      // kembalikan ke halaman check email
      return redirect()->route('forgot.password.check.email');
    }

    return back()->withErrors([
      'email' => __($status)
    ]);
  }

  /**
   * Tampilkan halaman check email setelah mengirim link reset password
   */
  public function show(): View|RedirectResponse
  {
    // jika session tidak ada kembalikan ke halaman forgot password
    if (!session()->has('forgot_email')) {
      return redirect()->route('forgot.password.index');
    }

    return view('auth.forgot-password.check-email');
  }

  /**
   * Kirim ulang link reset password
   */
  public function resend(): RedirectResponse
  {
    // ambil session forgot email
    $email = session('forgot_email');

    // jika session tidak ada kembalikan ke halaman forgot password
    if (!$email) {
      return redirect()->route('forgot.password.index');
    }

    // kirim ulang link reset password
    $this->passwordResetService->sendResetPasswordLink($email);

    return back()->with(
      'toast',
      $this->toast(
        title: 'Link berhasil dikirim ke email anda'
      )
    );
  }
}
