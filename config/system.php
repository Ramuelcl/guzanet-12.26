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
        'logo_path' => 'app/images/logo/Guzanet.png', // Futura ruta: 'img/guzanet-logo.png',
        'version' => "v1.2.0",
    ],

    'client' => [
        'name' => null,
        'logo_path' => null, // Futura ruta: 'img/client-logo.png'
    ],

    'display' => [
        'center_title' => 'Guzanet', // Este es el que se muestra al centro
        'show_client_logo' => true,
        // Control de bloques del footer (salvo el bloque 1 que es obligatorio)
        'footer_blocks' => [
            'location' => true, // Bloque 2
            'user'     => true, // Bloque 3
            'language' => true, // Bloque 4
            'clock'    => true, // Bloque 5
        ],
    ],
];
