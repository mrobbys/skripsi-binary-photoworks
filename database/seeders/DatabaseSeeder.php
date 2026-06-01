<?php

namespace Database\Seeders;

use App\Domains\Auth\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        User::factory(5)
            ->create()
            ->each(function ($user) {
                $user->assignRole('user');
            });

        $personalAccount = User::factory()->create([
            'name' => 'robby',
            'email' => 'robby@gmail.com',
        ]);
        $personalAccount->assignRole('user');
    }
}
