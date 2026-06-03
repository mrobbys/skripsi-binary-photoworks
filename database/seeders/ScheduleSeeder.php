<?php

namespace Database\Seeders;

use App\Domains\MasterData\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $days = [
            ['day' => 1, 'start_time' => '09:00', 'end_time' => '21:00', 'is_active' => true],
            ['day' => 2, 'start_time' => '09:00', 'end_time' => '21:00', 'is_active' => true],
            ['day' => 3, 'start_time' => '09:00', 'end_time' => '21:00', 'is_active' => true],
            ['day' => 4, 'start_time' => '09:00', 'end_time' => '21:00', 'is_active' => true],
            ['day' => 5, 'start_time' => '09:00', 'end_time' => '21:00', 'is_active' => true],
            ['day' => 6, 'start_time' => '09:00', 'end_time' => '21:00', 'is_active' => true],
            ['day' => 7, 'start_time' => '09:00', 'end_time' => '21:00', 'is_active' => false], // Minggu libur
        ];

        foreach ($days as $day) {
            Schedule::updateOrCreate(['day' => $day['day']], $day);
        }
    }
}
