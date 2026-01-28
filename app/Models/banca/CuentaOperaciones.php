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
        'hash_operacion',
        'valor_francos',
    ];

    /**
     * Casting de atributos.
     * Al usar 'date:Ymd', Laravel formatea automáticamente la fecha 
     * como AAAAMMDD al serializar (JSON) y permite usar Carbon.
     */
    protected $casts = [
        'fecha_operacion' => 'date',
        'debito' => 'decimal:2',
        'credito' => 'decimal:2',
        'valor_francos' => 'decimal:2',
    ];

    // // Obligamos a que la fecha siempre se maneje como un string AAAAMMDD al guardar
    // protected function fechaOperacion(): Attribute {
    //     return Attribute::make(
    //         set: fn(string $value) => str_replace('-', '', $value), // De 2026-01-15 a 20260115
    //         get: fn(string $value) => $value, // Se recupera como 20260115
    //     );
    // }
    /**
     * Obtener la cuenta a la que pertenece la operación.
     */
    public function cuenta(): BelongsTo {
        return $this->belongsTo(Cuenta::class, 'cuenta_id');
    }
}
