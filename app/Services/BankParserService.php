<?php
// C:\laragon\www\laravel\guzanet-12.26\app\Services\BankParserService.php

namespace App\Services;

class BankParserService {
  public function parseBancas(string $text) {
    $pdfConfig = config('banca.pdf');
    $cuentasConf = $pdfConfig['cuentas'];

    $pdfExtract = [
      'cliente' => $this->extraerDatosCliente($text, $pdfConfig)
    ];

    // --- DETECCIÓN DINÁMICA DE CUENTAS ---
    foreach ($cuentasConf as $key => $pattern) {
      // Ignoramos llaves que no son identificadores de cuenta
      if (in_array($key, ['iban', 'bic'])) continue;

      // Usamos PREG_OFFSET_CAPTURE para verificar si el patrón existe en el texto
      if (preg_match($pattern, $text, $match)) {

        // Mapeamos el nombre descriptivo según la clave de tu config
        $tipoNombre = match ($key) {
          'cuenta1' => 'COMPTE COURANT POSTAL',
          'cuenta2' => 'LIVRET A',
          'cuenta3' => 'LIVRET B',
          default   => 'AUTRE COMPTE'
        };

        // Agregamos la cuenta al array final
        $pdfExtract[$key] = [
          'detalle' => $this->mapearDetalleCuenta($tipoNombre, $match, $text, $cuentasConf),
          'operaciones' => []
        ];
      }
    }

    // Si se detectaron cuentas, procesamos las operaciones
    return $this->procesarBloquesOperaciones($text, $pdfExtract, $pdfConfig['delimitadores']);
  }

  private function mapearDetalleCuenta($nombreLargo, $matches, $text, $conf) {
    // Buscamos IBAN y BIC globales
    preg_match($conf['iban'], $text, $ibanM);
    preg_match($conf['bic'], $text, $bicM);

    // Limpieza de saldos: eliminamos espacios y convertimos coma en punto
    $rawSaldo = str_replace(' ', '', $matches[2]);
    $valSaldo = (float)str_replace(',', '.', $rawSaldo);

    return [
      'tipo' => $nombreLargo,
      'numero' => str_replace(' ', '', $matches[1]),
      'saldo' => $valSaldo,
      'signo' => ($valSaldo >= 0) ? '+' : '-',
      'iban' => $ibanM[1] ?? 'N/A',
      'bic' => $bicM[1] ?? 'N/A'
    ];
  }

  private function extraerDatosCliente($text, $pdfConfig) {
    $titConf = $pdfConfig['titular'];
    $lineas = explode("\n", $text);
    $cliente = ['id' => 'N/A', 'nombre' => 'N/A', 'direccion' => 'N/A', 'cp' => 'N/A', 'ciudad' => 'N/A', 'banco' => 'N/A'];

    if (preg_match($pdfConfig['banca_nombre'], $text, $bM)) $cliente['banco'] = trim($bM[1]);
    if (preg_match($titConf['identificador'], $text, $idM)) $cliente['id'] = $idM[1];

    foreach ($lineas as $i => $linea) {
      if (preg_match($titConf['titular'], $linea, $titM)) {
        $cliente['nombre'] = trim($titM[2]);
        $cliente['direccion'] = trim($lineas[$i + 1] ?? 'N/A');

        // Búsqueda de CP y Ciudad en proximidad
        for ($j = $i + 1; $j <= $i + 4; $j++) {
          if (isset($lineas[$j])) {
            if (preg_match($titConf['cp'], $lineas[$j], $cpM)) $cliente['cp'] = $cpM[1];
            if (preg_match($titConf['ciudad'], $lineas[$j], $cityM)) $cliente['ciudad'] = trim($cityM[1]);
          }
        }
        break;
      }
    }
    return $cliente;
  }

  private function procesarBloquesOperaciones($text, $pdfExtract, $delims) {
    // Separamos el PDF en bloques basados en los nombres de las cuentas
    $bloques = preg_split('/(Compte Courant Postal|Livret A)/i', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

    $contexto = '';
    foreach ($bloques as $bloque) {
      $clean = strtoupper(trim($bloque));
      if (str_contains($clean, 'COMPTE COURANT POSTAL')) {
        $contexto = 'cuenta1';
        continue;
      }
      if (str_contains($clean, 'LIVRET A')) {
        $contexto = 'cuenta2';
        continue;
      }

      if ($contexto && isset($pdfExtract[$contexto])) {
        $pdfExtract[$contexto]['operaciones'] = $this->extraerLineas($bloque, $delims);
      }
    }
    return $pdfExtract;
  }

  private function extraerLineas($textoBloque, $delims) {
    $lineas = explode("\n", $textoBloque);
    $ops = [];
    $temp = null;
    $capturar = false;

    foreach ($lineas as $l) {
      $l = trim($l);
      if (str_contains($l, $delims['inicio'])) {
        $capturar = true;
        continue;
      }
      if (str_contains($l, $delims['fin'])) {
        $capturar = false;
        break;
      }

      if ($capturar && strlen($l) > 5) {
        if (preg_match('/^(\d{2}\/\d{2})\s+(.+)/', $l, $m)) {
          if ($temp) $ops[] = $temp;
          $temp = ['fecha' => $m[1], 'desc' => $m[2], 'monto' => 0, 'signo' => '+', 'frf' => 0];
        } elseif ($temp && preg_match('/([\d\s]+,\d{2})\s+([+-])\s+([\d\s]+,\d{2})$/', $l, $mon)) {
          $temp['monto'] = (float)str_replace([' ', ','], ['', '.'], $mon[1]);
          $temp['signo'] = $mon[2];
          $temp['frf'] = (float)str_replace([' ', ','], ['', '.'], $mon[3]);
        } elseif ($temp) {
          $temp['desc'] .= ' ' . $l;
        }
      }
    }
    if ($temp) $ops[] = $temp;
    return $ops;
  }
}
