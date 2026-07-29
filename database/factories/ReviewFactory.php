<?php

namespace Database\Factories;

use App\Domains\Review\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
  protected $model = Review::class;

  public function definition(): array
  {
    $komentarPositif = [
      'Hasil foto studio sangat memuaskan! Pencahayaannya bagus dan studionya bersih',
      'Pelayanan sangat ramah, kakak fotografer mau membantu mengarahkan gaya untuk aku yang mati gaya ini huhuhu😫',
      'Sesuai dengan ekspektasi. Hasil edit foto rapi dan link Google Drive cepat dikirim',
      'Timnya ramah dan sabar banget dan hasil fotonya bagus" banget, suka dehhh❤️❤️❤️',
      'Di binary sudah langanan dari lama, fotografer nya super baik, admin nya ramah, hasil sangat memuaskan dan dikasih bonus foto edit juga😊😊😊',
      'Always langganan di binary, hasilnya bagus" banget jadi susah milih foto untuk diedit😆'
    ];

    $komentarNegatif = [
      'Hasil foto lumayan, tapi waktu tunggu agak sedikit lambat dari jadwal sesi.',
      'Ruang tunggu agak sempit saat ramai, tapi hasil fotonya cukup bagus',
      'Pencahayaannya oke, cuma pilihan warna background kurang variatif menurut saya',
      'Kualitas cetak foto lumayan baik, tapi waktu pengiriman link gdrive agak lambat',
      'Hasil foto oke, tapi beberapa foto hilang karena adminnya salah upload foto'
    ];

    // 80% untuk rating yang tinggi (4-5), 20% untuk rating yang rendah/sedang (1-3)
    $rating = fake()->boolean(80)
      ? fake()->numberBetween(4, 5)
      : fake()->numberBetween(1, 3);

    $comment = ($rating >= 4)
      ? fake()->randomElement($komentarPositif)
      : fake()->randomElement($komentarNegatif);

    return [
      'rating' => $rating,
      'comment' => $comment,
      'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
      'updated_at' => function (array $attributes) {
        return $attributes['created_at'];
      }
    ];
  }
}
