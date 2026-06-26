<?php

namespace Database\Seeders;

use App\Domains\MasterData\Models\Addon;
use Illuminate\Database\Seeder;

class AddonSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $addons = [
      [
        'name'         => 'Cetak Foto + Bingkai 10R',
        'price'        => 75000,
        'description'  => 'Cetak resolusi tinggi termasuk bingkai kayu minimalis berwarna hitam atau oak. Ukuran 10R (25x30cm).',
        'has_quantity' => true,  // Counter — klien bisa pilih lebih dari 1
        'is_active'    => true,
      ],
      [
        'name'         => 'Extra Time 30 Menit',
        'price'        => 50000,
        'description'  => 'Tambahan waktu pemotretan di dalam studio selama 30 menit untuk eksplorasi gaya lebih bebas.',
        'has_quantity' => false, // Checkbox — hanya sekali saja
        'is_active'    => true,
      ],
      [
        'name'         => 'Sewa Kostum Tambahan',
        'price'        => 35000,
        'description'  => 'Pilihan kostum tematik di luar paket utama (Casual, Traditional, atau Formal). Satu kostum per item.',
        'has_quantity' => true,  // Counter — bisa sewa lebih dari 1 kostum
        'is_active'    => true,
      ],
      [
        'name'         => 'Sewa Studio Lampu Tambahan',
        'price'        => 100000,
        'description'  => 'Penggunaan lighting kit profesional tambahan (Godox/Elinchrom) untuk efek dramatis atau low-key photography.',
        'has_quantity' => false, // Checkbox — set lampu, bukan per unit
        'is_active'    => true,
      ]
    ];

    foreach ($addons as $addon) {
      Addon::create($addon);
    }
  }
}
