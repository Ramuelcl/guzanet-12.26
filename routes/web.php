<?php
// C:\laragon\www\laravel\guzanet-12.26\routes\web.php

use Illuminate\Support\Facades\Route;

// Ambas rutas cargan la misma estética de "Sistema"
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Rutas administrativas
    Route::view('admin/settings', 'admin.settings')
        ->middleware('role:admin')
        ->name('admin.settings');
});

Route::view('acerca-de', 'home.acerca-de')->name('acerca-de');

require __DIR__ . '/auth.php';