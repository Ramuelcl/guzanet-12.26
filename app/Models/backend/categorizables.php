<?php

namespace App\Models\backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\backend\Categoria;

class categoriables extends Model
{
    use HasFactory;

    // Define the relationship with the Category model
    public function categoriables()
    {
        return $this->belongsTo(Categoria::class);
    }
}
