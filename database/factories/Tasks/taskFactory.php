<?php

namespace Database\Factories\Tasks;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\tasks\task>
 */
class taskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            'status' => $this->faker->randomElement(['pending', 'progress', 'completed']),
            'project_id' => \App\Models\tasks\Project::inRandomOrder()->first()->id?? null,
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'is_active' => $this->faker->boolean(80),
            'user_id' => \App\Models\User::inRandomOrder()->first()->id,
        ];
    }
}
