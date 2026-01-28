<?php
// app\Models\backend\Perfil.php

namespace App\Models\backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Perfil extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'perfiles';

  /**
   * The attributes that aren't mass assignable.
   *
   * @var array
   */
  protected $guarded = [];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'user_id' => 'integer',
    'edad' => 'integer',
  ];

  public function user(): HasOne
  {
    return $this->hasOne(\App\Models\User::class);
  }
  public function profesion_id($id = null)
  {
    // profesiones
    $tabla = config('constantes.PROFESIONES');
    if ($id !== null) {
      $profesion = Tabla::find()
        ->where('tabla', $tabla)
        // ->where('is_active', true)
        ->where('id', $id)
        ->limit(1)
        ->get();
      return $profesion;
    } else {
      return 'debe indicar ID de Profesión';
    }
  }
  public function profesion_rnd()
  {    // profesiones
    $tabla = config('constantes.PROFESIONES');
    $profesion = Tabla::orderByRaw('RAND()')
      ->where('tabla', $tabla)
      ->where('is_active', true)
      ->limit(1)
      ->pluck('tabla_id');
    // dd($profesion);
    return $profesion[0];
  }
}
