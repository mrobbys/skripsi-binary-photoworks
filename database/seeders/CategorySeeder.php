<?php

namespace Database\Seeders;

use App\Domains\MasterData\Models\Category;
use Illuminate\Database\Seeder;
use \Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'category_code' => 'CPS',
                'name' => 'Custom Photoshoot',
                'is_active' => true,
            ],
            [
                'category_code' => 'BDY',
                'name' => 'Birthday',
                'is_active' => true,
            ],
            [
                'category_code' => 'ENG',
                'name' => 'Engagement',
                'is_active' => true,
            ],
            [
                'category_code' => 'TDC',
                'name' => 'Traditional Ceremony',
                'is_active' => true,
            ],
            [
                'category_code' => 'SYU',
                'name' => 'Syukuran',
                'is_active' => true,
            ],
            [
                'category_code' => 'EVT',
                'name' => 'Event',
                'is_active' => true,
            ],
            [
                'category_code' => 'CPL',
                'name' => 'Couple Session',
                'is_active' => true,
            ],
            [
                'category_code' => 'MAT',
                'name' => 'Maternity',
                'is_active' => true,
            ],
            [
                'category_code' => 'PSN',
                'name' => 'Personal',
                'is_active' => true,
            ],
            [
                'category_code' => 'GRP',
                'name' => 'Group',
                'is_active' => true,
            ],
            [
                'category_code' => 'FAM',
                'name' => 'Family',
                'is_active' => true,
            ],
            [
                'category_code' => 'OHS',
                'name' => 'Outdoor / Home Service',
                'is_active' => true,
            ],
            [
                'category_code' => 'GRD',
                'name' => 'Graduation',
                'is_active' => true,
            ],
            [
                'category_code' => 'WDG',
                'name' => 'Wedding',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['category_code' => $category['category_code']],
                [
                    'name' => $category['name'],
                    'slug' => Str::slug($category['name']),
                    'is_active' => $category['is_active'],
                ]
            );
        }
    }
}
