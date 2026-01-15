// routes/register.php
<?php

use Illuminate\Support\Facades\Route;

// Aquí podrías definir lógica para registros por invitación o tipos de roles
Route::get('/register-invite/{token}', function ($token) {
  return "Procesando invitación: " . $token;
})->name('register.invite');
