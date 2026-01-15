<?php
// C:\laragon\www\laravel\guzanet-12.26\routes\dashboard.php

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');
});