<?php

namespace Database\Seeders;

use App\Domains\MasterData\Models\Background;
use Illuminate\Database\Seeder;

class BackgroundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $backgrounds = [
            [
                'name' => 'Putih Profil',
                'description' => 'Background layar putih polos yang clean, sangat cocok untuk pas foto profil resmi maupun foto casual minimalis.',
                'image' => database_path('seeders/images/putih-profile.jpg'),
                'is_active' => true,
            ],
            [
                'name' => 'Abstrak Abu',
                'description' => 'Background dengan tekstur dan motif abstrak berwarna abu-abu, memberikan kesan maskulin, elegan, dan profesional.',
                'image' => database_path('seeders/images/abstrak-abu.jpg'),
                'is_active' => true,
            ],
            [
                'name' => 'Abstrak Cream',
                'description' => 'Background dengan tekstur dan motif abstrak berwarna cream yang hangat, sangat pas untuk nuansa foto vintage, soft, atau romantis.',
                'image' => database_path('seeders/images/abstrak-cream.jpg'),
                'is_active' => true,
            ],
        ];

        foreach ($backgrounds as $bg) {
            $background = Background::create([
                'name' => $bg['name'],
                'description' => $bg['description'],
                'is_active' => $bg['is_active'],
            ]);

            // Melampirkan file dari local ke Supabase Storage (atau disk yang diset di .env)
            if (file_exists($bg['image'])) {
                $background->addMedia($bg['image'])
                    ->preservingOriginal() // SANGAT PENTING: agar file asli di folder seeders tidak ikut terhapus/dipindah
                    ->toMediaCollection('background-image');
            }
        }
    }
}
