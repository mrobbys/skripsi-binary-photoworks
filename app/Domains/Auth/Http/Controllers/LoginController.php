<?php

namespace App\Domains\Auth\Http\Controllers;

use App\Domains\Auth\Http\Requests\LoginRequest;
use App\Domains\Auth\Services\AuthService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\View\View;

class LoginController extends Controller
{
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
   * @param AuthService $authService
   * @return RedirectResponse
   */
  public function store(LoginRequest $request, AuthService $authService): RedirectResponse
  {
    $request->ensureIsNotRateLimited();
    $loginData = $request->toDto();

    if (!$authService->login($loginData)) {
      RateLimiter::hit($request->throttleKey(), 300);

      throw ValidationException::withMessages([
        'email' => 'Email atau password salah',
      ]);
    }

    RateLimiter::clear($request->throttleKey());
    $request->session()->regenerate();

    return redirect()->route('backdoor.dashboard');
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
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
  }
}
