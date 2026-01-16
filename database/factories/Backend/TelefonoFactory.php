<?php

namespace Database\Factories\backend;

use App\Models\backend\Tabla;

use Illuminate\Database\Eloquent\Factories\Factory;

class TelefonoFactory extends Factory
{
  public function definition(): array
  {
    $t = new Tabla();
    $array = $t->qTablas(config('constantes.TIPO_TELEFONO'));
    $tipo = $array ? array_rand($array) : 1;

    return [
      'numero' => $this->faker->phoneNumber,
      'tipo' => $tipo,
    ];
  }
}
