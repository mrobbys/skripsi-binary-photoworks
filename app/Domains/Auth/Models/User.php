<?php

namespace App\Domains\Auth\Models;

use Database\Factories\UserFactory;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Traits\HasRoles;

#[UseFactory(UserFactory::class)]
#[Fillable(['name', 'email', 'password', 'phone', 'google_id', 'google_token'])]
#[Hidden(['password', 'remember_token', 'google_token'])]
class User extends Authenticatable implements CanResetPasswordContract
{   
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, CanResetPassword;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Konfigurasi untuk activity log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone'])
            ->logOnlyDirty()
            ->useLogName('user')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => "User: {$this->name} telah dibuat",
                    'updated' => "User: {$this->name} telah diupdate",
                    'deleted' => "User: {$this->name} telah dihapus",
                    default => "User: {$this->name} telah di-{$eventName}",
                };
            });
    }
}
