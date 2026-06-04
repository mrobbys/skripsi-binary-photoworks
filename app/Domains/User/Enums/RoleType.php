<?php

namespace App\Domains\User\Enums;

enum RoleType: string
{
    case SUPERADMIN = 'superadmin';
    case ADMIN = 'admin';
    case OWNER = 'owner';
    case USER = 'user';

    /**
     * Helper untuk menampilkan nama role ke UI
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPERADMIN => 'Superadmin',
            self::ADMIN => 'Admin',
            self::OWNER => 'Owner',
            self::USER => 'User',
        };
    }
}
