<?php

namespace Database\Seeders;

use App\Domains\User\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Domains\User\Enums\RoleType;
use Illuminate\Support\Facades\Storage;
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // list folder di supabase storage yang ingin dibersihkan ketika menjalankan migrate:fresh --seed
        $foldersToClean = ['backgrounds'];
        $disk = env('MEDIA_DISK', 's3');
        
        foreach ($foldersToClean as $folder) {
            Storage::disk($disk)->deleteDirectory($folder);
        }

        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            CategorySeeder::class,
            PackageSeeder::class,
            ScheduleSeeder::class,
            BackgroundSeeder::class,
            AddonSeeder::class
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
