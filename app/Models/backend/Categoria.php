<?php

namespace App\Models\backend;

use App\Models\backend\Entidad;
use App\Models\Curso;
// use App\Models\backend\Categorizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // Import the Str class

class Categoria extends Model
{
  use HasFactory;

  protected $fillable = ["nombre"];
  protected $hidden = ["slug"];
  protected $table = 'categorias';

  public function entidades()
  {
    return $this->hasMany(Entidad::class);
    // return $this->morphedByMany(Entidad::class, 'categoriable');
  }

  public function cursos()
  {
    return $this->hasMany(Curso::class);
  }

  // Accessor for the slug attribute
  public function getSlugAttribute()
  {
    return Str::slug($this->nombre); // Generate the slug
  }

  public function scopeActive($query)
  {
    return $query->where('is_active', 1);
  }

  public static function getActiveCategories()
  {
    return self::active()->pluck('nombre', 'id');
  }
}
