<?php
// C:\laragon\www\laravel\guzanet-12.26\config\banca.php

return [
  'pdf' => [
    'banco1' => [
      'nombre_banco' => '/Courrier\s*:\s*([A-Z0-9\s]{15,}?)(?:\r?\n|$)/i',
      'titular' => [
        'id_cliente' => '/identifiant\s*:\s*(\d+)/i',
        'nombre'     => '/(MR|MME|M\.)\s+([A-Z\s\-]+)/i',
      ],
      'cuentas' => [
        'cuenta1' => ['regex' => '/(?:CCP|Compte\s*Courant\s*Postal)\s*n[°º]?\s*([\d\s\w]+?)\s+([+-]?[\d\s\.]*,\d{2})/i', 'label' => 'CCP'],
        'cuenta2' => ['regex' => '/(?:Livret\s*A)\s*n[°º]?\s*([\d\s\w]+?)\s+([+-]?[\d\s\.]*,\d{2})/i', 'label' => 'LIVRET A'],
      ],
      'detalles' => [
        'iban' => '/IBAN\s*:\s*([A-Z0-9\s]{15,})/i',
        'bic'  => '/BIC\s*:\s*([A-Z0-9]{8,11})/i',
      ],
      'delimitadores' => [
        'inicio' => 'Ancien solde au',
        'fin'    => 'Nouveau solde au',
      ]
    ],
    // 
    'banco2' => [ // Ejemplo de segundo banco
      'nombre_banco'  => '/Courrier\s*:\s*([A-Z0-9\s]{15,})/i',
      'titular' => [
        'id_cliente' => '/identifiant\s*:\s*(\d+)/i',
        'nombre'     => '/(MR|MME|M\.)\s+([A-Z\s\-]+)/i',
      ],
      'cuentas' => [
        'cuenta1' => ['regex' => '/(?:CCP|Compte\s*Courant\s*Postal)\s*n[°º]?\s*([\d\s\w]+?)\s+([+-]?[\d\s\.]*,\d{2})/i', 'label' => 'CCP'],
        'cuenta2' => ['regex' => '/(?:Livret\s*A)\s*n[°º]?\s*([\d\s\w]+?)\s+([+-]?[\d\s\.]*,\d{2})/i', 'label' => 'LIVRET A'],
      ],
      'detalles' => [
        'iban' => '/IBAN\s*:\s*([A-Z0-9\s]{15,})/i',
        'bic'  => '/BIC\s*:\s*([A-Z0-9]{8,11})/i',
      ],
      'delimitadores' => [
        'inicio' => 'Ancien solde au',
        'fin'    => 'Nouveau solde au',
      ]
    ],

  ]
];
