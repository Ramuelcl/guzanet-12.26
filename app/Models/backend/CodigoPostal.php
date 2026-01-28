<?php

namespace App\Models\backend;

use App\Models\backend\Ciudad;
use App\Models\backend\Direccion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CodigoPostal extends Model
{
  use HasFactory;
  protected $table = 'codigospostales';

  protected $fillable = [
    'cp',
    'ciudad_id',
  ];

  protected $casts = [
    'id' => 'integer',
    'cp' => 'integer',
    'ciudad_id' => 'integer',
  ];

  // Relaciones
  public function ciudad(): BelongsTo
  {
    return $this->belongsTo(Ciudad::class);
  }

  public function direcciones(): HasMany
  {
    return $this->hasMany(Direccion::class, 'cp_id');
  }
}
