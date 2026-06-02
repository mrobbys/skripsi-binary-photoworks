<?php

namespace App\Domains\Auth\Traits;

use App\Domains\Auth\Models\User;
use Illuminate\Http\RedirectResponse;
use App\Domains\Auth\Enums\RoleType;

trait RedirectsUsers
{
  public function redirectPath(User $user): RedirectResponse
  {
    $roleUser = RoleType::USER->value;

    // TODO: jangan lupa dirubah untuk nanti untuk route nya yak
    $routeName = $user->hasRole($roleUser) ? 'frontdoor.home' : 'backdoor.dashboard';

    return redirect()->intended(route($routeName));
  }
}
