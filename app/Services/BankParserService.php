<?php
// C:\laragon\www\laravel\guzanet-12.26\app\Services\BankParserService.php

namespace App\Services;

class BankParserService {
  private array $lineas = [];
  private int $punteroGlobal = 0;
  private array $configBanca = [];

  public function __construct() {
    $this->configBanca = config('banca.pdf') ?? [];
  }

  public function parseBancas(string $text): array {
    $this->punteroGlobal = 0;
    $this->prepararLineas($text);

    $bancoKey = $this->obtenerKeyPorNombre($text);
    $reglas = $this->configBanca[$bancoKey];

    return [
      'banco' => $this->identificarBanco($text),
      'cuentas' => $this->procesarCuentasLineal($reglas)
    ];
  }

  private function procesarCuentasLineal(array $reglas): array {
    $cuentasResultantes = [];

    foreach ($reglas['cuentas'] as $tipo => $conf) {
      dump(">>> BUSCANDO CUENTA: {$conf['label']} (Puntero actual: {$this->punteroGlobal})");

      for ($i = $this->punteroGlobal; $i < count($this->lineas); $i++) {
        if (preg_match($conf['regex'], $this->lineas[$i], $matches)) {
          $this->dumpLinea($i, "CUENTA DETECTADA");

          $operacionesData = $this->extraerOperacionesLineal($i, $reglas['operaciones']);

          $datosCuenta = [
            'label' => $conf['label'],
            'numero' => trim($matches[1]),
            'saldo' => $this->limpiarSaldo($matches[2]),
            'operaciones' => $operacionesData
          ];

          dump("<<< CUENTA {$conf['label']} FINALIZADA", $datosCuenta);
          $cuentasResultantes[] = $datosCuenta;
          break;
        }
      }
    }
    return $cuentasResultantes;
  }

  private function extraerOperacionesLineal(int $indexInicio, array $configOp): array {
    $ops = [];
    $bloqueAbierto = false;
    $ultimo = -1;

    for ($i = $indexInicio; $i < count($this->lineas); $i++) {
      $linea = $this->lineas[$i];

      if (preg_match($configOp['inicio'], $linea)) {
        $bloqueAbierto = true;
        $this->dumpLinea($i, ">>> INICIO BLOQUE OPS");
        continue;
      }

      if ($bloqueAbierto && preg_match($configOp['fin'], $linea)) {
        $this->dumpLinea($i, "<<< FIN BLOQUE OPS");
        $this->punteroGlobal = $i + 1;
        break;
      }

      if ($bloqueAbierto) {
        if (preg_match('/Relevé n°|Page \d|Vos opérations/i', $linea)) continue;

        $parsed = $this->parsearOperacion($linea, $configOp['regex_lineas'], $i);

        if ($parsed) {
          if ($parsed['tipo_linea'] === 'principal') {
            $ops[] = $parsed;
            $ultimo++;
          } elseif ($parsed['tipo_linea'] === 'monto_solo' && $ultimo >= 0) {
            // ASIGNACIÓN DE AMBOS MONTOS
            $ops[$ultimo]['tipo'] = $parsed['tipo'];
            $ops[$ultimo]['monto'] = $parsed['monto'];
            $ops[$ultimo]['monto_francos'] = $parsed['monto_francos'];
          } elseif ($parsed['tipo_linea'] === 'continuacion' && $ultimo >= 0) {
            $ops[$ultimo]['detalle'] .= " " . $parsed['detalle'];
          }
        }
      }
    }
    return $ops;
  }

  private function parsearOperacion(string $linea, array $regexLineas, int $indice): ?array {
    // 1. Detección de línea con Fecha (Líneas 41, 44, 47)
    if (preg_match('/^(\d{2}\/\d{2})\s*(.*)/i', $linea, $matches)) {
      return [
        'tipo_linea' => 'principal',
        'fecha'   => $matches[1],
        'detalle' => trim($matches[2]),
        'tipo'    => '?',
        'monto'   => '0.00',
        'monto_francos' => '0.00'
      ];
    }

    // 2. Detección de línea de Montos (Líneas 43, 46, 49: "12,90\t-84,62")
    // Buscamos todos los números que tengan el formato de moneda francesa
    if (preg_match_all('/([+-]?[\d\s\.]*,\d{2})/', $linea, $matches)) {
      $valores = $matches[1]; // Array con todos los montos encontrados

      // El último valor de la línea es el monto real con el signo
      $textoFrancos = end($valores);
      // El primer valor suele ser el monto de referencia o francos
      $textoMontoReal = $valores[0];

      $montoFloat = $this->limpiarSaldo($textoMontoReal);
      $signoReal = str_contains($textoFrancos, '-') ? 'credito' : 'debito';
      $signo = str_contains($textoFrancos, '-') ? -1 : 1;

      $this->dumpLinea($indice, "MONTOS DETECTADOS: Francos[$textoFrancos] Real[" . number_format(abs($montoFloat) * $signo, 2, '.', '') . "]");

      return [
        'tipo_linea' => 'monto_solo',
        'tipo'       => $signoReal,
        'monto'      => number_format(abs($montoFloat) * $signo, 2, '.', ''),
        'monto_francos' => $this->limpiarSaldo($textoFrancos)
      ];
    }

    // 3. Continuación de texto (Líneas 42, 45, 48)
    if (preg_match('/^([A-Z\s].*)$/i', $linea, $matches)) {
      return [
        'tipo_linea' => 'continuacion',
        'detalle'    => trim($matches[1])
      ];
    }

    return null;
  }

  private function prepararLineas(string &$text): void {
    // Reemplazamos tabuladores y otros caracteres por un espacio simple para no perder la separación de columnas
    $text = str_replace(["\t", "\xc2\xa0", "\xa0", "\r"], " ", $text);

    $this->lineas = array_values(array_filter(array_map('trim', explode("\n", $text)), function ($l) {
      return $l !== '';
    }));

    // PARA VER EL RESULTADO:
    // Opción A: dump() -> Muestra el resultado y sigue la ejecución
    // dump("--- RESULTADO DE PREPARAR LINEAS ---", $this->lineas);

    // Opción B: dd() -> Muestra el resultado y MATA el proceso (útil para inspeccionar el array completo)
    // dd($this->lineas);
  }

  private function dumpLinea(int $indice, string $contexto = ''): void {
    dump([
      'indice'   => $indice,
      'contexto' => $contexto,
      'linea'    => $this->lineas[$indice] ?? '---'
    ]);
  }

  private function limpiarSaldo(string $valor): float {
    $esNegativo = str_contains($valor, '-');
    $clean = preg_replace('/[^0-9,]/', '', $valor);
    $clean = str_replace(',', '.', $clean);
    return $esNegativo ? -(float)$clean : (float)$clean;
  }

  private function identificarBanco(string $text): string {
    foreach ($this->configBanca as $config) {
      if (isset($config['nombre_banco']) && preg_match($config['nombre_banco'], $text, $matches)) {
        return trim($matches[1]);
      }
    }
    return "Desconocido";
  }

  private function obtenerKeyPorNombre(string $text): string {
    foreach ($this->configBanca as $key => $config) {
      if (preg_match($config['nombre_banco'], $text)) return $key;
    }
    throw new \Exception("Banco no identificado.");
  }
}
