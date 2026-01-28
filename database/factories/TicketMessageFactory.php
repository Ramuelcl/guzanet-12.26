<?php
// database/factories/TicketMessageFactory.php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketMessageFactory extends Factory {
  public function definition(): array {
    return [
      'ticket_id' => Ticket::factory(),
      'user_id' => User::factory(),
      'session_id' => null,
      'body' => $this->faker->paragraph(),
    ];
  }
}
