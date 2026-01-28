<?php
// database/seeders/TicketSeeder.php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder {
  public function run(): void {
    Ticket::factory()
      ->count(10)
      ->has(TicketMessage::factory()->count(3), 'messages')
      ->create();
  }
}
