<?php

namespace App\Models\backend;

use App\Models\backend\Entidad;
use App\Models\Curso;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Marcador extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'marcadores';

  /**
   * The attributes that aren't mass assignable.
   *
   * @var array
   */
  protected $guarded = [];

  protected $fillable = ["nombre", "babosa", "hexa", "rgb", "metadata", "is_active",];
  protected $hidden = ["babosa", "rgb", "metadata"];

  // Accessor for the slug attribute
  public function getSlugAttribute()
  {
    return Str::slug($this->nombre); // Generate the slug
  }
  protected $casts = [
    'id' => 'integer',
    'metadata' => 'array',
    'is_active' => 'boolean',
  ];

  /**
   * Get all of the Entidad that are assigned this Marcador.
   */
  public function entidades($active = null): MorphToMany
  {
    $query = $this->morphedByMany(Entidad::class, 'marcable'); //marcadorable

    if (!is_null($active)) {
      $query->where('is_active', $active);
    }

    return $query;
  }

  // relacion muchos a muchos
  public function cursos()
  {
    return $this->belongsToMany(Curso::class);
  }

  public function scopeActive($query)
  {
    return $query->where('is_active', 1);
  }

  public static function getActiveMarcadores()
  {
    return self::active()->pluck('nombre', 'id');
  }

  public static function getActiveColores()
  {
    return self::active()->pluck('hexa', 'id');
  }
}
