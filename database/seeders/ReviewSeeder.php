<?php

namespace Database\Seeders;

use App\Domains\Review\Models\Review;
use App\Domains\User\Models\User;
use Illuminate\Database\Seeder;
use App\Domains\User\Enums\RoleType;

class ReviewSeeder extends Seeder
{
  public function run(): void
  {
    $users = User::role(RoleType::USER->value)->get();

    if ($users->isEmpty()) {
      $this->command->warn('Tidak ada user dengan role "user". Harap jalankan seeder user terlebih dahulu.');
      return;
    }

    foreach ($users as $user) {
      // 70% untuk memiliki review
      if (fake()->boolean(70)) {
        Review::factory()->create([
          'user_id' => $user->id,
        ]);
      }
    }
  }
}
