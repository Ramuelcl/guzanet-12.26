<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\backend\Categoria;
use App\Models\post\post;
use App\Models\backend\Marca;

class CategoriaSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed categories
        Categoria::factory()->count(3)->create(); // Create 3 level 1 categories
        Categoria::factory()->count(5)->level2()->create(); // Create 5 level 2 categories
        Categoria::factory()->count(10)->level3()->create(); // Create 10 level 3 categories

        // Seed brands
        // Marca::factory()->count(5)->create();

        // Seed posts
        // post::factory()->count(10)->create()->each(function (post $post) {
        //     // Assign a random category to each post
        //     $post->categoria()->associate(Categoria::inRandomOrder()->first());
        //     $post->save();

        // Assign random brands to each post
        // $post->marcas()->attach(Marca::inRandomOrder()->take(rand(1, 3))->pluck('id')->toArray());
        // });
    }
}
