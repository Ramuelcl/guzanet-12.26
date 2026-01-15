// routes/login.php
<?php

use Illuminate\Support\Facades\Route;

// Ejemplo: Una ruta para logueo de invitados o logs de acceso
Route::middleware('guest')->group(function () {
  // Tu lógica personalizada aquí
});
