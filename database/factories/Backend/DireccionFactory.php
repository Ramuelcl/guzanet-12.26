<?php

namespace Database\Factories\backend;

use App\Models\backend\Direccion;
use App\Models\backend\Tabla;
use Illuminate\Database\Eloquent\Factories\Factory;

class DireccionFactory extends Factory
{
  protected $model = Direccion::class;

  public function definition(): array
  {
    $t = new Tabla();
    $array = $t->qTablas(config('constantes.TIPO_DATOS'));
    $tipo = $array ? array_rand($array) : 1;
    return [
      'entidad_id' => \App\Models\backend\Entidad::factory(), // Asume que Entidad también tiene un factory
      'numero' => $this->faker->buildingNumber,
      'calle' => $this->faker->streetName,
      'cp_id' => \App\Models\backend\CodigoPostal::factory(), // Asume que CodigoPostal también tiene un factory
      'tipo' => $tipo,
    ];
  }
}
