<?php
// C:\laragon\www\laravel\guzanet-12.26\routes\home.php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

// --- RUTAS PÚBLICAS (Sin login) ---
// Cualquier persona puede ver esto
Route::view('acerca-de', 'home.acerca-de')->name('acerca-de');

// Ruta para el cambio de idioma
Route::get('lang/{locale}', function ($locale) {
  if (in_array($locale, ['en', 'es', 'fr'])) {
    Session::put('locale', $locale);
  }
  return redirect()->back();
})->name('lang.switch');