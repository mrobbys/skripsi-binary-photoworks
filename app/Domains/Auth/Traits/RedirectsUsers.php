<?php

namespace App\Domains\Auth\Traits;

use App\Domains\Auth\Models\User;
use Illuminate\Http\RedirectResponse;

trait RedirectsUsers
{
  public function redirectPath(User $user): RedirectResponse
  {
    // TODO: jangan lupa dirubah untuk nanti untuk route nya yak
    $routeName = $user->hasRole('user') ? 'frontdoor.home' : 'backdoor.dashboard';

    return redirect()->intended(route($routeName));
  }
}
