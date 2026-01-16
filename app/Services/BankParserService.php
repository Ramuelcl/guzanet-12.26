<?php
// C:\laragon\www\laravel\guzanet-12.26\app\Services\BankParserService.php

namespace App\Services;

use Carbon\Carbon;

class BankParserService {
  public function parseLaBanquePostale(string $text) {
    $cuentasDetectadas = [];

    // Dividimos el texto por tipo de cuenta detectado
    $secciones = preg_split('/(Compte Courant Postal|Livret A)/i', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

    $tipoActual = '';
    foreach ($secciones as $seccion) {
      if (in_array(strtoupper(trim($seccion)), ['COMPTE COURANT POSTAL', 'LIVRET A'])) {
        $tipoActual = strtoupper(trim($seccion));
        continue;
      }

      // Extraer Metadatos de la sección actual
      preg_match('/IBAN\s*:\s*([A-Z0-9\s]{15,34})/i', $seccion, $ibanM);
      $iban = isset($ibanM[1]) ? str_replace(' ', '', trim($ibanM[1])) : null;

      preg_match('/Ancien solde au (\d{2}\/\d{2}\/\d{4})\s+([\d\s]+,\d{2})/', $seccion, $soldeM);
      $fechaBase = isset($soldeM[1]) ? Carbon::createFromFormat('d/m/Y', $soldeM[1]) : now();
      $saldoAnterior = isset($soldeM[2]) ? (float)str_replace([' ', ','], ['', '.'], $soldeM[2]) : 0;

      if (!$iban) continue;

      // Extraer bloque entre "Ancien solde" y "Nouveau solde"
      $lineas = explode("\n", $seccion);
      $enOperaciones = false;
      $movimientos = [];
      $tempMov = null;

      foreach ($lineas as $linea) {
        // Inicia el bloque de operaciones
        if (str_contains($linea, 'Ancien solde')) {
          $enOperaciones = true;
          continue;
        }
        // Finaliza el bloque
        if (str_contains($linea, 'Nouveau solde')) {
          $enOperaciones = false;
          break;
        }

        if ($enOperaciones) {
          // Detectar nueva fecha (Inicio de movimiento)
          // Patrón: "26/12" seguido de texto
          if (preg_match('/^(\d{2}\/\d{2})\s+(.+)/', trim($linea), $match)) {
            if ($tempMov) $movimientos[] = $tempMov; // Guardar anterior

            $fechaMov = Carbon::createFromDate($fechaBase->year, (int)substr($match[1], 3, 2), (int)substr($match[1], 0, 2));
            if ($fechaMov->month < $fechaBase->month) $fechaMov->addYear();

            $tempMov = [
              'fecha' => $fechaMov->format('Y-m-d'),
              'desc' => trim($match[2]),
              'monto' => 0,
              'es_debito' => true
            ];
          }
          // Si es una línea de detalle (sin fecha al inicio)
          elseif ($tempMov && strlen(trim($linea)) > 5 && !preg_match('/^(\d{2}\/\d{2})/', trim($linea))) {
            // Si la línea contiene los montos finales (€ y Francos)
            if (preg_match('/([\d\s]+,\d{2})\s+([+-])\s+([\d\s]+,\d{2})$/', trim($linea), $montoM)) {
              $tempMov['monto'] = (float)str_replace([' ', ','], ['', '.'], $montoM[1]);
              $tempMov['es_debito'] = ($montoM[2] === '-');
              $tempMov['valor_frf'] = (float)str_replace([' ', ','], ['', '.'], $montoM[3]);
            } else {
              // Es detalle de descripción multilínea
              $tempMov['desc'] .= ' ' . trim($linea);
            }
          }
        }
      }
      if ($tempMov) $movimientos[] = $tempMov;

      $cuentasDetectadas[] = [
        'iban' => $iban,
        'tipo' => $tipoActual,
        'saldo_anterior' => $saldoAnterior,
        'movimientos' => $movimientos
      ];
    }

    return $cuentasDetectadas;
  }
}
