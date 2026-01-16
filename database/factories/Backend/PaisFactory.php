<?php

namespace Database\Factories\backend;

use App\Models\backend\Pais;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaisFactory extends Factory
{
  protected $model = Pais::class;

  public function definition(): array
  {
    return [
      'nombre' => $this->faker->country,
    ];
  }
}
