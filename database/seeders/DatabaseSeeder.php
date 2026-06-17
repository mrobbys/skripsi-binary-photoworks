<?php

namespace Database\Seeders;

use App\Domains\User\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Domains\User\Enums\RoleType;

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
            ScheduleSeeder::class,
            CategorySeeder::class,
        ]);

        User::factory(5)
            ->create()
            ->each(function ($user) {
                $user->assignRole(RoleType::USER->value);
            });

        $superadminAccount = User::factory()->create([
            'name' => 'superadmin',
            'email' => 'superadmin@gmail.com'
        ]);
        $superadminAccount->assignRole(RoleType::SUPERADMIN->value);

        $personalAccount = User::factory()->create([
            'name' => 'robby',
            'email' => 'robby@gmail.com',
        ]);
        $personalAccount->assignRole(RoleType::USER->value);
    }
}
