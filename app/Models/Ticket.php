<?php
// app/Models/Ticket.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model {
  use HasFactory;

  protected $fillable = ['customer_id', 'user_id', 'session_id', 'subject', 'status'];

  public function messages(): HasMany {
    return $this->hasMany(TicketMessage::class);
  }

  public function customer(): BelongsTo {
    return $this->belongsTo(User::class, 'customer_id');
  }

  public function agent(): BelongsTo {
    return $this->belongsTo(User::class, 'user_id');
  }
}
