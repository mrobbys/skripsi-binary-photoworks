<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
            AddonSeeder::class,
            UserSeeder::class,
            ReviewSeeder::class
        ]);
    }
}
