<?php
// app\Models\backend\Telefono.php

namespace App\Models\backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Telefono extends Model
{
  use HasFactory;

  protected $table = 'telefonos';
  protected $primary = 'id';

  protected $fillable = [
    'entidad_id',
    'numero',
    'tipo',
  ];
  protected $casts = [
    'id' => 'integer',
    'entidad_id' => 'integer',
    'numero' => 'integer',
    'tipo' => 'integer'
  ];
  // Relaciones
  public function entidad(): BelongsTo
  {
    return $this->belongsTo(Entidad::class);
  }
}
