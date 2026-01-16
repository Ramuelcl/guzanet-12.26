<?php

namespace Database\Factories\backend;

use App\Models\backend\Entidad;
use App\Models\backend\Tabla;
use Illuminate\Database\Eloquent\Factories\Factory;

class EntidadFactory extends Factory
{
  protected $model = Entidad::class;
  // public $entidad, $sexo;

  public function definition(): array
  {
    $t = new Tabla();
    $array = $t->qTablas(config('constantes.TIPO_ENTIDAD'));
    $tipoEntidad = $array ? array_rand($array) : 1;

    $t = new Tabla();
    $array = $t->qTablas(config('constantes.SEXO'));
    $sexo = $array ? array_rand($array) : 1;

    $nombre = $sexo == 1 ? $this->faker->firstNameMale : $this->faker->firstNameFemale;
    $titulo = $sexo == 1 ? $this->faker->titleMale : $this->faker->titleFemale;
    $razonSocial = rand(0, 1) ? $this->faker->company : null;
    $website = rand(0, 1) ? $this->faker->url : null;
    return [
      'tipoEntidad' => $tipoEntidad,
      'sexo' => $sexo,
      'razonSocial' => $razonSocial,
      'website' => $website,
      'titulo' => $titulo,
      'nombres' => $nombre,
      'apellidos' => $this->faker->lastName,
      'is_active' => (int) $this->faker->boolean(80), // 80% de probabilidad de ser true
      'aniversario' => $this->faker->date,
    ];
  }
}
