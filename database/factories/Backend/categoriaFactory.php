<?php

namespace Database\Factories\Backend;

use App\Models\backend\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str; // Import the Str class

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\backend\Categoria>
 **/
class CategoriaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombre = $this->faker->unique()->words($nb = 2, $asText = true);
        $nombre = ucwords($nombre); // Capitalize the first character of each word

        $slug = Str::slug($nombre);

        return [
            'nombre' => $nombre,
            'slug' => $slug,
            'parent_id' => null, // Nivel 1
        ];
    }

    public function level2()
    {
        $parent = Categoria::whereNull('parent_id')->inRandomOrder()->first();
        return $this->state(function (array $attributes) use ($parent) {
            return [
                'parent_id' => $parent->id, // Nivel 2
            ];
        });
    }

    public function level3()
    {
        $parent = Categoria::whereNotNull('parent_id')->inRandomOrder()->first();
        return $this->state(function (array $attributes) use ($parent) {
            return [
                'parent_id' => $parent->id, // Nivel 3
            ];
        });
    }
}
