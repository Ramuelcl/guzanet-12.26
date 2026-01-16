<?php

namespace Database\Factories\Backend;

use App\Models\backend\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MenuFactory extends Factory {
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Menu::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        // Para el ID no auto-incrementable, necesitamos una forma de generar IDs únicos.
        // Usaremos un número aleatorio grande y único para demostración.
        // En un seeder real, podrías querer controlar esto más explícitamente.
        static $id_counter = 1000; // Empezar desde un número alto para evitar colisiones con IDs manuales

        return [
            //  'id' => $id_counter++, // Asignar y luego incrementar para el siguiente
            'name' => Str::limit($this->faker->unique()->words(2, true), 15), // Límite de 16, usamos 15 para estar seguros
            'url' => $this->faker->optional(0.7)->slug(2), // 70% de las veces tendrá una URL
            'icon' => $this->faker->optional(0.8)->word(), // 80% de las veces tendrá un icono, limitado a 16 por la tabla
            'parent_id' => null, // Por defecto, es un menú de nivel superior
            'order' => $this->faker->numberBetween(1, 100),
            'type' => $this->faker->randomElement(['module', 'group', 'setting', 'action', 'utility', 'page', 'link', 'sys', 'users']), // Usar los tipos que definiste
            'is_active' => $this->faker->boolean(90), // 90% de las veces será activo
        ];
    }

    /**
     * Indicate that the menu item has a parent.
     *
     * @param int|null $parentId
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withParent(int $parentId = null): Factory {
        return $this->state(function (array $attributes) use ($parentId) {
            return [
                // Si no se provee un parentId, intenta obtener uno aleatorio de los menús existentes
                // o simplemente déjalo como null si no hay menús (o si es el primer menú creado).
                // Para simplicidad, si no se pasa, se podría crear un nuevo menú padre o dejarlo null.
                // Aquí asumimos que el parentId se pasará o se manejará en el seeder.
                'parent_id' => $parentId ?? (Menu::inRandomOrder()->first()?->id),
            ];
        });
    }

    /**
     * Indicate that the menu item is a specific type.
     *
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function type(string $type): Factory {
        return $this->state(function (array $attributes) use ($type) {
            return [
                'type' => Str::limit($type, 16),
            ];
        });
    }

    /**
     * Indicate that the menu item is active.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function active(): Factory {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }

    /**
     * Indicate that the menu item is inactive.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function inactive(): Factory {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}
