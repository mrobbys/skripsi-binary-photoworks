<?php

namespace App\Domains\Auth\Http\Controllers;

use App\Domains\Auth\Http\Requests\LoginRequest;
use App\Domains\Auth\Services\AuthService;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use App\Domains\Auth\Traits\RedirectsUsers;

class LoginController extends Controller
{

  use RedirectsUsers;

  public function __construct(protected AuthService $authService) {}

  /**
   * Menampilkan halaman login.
   *
   * @return View
   */
  public function index(): View
  {
    return view('auth.login.index');
  }

  /**
   * Handle login pengguna.
   * 
   * @param LoginRequest $request
   * @return RedirectResponse
   */
  public function store(LoginRequest $request): RedirectResponse
  {
    $request->ensureIsNotRateLimited();
    $loginData = $request->toDto();

    if (!$this->authService->login($loginData)) {
      RateLimiter::hit($request->throttleKey(), 300);

      throw ValidationException::withMessages([
        'email' => 'Email atau password salah',
      ]);
    }

    RateLimiter::clear($request->throttleKey());
    $request->session()->regenerate();

    // return redirect()->route('backdoor.dashboard');
    return $this->redirectPath(Auth::user());
  }

  public function redirectToGoogle(): RedirectResponse
  {
    return Socialite::driver('google')->redirect();
  }

  public function handleGoogleCallback(): RedirectResponse
  {
    try {
      $googleUser = Socialite::driver('google')->user();
    } catch (\Exception $e) {
      return redirect()->route('login')->withErrors([
        'email' => 'Gagal login dengan Google. Silahkan coba lagi.'
      ]);
    }

    $user = $this->authService->loginWithGoogle($googleUser);
    Auth::login($user);
    request()->session()->regenerate();

    // return redirect()->route('backdoor.dashboard');
    return $this->redirectPath(Auth::user());
  }

  /**
   * Function logout.
   *
   * Setelah logout, pengguna dikembalikan ke halaman login.
   * @param Request $request
   * @return RedirectResponse
   */
  public function destroy(Request $request): RedirectResponse
  {
    $this->authService->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
  }
}
