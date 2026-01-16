<?php
// database/factories/backend/EntidadFactory.php

namespace Database\Factories\backend;

use App\Models\backend\Entidad;
use App\Models\backend\Tabla;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EntidadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Entidad::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Obtener un usuario aleatorio
        $user = User::inRandomOrder()->first() ?? User::factory()->create();

        // Obtener un tipo de entidad aleatorio (ej. Cliente, Vendedor, etc.)
        // Excluimos el ID 1 ('Perfil') para manejarlo de forma especial si es necesario
        $tipoEntidad = Tabla::where('tabla', config('constantes.TIPO_ENTIDAD'))
            ->where('is_active', true)
            ->where('tabla_id', '!=', 1) // Excluir 'Perfil'
            ->inRandomOrder()
            ->first();

        return [
            'user_id' => $user->id,
            'entidad_tipo_id' => $tipoEntidad->tabla_id,
            'nombre' => $user->name . ' (' . $tipoEntidad->valores['nombre'] . ')',
            'identificacion' => $this->faker->unique()->numerify('#########L'),
            'is_active' => $this->faker->boolean(90), // 90% de probabilidad de ser activo
        ];
    }
}