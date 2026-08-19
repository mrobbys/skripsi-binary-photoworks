<?php

namespace App\Domains\User\Http\Controllers;

use App\Domains\User\Services\GoogleAuthService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Domains\User\Traits\RedirectsUsers;
use Illuminate\Support\Facades\Log;

class AuthGoogleController extends Controller
{
  use RedirectsUsers;

  public function __construct(protected GoogleAuthService $googleAuthService) {}

  /**
   * Arahkan login ke Google OAuth
   *
   * @return RedirectResponse
   */
  public function redirectToGoogle(): RedirectResponse
  {
    return Socialite::driver('google')->stateless()->redirect();
  }

  /**
   * Handle google OAuth callback
   *
   * @return RedirectResponse
   */
  public function handleGoogleCallback(): RedirectResponse
  {
    try {
      $googleUser = Socialite::driver('google')->stateless()->user();
    } catch (\Exception $e) {
      Log::error('Google Auth Error: ', ['error' => $e->getMessage()]);
      return redirect()->route('login')->withErrors([
        'email' => 'Gagal login dengan Google. Silahkan coba lagi.'
      ]);
    }

    [$user, $isNewUser] = $this->googleAuthService->loginWithGoogle($googleUser);
    Auth::login($user);
    request()->session()->regenerate();

    /**
     * * jika user baru (isNewUser) message = Daftar Akun Berhasil! 
     * * jika user lama (isNewUser) message = Login Berhasil!
     */
    $message = $isNewUser ? 'Daftar Akun Berhasil' : 'Login Berhasil!';

    return $this->redirectPath(Auth::user())
      ->with('toast', $this->toast(title: $message));
  }
}
