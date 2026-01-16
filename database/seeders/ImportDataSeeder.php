<?php
// Database\Seeders\ImportDataSeeder.php

namespace Database\Seeders;

use App\Models\backend\Ciudad;
use App\Models\backend\CodigoPostal;
use App\Models\Backend\Direccion;
use App\Models\Backend\Email;
use App\Models\Backend\Entidad;
use App\Models\backend\Pais;
use App\Models\Backend\Telefono;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportDataSeeder extends Seeder {
  /**
   * Run the database seeds.
   */
  public function run(): void {
    $this->command->info('Iniciando la importación de datos...');

    DB::transaction(function () {
      $this->importExactCopyFromOrigen0();
    });

    $this->command->info('Importación de datos completada con éxito.');
  }

  /**
   * Importa datos desde la conexión 'origen0' (guzanet).
   */
  private function importExactCopyFromOrigen0(): void {
    $this->command->info('Iniciando copia exacta desde la conexión "origen0" (guzanet)...');

    // Mapeo de tablas de origen a destino. El orden es crucial para las dependencias.
    $tablesToCopy = [
      'paises' => 'paises',
      'ciudades' => 'ciudades',
      'codigospostales' => 'codigospostales',
      'entidades' => 'entidades',
      'telefonos' => 'entidades_telefonos',
      'emails' => 'entidades_emails',
      'direcciones' => 'entidades_direcciones',
    ];

    try {
      Schema::disableForeignKeyConstraints();

      // Vaciar las tablas en orden inverso para evitar problemas de dependencias
      foreach (array_reverse($tablesToCopy) as $destTable) {
        $this->command->line("Vaciando tabla de destino: {$destTable}...");
        DB::table($destTable)->truncate();
      }

      // Copiar los datos tabla por tabla
      foreach ($tablesToCopy as $sourceTable => $destTable) {
        $this->command->line("Copiando datos desde '{$sourceTable}' hacia '{$destTable}'...");

        $sourceData = DB::connection('origen0')->table($sourceTable)->get();

        if ($sourceData->isEmpty()) {
          $this->command->warn(" -> La tabla de origen '{$sourceTable}' está vacía. No se copió nada.");
          continue;
        }

        // Mapear y transformar los datos según la tabla de destino
        $dataToInsert = [];
        switch ($sourceTable) {
          case 'entidades':
            // Copia las columnas exactas de la tabla de origen
            $dataToInsert = $sourceData->map(function ($item) {
              return [
                'id' => $item->id,
                'tipoEntidad' => $item->tipoEntidad,
                'razonSocial' => $item->razonSocial,
                'titulo' => $item->titulo,
                'nombres' => $item->nombres,
                'apellidos' => $item->apellidos,
                'is_active' => $item->is_active,
                'aniversario' => $item->aniversario,
                'sexo' => $item->sexo,
                'image_path' => $item->image_path,
                'created_at' => property_exists($item, 'created_at') ? $item->created_at : null,
                'updated_at' => property_exists($item, 'updated_at') ? $item->updated_at : null,
                'deleted_at' => property_exists($item, 'deleted_at') ? $item->deleted_at : null,
              ];
            })->toArray();
            break;
          case 'telefonos':
            // Mapea la columna entidad_id
            $dataToInsert = $sourceData->map(function ($item) {
              return [
                'id' => $item->id,
                'entidad_id' => $item->entidad_id,
                'numero' => $item->numero,
                'tipo' => $item->tipo,
              ];
            })->toArray();
            break;
          case 'emails':
            // Mapea la columna entidad_id
            $dataToInsert = $sourceData->map(function ($item) {
              return [
                'id' => $item->id,
                'entidad_id' => $item->entidad_id,
                'mail' => $item->mail,
                'tipo' => $item->tipo,
              ];
            })->toArray();
            break;
          case 'direcciones':
            // Mapea la columna entidad_id y cp_id
            $dataToInsert = $sourceData->map(function ($item) {
              return [
                'id' => $item->id,
                'entidad_id' => $item->entidad_id,
                'numero' => $item->numero,
                'calle' => $item->calle,
                'cp_id' => $item->cp_id,
                'tipo' => $item->tipo,
              ];
            })->toArray();
            break;
          default:
            // Para las otras tablas, se asume que las columnas coinciden
            $dataToInsert = $sourceData->map(fn($item) => (array) $item)->toArray();
            break;
        }

        if (!empty($dataToInsert)) {
          DB::table($destTable)->insert($dataToInsert);
          $this->command->info(" -> Se copiaron " . count($dataToInsert) . " registros en la tabla '{$destTable}'.");
        }
      }
    } finally {
      Schema::enableForeignKeyConstraints();
      $this->command->info('Revisión de claves foráneas reactivada.');
    }
  }
}
