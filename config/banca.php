<?php
// C:\laragon\www\laravel\guzanet-12.26\config\banca.php

return [
  'pdf' => [
    'banca_nombre' => '/Courrier\s*:\s*([A-Z\s\-]+)/i',
    'titular' => [
      'identificador' => '/Votre identifiant\s*:\s*(\d+)/i',
      'titular'       => '/(MR|MME|M\.)\s+([A-Z\s\-]+)/i',
      'direccion'     => '/\d{5}\s+[A-Z\s\-]+/', // Usado para separar el bloque de dirección
      'cp'            => '/(\d{5})/',
      'ciudad'        => '/\d{5}\s+([A-Z\s\-]+)/i',
      'ccp_header'    => '/CCP n°\s*([\d\s\w]+?)\s+([\d\s]+,\d{2})/i',
      'livret_header' => '/Livret A n°\s*([\d\s\w]+?)\s+([\d\s]+,\d{2})/i',
    ],
    'cuentas' => [
      'cuenta1'      => '/CCP n°\s*([\w\s]+?)\s+([\d\s]+,\d{2})/i',
      'cuenta2' => '/Livret A n°\s*([\w\s]+?)\s+([\d\s]+,\d{2})/i',
      'cuenta3' => '/Livret B n°\s*([\w\s]+?)\s+([\d\s]+,\d{2})/i',
      'iban'     => '/IBAN\s*:\s*([A-Z0-9\s]+)/i',
      'bic'      => '/BIC\s*:\s*([A-Z0-9]+)/i',
    ],
    'delimitadores' => [
      'inicio' => 'Ancien solde au',
      'fin'    => 'Nouveau solde au',
    ]
  ]
];
