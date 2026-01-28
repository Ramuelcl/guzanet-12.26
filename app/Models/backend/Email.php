<?php
// app\Models\backend\Email.php

namespace App\Models\backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Email extends Model
{
  use HasFactory;

  protected $table = 'emails';
  protected $primary = 'id';

  protected $fillable = [
    'entidad_id',
    'mail',
    'tipo',
  ];

  protected $casts = [
    'id' => 'integer',
  ];

  // Relaciones
  public function entidad()
  {
    return $this->belongsTo(Entidad::class);
  }
}
