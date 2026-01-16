<?php

namespace Database\Factories\backend;

use App\Models\backend\Ciudad;
use App\Models\backend\Pais;
use Illuminate\Database\Eloquent\Factories\Factory;

class CiudadFactory extends Factory
{
  protected $model = Ciudad::class;

  public function definition(): array
  {
    return [
      'nombre' => $this->faker->city,
      'pais_id' => Pais::factory(),
    ];
  }
}
