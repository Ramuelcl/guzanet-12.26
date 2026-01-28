<?php
// database/factories/TicketFactory.php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory {
  public function definition(): array {
    $isGuest = $this->faker->boolean(30);

    return [
      'customer_id' => $isGuest ? null : User::factory(),
      'user_id' => User::factory(), // El agente asignado
      'session_id' => $isGuest ? $this->faker->uuid() : null,
      'subject' => $this->faker->sentence(),
      'status' => $this->faker->randomElement(['open', 'pending', 'closed']),
    ];
  }
}
