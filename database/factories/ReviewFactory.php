<?php

namespace Database\Factories;

use App\Domains\Review\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
  protected $model = Review::class;

  public function definition(): array
  {
    // 80% untuk rating yang tinggi, 20% untuk rating yang rendah
    $isGoodRating = $this->faker->boolean(80);

    return [
      'rating' => $isGoodRating ? $this->faker->numberBetween(4, 5) : $this->faker->numberBetween(1, 3),
      'comment' => $this->faker->sentences($this->faker->numberBetween(1, 3), true),
      'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
      'updated_at' => function (array $attributes) {
        return $attributes['created_at'];
      }
    ];
  }
}
