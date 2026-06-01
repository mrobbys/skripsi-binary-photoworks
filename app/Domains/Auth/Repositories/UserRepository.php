<?php

namespace App\Domains\Auth\Repositories;

use App\Domains\Auth\Models\User;

class UserRepository
{
    /**
     * cari user berdasarkan email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * cari user berdasarkan google id | socialite
     * 
     * @param string $googleId
     * @return User|null
     */
    public function findByGoogleId(string $googleId): ?User
    {
        return User::where('google_id', $googleId)->first();
    }

    /**
     * simpan user baru
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return User::create($data);
    }
}
