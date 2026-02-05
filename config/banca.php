<?php
// C:\laragon\www\laravel\guzanet-12.26\config\banca.php

return [
  'pdf' => [
    'banco1' => [
      // PROACTIVO: Buscamos el nombre que sigue a "Courrier :" para identificar al banco dinámicamente
      'nombre_banco' => '/Courrier\s*:\s*([A-Z\s]+)(?=\d)/i',

      'releve_numero' => '/Relevé de vos comptes\s*-\s*n°\s*(\d+)/i',
      'releve_fecha'  => '/Relevé édité le (\d{1,2})\s+(janvier|février|mars|avril|mai|juin|juillet|août|septembre|octobre|novembre|décembre)\s+(\d{4})/i',

      'titular' => [
        'id_cliente' => '/identifiant\s*:\s*(\d+)/i',
        'nombre'     => '/^(?:MR|MME|M\.)\s+([A-Z\s\-]+)/im',
        // PROACTIVO: Captura la dirección deteniéndose ANTES de "Situation de vos comptes"
        'direccionCliente' => '/(?:MR|MME|M\.)\s+[A-Z\s\-]+\s*\n([\s\S]*?)(?=Situation de vos comptes)/i',
      ],

      'direccion_parseada' => [
        // Captura todo hasta encontrar el código postal de 5 dígitos
        'numero_y_calle' => '/^(.*?)(?=\s*\d{5})/is',
        'codigo_postal'  => '/\b(\d{5})\b/',
        'ciudad'         => '/\d{5}\s+([A-ZÀ-ÿ\s]+?)(?=\n|\r|$)/i',

        'limpiar_patrones' => [
          '/Situation de vos comptes.*$/is',
          '/Solde.*$/is',
          '/\d{2}\s+janvier.*$/i'
        ]
      ],

      'cuentas' => [
        'cuenta1' => [
          'regex' => '/CCP\s*n°?\s*([\d\s\w]+?)(?:\s+([+-]?[\d\s\.]*,\d{2}))/i',
          'label' => 'CCP'
        ],
        'cuenta2' => [
          'regex' => '/Livret\s*A\s*n°?\s*([\d\s\w]+?)(?:\s+([+-]?[\d\s\.]*,\d{2}))/i',
          'label' => 'LIVRET A'
        ],
      ],

      'detalles' => [
        'iban' => '/IBAN\s*:\s*([A-Z0-9\s]{15,})/i',
        'bic'  => '/BIC\s*:\s*([A-Z0-9]{8,11})/i',
      ],

      'operaciones' => [
        'inicio' => '/Ancien solde au([A-Z0-9\s]{15,})/i',
        'fin'    => '/Nouveau solde au([A-Z0-9\s]{15,})/i',
        'regex_fila' => '/^(\d{2}\/\d{2})\s+(.*?)\s+([+-]?[\d\s\.]*,\d{2})$/m',
      ],

    ]
  ]
];
