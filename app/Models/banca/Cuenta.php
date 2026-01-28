<?php
// app/Models/banca/Cuenta.php

namespace App\Models\banca;

use App\Models\backend\Entidad;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cuenta extends Model {
    use HasFactory;

    protected $table = 'cuentas';

    // app/Models/banca/Cuenta.php
    protected $fillable = [
        'numero_cuenta',
        'saldo_anterior',    // Añadir si existe
        'saldo_actual',      // Añadir si existe
        'tipo',
        'iban',
        'bic',
        'banco_entidad_id',
        'cliente_entidad_id',
        'banco_nombre',
        'cliente_nombre',
        'cliente_id',
    ];

    // Relaciones
    public function banco(): BelongsTo {
        return $this->belongsTo(Entidad::class, 'banco_entidad_id');
    }

    public function cliente(): BelongsTo {
        return $this->belongsTo(Entidad::class, 'cliente_entidad_id');
    }

    public function operaciones() {
        return $this->hasMany(CuentaOperacion::class);
    }
}
