<?php
// app\Models\backend\Ciudad.php


namespace App\Models\backend;

use App\Models\backend\CodigoPostal;
use App\Models\backend\Pais;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ciudad extends Model
{
  use HasFactory;

  protected $table = 'ciudades';

  protected $fillable = [
    'nombre',
    'region',
    'pais_id',
  ];

  protected $casts = [
    'id' => 'integer',
    'nombre' => 'string',
    'region' => 'string',
    'pais_id' => 'integer',
  ];

  // Relaciones
  public function pais(): BelongsTo
  {
    return $this->belongsTo(Pais::class);
  }

  public function codigosPostales(): HasMany
  {
    return $this->hasMany(CodigoPostal::class);
  }
}
