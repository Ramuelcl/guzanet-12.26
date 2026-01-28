<?php
// app\Models\backend\Entidad.php

namespace App\Models\backend;

use App\Models\backend\Direccion;
use App\Models\backend\Email;
use App\Models\backend\Telefono;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Entidad extends Model
{

  use HasFactory;

  // Nombre de la tabla asociada
  protected $table = 'entidades';

  // Atributos que se pueden asignar en masa
  protected $fillable = [
    'tipoEntidad',
    'razonSocial',
    'website',
    'titulo',
    'nombres',
    'apellidos',
    'is_active',
    'aniversario',
    'sexo',
    'image_path'
  ];

  protected $casts = [
    'id' => 'integer',
    'tipoEntidad' => 'integer',
    'razonSocial' => 'string',
    'website' => 'string',
    'titulo' => 'string',
    'nombres' => 'string',
    'apellidos' => 'string',
    'is_active' => 'boolean',
    'aniversario' => 'date',
    'sexo' => 'integer',
    'categoria_id' => 'integer',
    'image_path' => 'string',
  ];

  // Relaciones
  public function telefonos(): HasMany
  {
    return $this->hasMany(Telefono::class);
  }

  public function emails(): HasMany
  {
    return $this->hasMany(Email::class);
  }

  public function direcciones(): HasMany
  {
    return $this->hasMany(Direccion::class);
  }

  // Mutator para el nombre completo
  public function getNombreCompletoAttribute()
  {
    return "{$this->nombres} {$this->apellidos}";
  }

  public function listaTipos($tipo=11050)
  {
    $datos = Tabla::where('tabla', '=', $tipo)
      ->where('is_active', '=', true)
      ->select('tabla_id as id', \DB::raw("JSON_UNQUOTE(JSON_EXTRACT(valores, '$.nombre')) as nombre"))
      ->get()
      ->pluck('nombre', 'id') // Pluck the 'nombre' value with 'id' as the key
      ->toArray();

    // dd($datos);
    return $datos;
  }

  /**
   * get Categoria for Entidad
   */
  public function xCategoria()
  {
    return $this->belongsTo(Categoria::class);
  }

  /**
  * Get all of the Marcador for the Entidad.
  */
  public function xMarcadores(): MorphToMany
  {
      return $this->morphToMany(Marcador::class, 'marcable');
  }
}
