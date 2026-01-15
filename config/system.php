<?php
// C:\laragon\www\laravel\guzanet-12.26\config\system.php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Identidad Visual
    |--------------------------------------------------------------------------
    */
    'owner' => [
        'name' => 'Guzanet',
        'short' => 'G',
        'logo_path' => 'app/images/logo/Guzanet.png', // Futura ruta: 'img/guzanet-logo.png'
    ],

    'client' => [
        'name' => 'Empresa Cliente S.A.',
        'short' => 'CLI',
        'logo_path' => null, // Futura ruta: 'img/client-logo.png'
    ],

    'display' => [
        'center_title' => 'GUZANET', // Este es el que se muestra al centro
        'show_client_logo' => true,
    ],
];