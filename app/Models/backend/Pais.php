<?php
// app\Models\backend\Pais.php

namespace App\Models\backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pais extends Model
{
  use HasFactory;

  protected $table = 'paises';

  protected $fillable = [
    'nombre',
  ];

  protected $casts = [
    'id' => 'integer',
    'nombre' => 'string',
  ];

  // Relaciones
  public function ciudades(): HasMany
  {
    return $this->hasMany(Ciudad::class);
  }
}
