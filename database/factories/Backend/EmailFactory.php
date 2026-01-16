<?php

namespace Database\Factories\backend;

use App\Models\backend\Tabla;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailFactory extends Factory
{
  public function definition(): array
  {
    $t = new Tabla();
    $array = $t->qTablas(config('constantes.TIPO_EMAIL'));
    $tipo = $array ? array_rand($array) : 1;
    return [
      'mail' => $this->faker->unique()->safeEmail,
      'tipo' => $tipo,

    ];
  }
}
