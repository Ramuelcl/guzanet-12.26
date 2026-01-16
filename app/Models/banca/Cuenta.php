<?php
// C:\laragon\www\laravel\guzanet-12.26\app\Models\banca\Cuenta.php

namespace App\Models\banca;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cuenta extends Model {
    // Definimos la tabla manualmente como en tu migración
    protected $table = 'cuentas';

    protected $fillable = [
        'banco_nombre',
        'cliente_id',
        'nombre_cliente',
        'direccion_cliente',
        'fecha_reporte',
        'numero_cuenta',
        'tipo_cuenta',
        'iban',
        'bic',
        'saldo_anterior',
        'saldo_actual',
    ];

    /**
     * Obtener las operaciones asociadas a esta cuenta.
     */
    public function operaciones(): HasMany {
        return $this->hasMany(Operacion::class, 'cuenta_id');
    }
}
