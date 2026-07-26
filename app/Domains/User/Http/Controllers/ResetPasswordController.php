<?php

namespace App\Domains\User\Http\Controllers;

use App\Domains\User\DTOs\ResetPasswordData;
use App\Domains\User\Http\Requests\ResetPasswordRequest;
use App\Domains\User\Services\PasswordResetService;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    public function __construct(protected PasswordResetService $passwordResetService) {}

    /**
     * Tampilkan halaman reset password
     * Mengambil token dan email dari URL lalu mengirimkannya ke view
     */
    public function index(string $token): View
    {
        return view('auth.reset-password.index', [
            'token' => $token,
            'email' => request()->query('email', ''),
        ]);
    }

    /**
     * Handle submit form reset password
     * @param ResetPasswordRequest $request
     */
    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $status = $this->passwordResetService->resetPassword(ResetPasswordData::fromRequest($request));

        if ($status === Password::PASSWORD_RESET) {
            // hapus session forgot email
            session()->forget('forgot_email');

            // lalu pindah ke halaman login dan kirimkan pesan
            return redirect()
                ->route('login')
                ->with(
                    'toast',
                    $this->toast(title: 'Password berhasil diperbarui! Silahkan login.')
                );
        }

        return back()->withErrors([
            'email' => __($status),
        ]);
    }
}
