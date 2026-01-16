<?php

namespace Database\Seeders;

use App\Models\Backend\Ciudad;
use App\Models\Backend\CodigoPostal;
use App\Models\backend\Direccion;
use App\Models\Backend\Email;
use App\Models\Backend\Entidad;
use App\Models\Backend\Pais;
use App\Models\Backend\Telefono;
//
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EntidadSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Primero crea los países
    Pais::create(['nombre' => 'Chile']);
    Pais::create(['nombre' => 'Francia']);
    Pais::create(['nombre' => 'Alemania']);

    // Obtén todos los países creados
    $paises = Pais::all();

    // Para cada país, crea ciudades
    $paises->each(function ($pais) {
      $ciudades = Ciudad::factory()->count(3)->create([
        'pais_id' => $pais->id,
      ]);

      // Para cada ciudad, crea códigos postales
      $ciudades->each(function ($ciudad) {
        $codigosPostales = CodigoPostal::factory()->count(3)->create([
          'ciudad_id' => $ciudad->id,
        ]);

        // Crea 5 entidades, cada una con entre 1 y 2 direcciones
        Entidad::factory(15)->create()->each(function ($entidad) use ($codigosPostales) {
          Direccion::factory(rand(1, 2))->create([
            'entidad_id' => $entidad->id,
            'cp_id' => $codigosPostales->random()->id, // Asocia una dirección a un código postal aleatorio
          ]);

          // Crea entre 0 y 3 correos electrónicos para cada entidad
          Email::factory(rand(0, 2))->create([
            'entidad_id' => $entidad->id,
          ]);
          // Crea entre 0 y 3 teléfonos para cada entidad
          Telefono::factory(rand(0, 2))->create([
            'entidad_id' => $entidad->id,
          ]);
        });
      });
    });
  }
}
