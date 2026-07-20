<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;

class AuthEventSubscriber
{
    public function handleLogin(Login $event): void
    {
        $activity = activity('otentikasi');
        
        if ($event->user instanceof Model) {
            $activity->causedBy($event->user);
        }
        
        $activity->log('login');
    }

    public function handleLogout(Logout $event): void
    {
        $activity = activity('otentikasi');
        
        if ($event->user instanceof Model) {
            $activity->causedBy($event->user);
        }
        
        $activity->log('logout');
    }

    public function handleRegistered(Registered $event): void
    {
        $activity = activity('otentikasi');
        
        if ($event->user instanceof Model) {
            $activity->causedBy($event->user);
        }
        
        $activity->log('registered');
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        $activity = activity('otentikasi');
        
        if ($event->user instanceof Model) {
            $activity->causedBy($event->user);
        }
        
        $activity->log('password_reset');
    }

    public function handleFailed(Failed $event): void
    {
        $activity = activity('otentikasi')
            ->withProperties([
                'email' => $event->credentials['email'] ?? 'unknown',
            ]);

        if ($event->user instanceof Model) {
            $activity->causedBy($event->user);
        }

        $activity->log('failed_login');
    }

    public function handleLockout(Lockout $event): void
    {
        activity('otentikasi')
            ->withProperties([
                'ip' => $event->request->ip(),
                'user_agent' => $event->request->userAgent(),
            ])
            ->log('lockout');
    }
}
