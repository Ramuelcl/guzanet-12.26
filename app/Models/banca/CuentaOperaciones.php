<?php
// C:\laragon\www\laravel\guzanet-12.26\app\Models\banca\CuentaOperaciones.php

namespace App\Models\banca;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuentaOperaciones extends Model {
    protected $table = 'cuenta_operaciones';

    protected $fillable = [
        'cuenta_id',
        'fecha_operacion',
        'descripcion_operacion',
        'debito',
        'credito',
        'valor_francos',
        'hash_operacion',
        'releve_numero', // <-- NUEVO CAMPO
    ];

    /**
     * Casting de atributos.
     */
    protected $casts = [
        'fecha_operacion' => 'date',
        'debito' => 'decimal:2',
        'credito' => 'decimal:2',
        'valor_francos' => 'decimal:2',
    ];

    /**
     * Obtener la cuenta a la que pertenece la operación.
     */
    public function cuenta(): BelongsTo {
        return $this->belongsTo(Cuenta::class, 'cuenta_id');
    }
}
