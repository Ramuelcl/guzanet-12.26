<?php
// C:\laragon\www\laravel\guzanet-12.26\app\Services\BankParserService.php

namespace App\Services;

class BankParserService {
  public function parseBancas(string $text) {
    $text = str_replace("\t", " ", $text);
    $pdfConfig = config('banca.pdf');
    $lineas = explode("\n", $text);

    $pdfExtract = [
      'cliente' => $this->extraerDatosCliente($text, $pdfConfig)
    ];

    $cuentaActual = null;
    $capturarOps = false;
    $tempOp = null; // Para acumular la operación actual

    foreach ($lineas as $index => $l) {
      $l = trim($l);
      if (empty($l) || $l === "pausa") continue;

      // 1. IDENTIFICACIÓN DE CUENTA (CABECERA O SALDO)
      foreach ($pdfConfig['cuentas'] as $key => $pattern) {
        if (in_array($key, ['iban', 'bic'])) continue;

        if (preg_match($pattern, $l, $m)) {
          // Si ya teníamos una operación pendiente de la cuenta anterior, la guardamos
          if ($tempOp && $cuentaActual) {
            $pdfExtract[$cuentaActual]['operaciones'][] = $tempOp;
            $tempOp = null;
          }

          $cuentaActual = $key;
          $capturarOps = false;

          if (!isset($pdfExtract[$cuentaActual])) {
            $pdfExtract[$cuentaActual] = [
              'detalle' => [
                'tipo' => strtoupper($key),
                'numero' => str_replace(' ', '', $m[1]),
                'saldo' => (float)str_replace([' ', ','], ['', '.'], $m[2]),
                'signo' => (str_contains($m[2], '-')) ? '-' : '+',
                'iban' => 'N/A',
                'bic' => 'N/A'
              ],
              'operaciones' => []
            ];
          } else {
            $pdfExtract[$cuentaActual]['detalle']['saldo'] = (float)str_replace([' ', ','], ['', '.'], $m[2]);
          }
          continue 2;
        }
      }

      // 2. BÚSQUEDA DE NOMBRE LARGO PARA IBAN/BIC
      if (preg_match('/(Compte Courant Postal|Livret A|Livret B)\s+n°\s*([\w\s]+)/i', $l, $mLargo)) {
        $cuentaActual = $this->mapearNombreAClave($mLargo[1]);
        if (!isset($pdfExtract[$cuentaActual])) {
          $pdfExtract[$cuentaActual] = [
            'detalle' => ['tipo' => $mLargo[1], 'numero' => str_replace(' ', '', $mLargo[2]), 'saldo' => 0, 'signo' => '+', 'iban' => 'N/A', 'bic' => 'N/A'],
            'operaciones' => []
          ];
        }

        for ($i = 1; $i <= 3; $i++) {
          if (isset($lineas[$index + $i])) {
            $subL = trim($lineas[$index + $i]);
            if (preg_match($pdfConfig['cuentas']['iban'], $subL, $ibanM)) $pdfExtract[$cuentaActual]['detalle']['iban'] = str_replace(' ', '', $ibanM[1]);
            if (preg_match($pdfConfig['cuentas']['bic'], $subL, $bicM)) $pdfExtract[$cuentaActual]['detalle']['bic'] = trim($bicM[1]);
          }
        }
        continue;
      }

      // 3. PROCESAMIENTO DE OPERACIONES (MÁQUINA DE ESTADOS)
      if ($cuentaActual && isset($pdfExtract[$cuentaActual])) {
        $delims = $pdfConfig['delimitadores'];

        // Control de entrada/salida de la zona de movimientos
        if (str_contains($l, $delims['inicio'])) {
          $capturarOps = true;
          continue;
        }
        if (str_contains($l, $delims['fin']) || str_contains($l, "Nouveau solde au")) {
          if ($tempOp) {
            $pdfExtract[$cuentaActual]['operaciones'][] = $tempOp;
            $tempOp = null;
          }
          $capturarOps = false;
          continue;
        }

        if ($capturarOps) {
          // Detectar nueva operación por fecha (dd/mm)
          // Buscamos fecha al inicio y capturamos el resto de la línea
          if (preg_match('/^(\d{2}\/\d{2})\s+(.+)$/', $l, $mOp)) {
            // Si ya había una operación, la guardamos antes de empezar la nueva
            if ($tempOp) {
              $pdfExtract[$cuentaActual]['operaciones'][] = $tempOp;
            }

            $fecha = $mOp[1];
            $restoLinea = $mOp[2];

            // Intentamos extraer montos y saldo de esta misma línea
            // Patrón: Monto (con coma) + Espacio + Signo (+/-) + Espacio + Saldo (con coma)
            $monto = 0;
            $signo = '+';
            $saldo_linea = 0;
            if (preg_match('/([\d\s]+,\d{2})\s+([+-])\s+([\d\s]+,\d{2})$/', $restoLinea, $mValores)) {
              $monto = (float)str_replace([' ', ','], ['', '.'], $mValores[1]);
              $signo = $mValores[2];
              $saldo_linea = (float)str_replace([' ', ','], ['', '.'], $mValores[3]);
              // Limpiamos la descripción quitando los montos
              $descripcion = trim(str_replace($mValores[0], '', $restoLinea));
            } else {
              $descripcion = trim($restoLinea);
            }

            $tempOp = [
              'fecha' => $fecha,
              'descripcion' => $descripcion,
              'monto' => $monto,
              'signo' => $signo,
              'saldo_mov' => $saldo_linea
            ];
          } elseif ($tempOp) {
            // Si no hay fecha, es una línea de descripción excedente
            // Pero cuidado: a veces el monto viene en la segunda línea
            if (preg_match('/([\d\s]+,\d{2})\s+([+-])\s+([\d\s]+,\d{2})$/', $l, $mValores)) {
              $tempOp['monto'] = (float)str_replace([' ', ','], ['', '.'], $mValores[1]);
              $tempOp['signo'] = $mValores[2];
              $tempOp['saldo_mov'] = (float)str_replace([' ', ','], ['', '.'], $mValores[3]);
              $extraDesc = trim(str_replace($mValores[0], '', $l));
              if (!empty($extraDesc)) $tempOp['descripcion'] .= " " . $extraDesc;
            } else {
              $tempOp['descripcion'] .= " " . $l;
            }
          }
        }
      }
    }

    return $pdfExtract;
  }

  private function mapearNombreAClave($nombre) {
    return match (true) {
      str_contains(strtoupper($nombre), 'COURANT') => 'cuenta1',
      str_contains(strtoupper($nombre), 'LIVRET A') => 'cuenta2',
      str_contains(strtoupper($nombre), 'LIVRET B') => 'cuenta3',
      default => 'cuenta_extra'
    };
  }

  private function extraerDatosCliente($text, $pdfConfig) {
    $lineas = explode("\n", $text);
    $cliente = ['id' => 'N/A', 'nombre' => 'N/A', 'direccion' => 'N/A', 'cp' => 'N/A', 'ciudad' => 'N/A', 'banco' => 'N/A'];
    if (preg_match($pdfConfig['banca_nombre'], $text, $bM)) $cliente['banco'] = trim($bM[1]);
    if (preg_match($pdfConfig['titular']['identificador'], $text, $idM)) $cliente['id'] = $idM[1];
    foreach ($lineas as $i => $linea) {
      if (preg_match($pdfConfig['titular']['titular'], $linea, $titM)) {
        $cliente['nombre'] = trim($titM[2]);
        $cliente['direccion'] = trim($lineas[$i + 1] ?? 'N/A');
        if (isset($lineas[$i + 2]) && preg_match($pdfConfig['titular']['cp'], $lineas[$i + 2], $cpM)) {
          $cliente['cp'] = $cpM[1];
          if (preg_match($pdfConfig['titular']['ciudad'], $lineas[$i + 2], $cityM)) $cliente['ciudad'] = trim($cityM[1]);
        }
        break;
      }
    }
    return $cliente;
  }
}
