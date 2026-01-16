<?php
// C:\laragon\www\laravel\guzanet-12.26\routes\banking.php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Esta ruta cargará el componente principal que tiene el menú lateral
// URL: /banking
Volt::route('/', 'pages.banking.index')
  ->name('banking.index');
