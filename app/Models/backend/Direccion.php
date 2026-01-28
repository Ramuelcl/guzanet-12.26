<?php
// app\Models\backend\Direccion.php

namespace App\Models\backend;

use App\Models\backend\CodigoPostal;
use App\Models\backend\Entidad;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Direccion extends Model
{
  use HasFactory;

  protected $table = 'direcciones';

  protected $fillable = [
    'entidad_id',
    'numero',
    'calle',
    'cp_id',
    'tipo',
  ];

  // Relaciones
  public function entidad(): BelongsTo
  {
    return $this->belongsTo(Entidad::class);
  }

  public function codigoPostal(): BelongsTo
  {
    return $this->belongsTo(CodigoPostal::class, 'cp_id');
  }
}
