<?php
// app/Models/TicketMessage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMessage extends Model {
  use HasFactory;

  protected $fillable = ['ticket_id', 'user_id', 'session_id', 'body'];

  public function ticket(): BelongsTo {
    return $this->belongsTo(Ticket::class);
  }

  public function user(): BelongsTo {
    return $this->belongsTo(User::class);
  }
}
