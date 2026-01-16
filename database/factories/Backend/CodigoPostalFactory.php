<?php

namespace Database\Factories\backend;

use App\Models\backend\Ciudad;
use App\Models\backend\CodigoPostal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CodigoPostalFactory extends Factory
{
  protected $model = CodigoPostal::class;

  public function definition(): array
  {
    $pc = $this->faker->postcode;
    return [
      'cp' => Str::substr($string = $pc, $start = 0, $length = 5),
      'ciudad_id' => Ciudad::factory(),
    ];
  }
}
