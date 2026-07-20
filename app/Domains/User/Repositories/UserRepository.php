<?php

namespace App\Domains\User\Repositories;

use App\Domains\User\Models\User;

class UserRepository
{
    /**
     * Cari user berdasarkan id
     * @param int $userId
     */
    public function findById(int $userId): ?User
    {
        return User::find($userId);
    }

    /**
     * cari user berdasarkan email
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * cari user berdasarkan google id
     * @param string $googleId
     * @return User|null
     */
    public function findByGoogleId(string $googleId): ?User
    {
        return User::where('google_id', $googleId)->first();
    }

    /**
     * simpan user baru
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Update data user berdasarkan id
     * @param int $userId
     * @param array $data
     */
    public function updateById(int $userId, array $data): void
    {
        $user = $this->findById($userId);
        if ($user) {
            $user->update($data);
        }
    }
}
