<?php

namespace App\Domains\Auth\Http\Controllers;

use App\Domains\Auth\Services\AuthService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{

  public function __construct(protected AuthService $authService) {}

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

    return redirect()->route('login')
      ->with('toast', $this->toast(
        title: 'Logout Berhasil'
      ));
  }
}
