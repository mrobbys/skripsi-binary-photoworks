<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\User\Models\User;
use App\Domains\User\Enums\RoleType;

class UserSeeder extends Seeder
{
  public function run(): void
  {
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

    $adminAccount = User::factory()->create([
      'name' => 'admin',
      'email' => 'admin@gmail.com'
    ]);
    $adminAccount->assignRole(RoleType::ADMIN->value);
    
    $personalAccount = User::factory()->create([
      'name' => 'robby',
      'email' => 'robby@gmail.com',
      'phone' => '6281936020227'
    ]);
    $personalAccount->assignRole(RoleType::USER->value);
  }
}
