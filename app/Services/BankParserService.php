<?php
// C:\laragon\www\laravel\guzanet-12.26\app\Services\BankParserService.php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class BankParserService {
  private array $lineas = [];
  private array $configBanca = [];

  public function __construct() {
    $this->configBanca = config('banca.pdf') ?? [];
  }

  /**
   * Procesa el texto del PDF y devuelve los datos estructurados.
   */
  public function parseBancas(string $text): array {
    $this->prepararLineas($text);

    $nombreBanco = $this->identificarBanco($text);
    $bancoKey = $this->obtenerKeyPorNombre($text);
    $reglas = $this->configBanca[$bancoKey];

    $fechaDocumento = $this->extraerFechaDocumento();
    $anioDocumento = $this->extraerAnioDocumento($fechaDocumento);

    $paso = [
      'banco_nombre_real' => $nombreBanco,
      'banco_key' => $bancoKey,
      'fecha_documento' => $fechaDocumento,
      'anio_documento' => $anioDocumento,
      'cliente' => [
        'nombre' => $this->extraerCampo($text, $reglas['titular']['nombre']),
        'id'     => $this->extraerCampo($text, $reglas['titular']['id_cliente']),
      ],
      'cuentas' => $this->procesarCuentas($reglas, $anioDocumento),
    ];

    Log::info('Resultado del parsing completo: ' . json_encode($paso));
    return $paso;
  }

  /**
   * Extrae la fecha del documento del PDF
   */
  private function extraerFechaDocumento(): ?string {
    foreach ($this->lineas as $linea) {
      // Buscar "Ancien solde au dd/mm/yyyy"
      if (preg_match('/Ancien solde au (\d{2}\/\d{2}\/\d{4})/i', $linea, $matches)) {
        return trim($matches[1]);
      }
      // Buscar cualquier fecha completa
      if (preg_match('/(\d{2}\/\d{2}\/\d{4})/i', $linea, $matches)) {
        return trim($matches[1]);
      }
    }
    return null;
  }

  /**
   * Extrae el año del documento o usa 2016 como predeterminado
   */
  private function extraerAnioDocumento(?string $fechaDocumento): int {
    if ($fechaDocumento && preg_match('/\d{2}\/\d{2}\/(\d{4})/', $fechaDocumento, $matches)) {
      return (int)$matches[1];
    }
    return 2016; // Año predeterminado
  }

  /**
   * Extrae el nombre del banco usando EXCLUSIVAMENTE los patrones de banca.php
   */
  private function identificarBanco(string $text): string {
    foreach ($this->configBanca as $config) {
      if (isset($config['nombre_banco']) && preg_match($config['nombre_banco'], $text, $matches)) {
        return trim($matches[1]);
      }
    }

    throw new \Exception("No se pudo extraer el nombre del banco.");
  }

  /**
   * Auxiliar para saber qué reglas (banco1, banco2...) aplicar tras identificar el texto
   */
  private function obtenerKeyPorNombre(string $text): string {
    foreach ($this->configBanca as $key => $config) {
      if (preg_match($config['nombre_banco'], $text)) {
        return $key;
      }
    }
    throw new \Exception("No se encontró una llave de configuración para este banco.");
  }

  private function prepararLineas(string $text): void {
    $text = str_replace(["\t", "\r", "\xc2\xa0", "\xa0"], [" ", "", " ", " "], $text);
    $this->lineas = array_values(array_filter(array_map('trim', explode("\n", $text))));
    Log::info("Total líneas preparadas: " . count($this->lineas));
  }

  private function extraerCampo(string $text, string $regex): string {
    if (preg_match($regex, $text, $matches)) {
      return trim(end($matches));
    }
    return 'N/A';
  }

  /**
   * Busca cuentas y sus detalles específicos (IBAN/BIC)
   */
  private function procesarCuentas(array $reglas, int $anioBase): array {
    $cuentasEncontradas = [];
    $cuentasConfig = $reglas['cuentas'];

    foreach ($cuentasConfig as $tipo => $conf) {
      $regex = $conf['regex'];

      foreach ($this->lineas as $index => $linea) {
        $lineaLimpia = preg_replace('/[[:^print:]]/', ' ', $linea);

        if (preg_match($regex, $lineaLimpia, $matches)) {
          $numeroCuenta = trim($matches[1]);
          $saldoTexto = trim($matches[2]);
          $numeroCuentaLimpio = str_replace(' ', '', $numeroCuenta);

          $datosBancarios = $this->extraerLineaIbanYBic($numeroCuentaLimpio, $reglas['detalles']);

          $cuentasEncontradas[] = [
            'tipo_key' => $tipo,
            'label'    => $conf['label'],
            'index'    => $index,
            'detalle'  => [
              'numero' => $numeroCuenta,
              'saldo'  => $this->limpiarSaldo($saldoTexto),
              'tipo'   => $conf['label'],
              'iban'   => $datosBancarios['iban'],
              'bic'    => $datosBancarios['bic'],
            ],
            'operaciones' => []
          ];
          break;
        }
      }
    }

    // Ordenar cuentas por índice y extraer operaciones
    usort($cuentasEncontradas, fn($a, $b) => $a['index'] <=> $b['index']);

    foreach ($cuentasEncontradas as $i => &$cuenta) {
      Log::info("Procesando operaciones para cuenta: " . $cuenta['detalle']['numero']);
      $cuenta['operaciones'] = $this->procesarOperacionesParaCuenta($cuenta['detalle']['numero'], $anioBase);
      unset($cuenta['index']);
    }

    return $cuentasEncontradas;
  }

  private function procesarOperacionesParaCuenta(string $numeroCuenta, int $anioBase): array {
    $operaciones = [];
    $enContenidoCuenta = false;
    $lineasOperaciones = [];
    $ultimaFecha = null;
    $ultimoAnio = $anioBase;
    $ultimoMes = null;

    Log::info("=== BUSCANDO OPERACIONES PARA CUENTA: $numeroCuenta (Año base: $anioBase) ===");

    foreach ($this->lineas as $index => $linea) {
      $lineaTrim = trim($linea);

      // Buscar inicio de operaciones para esta cuenta
      if (!$enContenidoCuenta && (
        strpos($lineaTrim, $numeroCuenta) !== false ||
        strpos($lineaTrim, 'Ancien solde') !== false
      )) {
        $enContenidoCuenta = true;
        Log::info("Inicio encontrado en línea $index: " . substr($lineaTrim, 0, 50));
        continue;
      }

      if ($enContenidoCuenta) {
        // Detectar líneas de operación (contienen fecha dd/mm)
        if (preg_match('/^(\d{2}\/\d{2})/', $lineaTrim, $matches)) {
          $fechaOperacion = $matches[1];
          list($dia, $mes) = explode('/', $fechaOperacion);
          $mes = (int)$mes;

          // Manejar cambio de año
          if ($ultimoMes !== null) {
            // Si pasamos de diciembre (12) a enero (1), incrementar año
            if ($ultimoMes === 12 && $mes === 1) {
              $ultimoAnio++;
              Log::info("Cambio de año detectado: " . ($ultimoAnio - 1) . " --> {$ultimoAnio}");
            }
            // Si la fecha actual es menor que la anterior (ej: 02/01 vs 26/12), 
            // asumimos que es del año siguiente solo si estamos en enero
            elseif ($mes < $ultimoMes && $mes === 1) {
              $ultimoAnio++;
              Log::info("Cambio de año por mes menor: " . ($ultimoAnio - 1) . " --> {$ultimoAnio}");
            }
          }

          $ultimaFecha = $fechaOperacion;
          $ultimoMes = $mes;

          $fechaCompleta = "{$dia}/{$mes}/{$ultimoAnio}";
          $lineasOperaciones[] = [
            'linea' => $lineaTrim,
            'fecha_completa' => $fechaCompleta
          ];

          Log::info("Línea de operación [$index]: $fechaCompleta - " . substr($lineaTrim, 0, 100));
        }

        // Detener cuando encontramos fin
        if (
          strpos($lineaTrim, 'Nouveau solde') !== false ||
          strpos($lineaTrim, 'Total des opérations') !== false ||
          strpos($lineaTrim, 'Livret A') !== false ||
          empty($lineaTrim)
        ) {
          Log::info("Fin encontrado en línea $index: " . substr($lineaTrim, 0, 50));
          break;
        }
      }
    }

    Log::info("Total líneas de operaciones encontradas: " . count($lineasOperaciones));

    // Parsear cada línea
    foreach ($lineasOperaciones as $item) {
      $operacion = $this->parsearLineaOperacion($item['linea'], $item['fecha_completa']);
      if ($operacion) {
        $operaciones[] = $operacion;
      }
    }

    return $operaciones;
  }

  private function parsearLineaOperacion(string $linea, string $fechaCompleta): ?array {
    // Extraer día y mes de la fecha completa
    list($dia, $mes, $anio) = explode('/', $fechaCompleta);
    $fechaCorta = "{$dia}/{$mes}";

    // Eliminar la fecha corta del inicio de la línea para obtener el resto
    $lineaSinFecha = preg_replace('/^' . preg_quote($fechaCorta, '/') . '\s*/', '', $linea);

    // Patrón 1: Operaciones con descripción y monto único
    // Ejemplo: "ACHAT CB STARBUCKS 24,50 -1.234,56"
    if (preg_match('/^(.+?)\s+([\d\s.,]+?)\s+([+-]?\d+(?:[.,]\d{2})?)$/', $lineaSinFecha, $matches)) {
      $descripcion = trim($matches[1]);
      $montoRaw = trim($matches[2]);
      $saldoRaw = trim($matches[3]);

      // Limpiar monto
      $montoClean = str_replace([' ', '.'], '', $montoRaw);
      $montoClean = str_replace(',', '.', $montoClean);
      $monto = (float) $montoClean;

      $saldo = $this->limpiarSaldo($saldoRaw);

      return [
        'Date' => $fechaCompleta,
        'Opérations' => $descripcion,
        'Débit' => $saldo < 0 ? abs($monto) : 0,
        'Crédit' => $saldo > 0 ? $monto : 0,
        'francs' => $saldo,
        'tipo' => $saldo < 0 ? 'debito' : 'credito',
        'importe' => $monto
      ];
    }

    // Patrón 2: Operaciones con débito y crédito separados (menos común)
    // Ejemplo: "TRANSFERT 100,00 0,00 1.234,56"
    if (preg_match('/^(.+?)\s+(\d+(?:[.,]\d{2})?)\s+(\d+(?:[.,]\d{2})?)?\s+([+-]?\d+(?:[.,]\d{2})?)$/', $lineaSinFecha, $matches)) {
      $descripcion = trim($matches[1]);
      $debitoRaw = trim($matches[2]);
      $creditoRaw = isset($matches[3]) ? trim($matches[3]) : '0';
      $saldoRaw = trim($matches[4]);

      $debito = $this->limpiarSaldo($debitoRaw);
      $credito = $this->limpiarSaldo($creditoRaw);
      $saldo = $this->limpiarSaldo($saldoRaw);

      return [
        'Date' => $fechaCompleta,
        'Opérations' => $descripcion,
        'Débit' => $debito,
        'Crédit' => $credito,
        'francs' => $saldo,
        'tipo' => $debito > 0 ? 'debito' : ($credito > 0 ? 'credito' : 'neutro'),
        'importe' => $debito > 0 ? $debito : $credito
      ];
    }

    // Patrón 3: Solo descripción (operaciones de 0€ o formato diferente)
    if (preg_match('/^(.+?)$/', $lineaSinFecha, $matches)) {
      return [
        'Date' => $fechaCompleta,
        'Opérations' => trim($matches[1]),
        'Débit' => 0,
        'Crédit' => 0,
        'francs' => 0,
        'tipo' => 'neutro',
        'importe' => 0
      ];
    }

    Log::warning("No se pudo parsear línea de operación: $linea (fecha: $fechaCompleta)");
    return null;
  }

  /**
   * Localiza la línea del IBAN que corresponde a la cuenta y extrae el BIC de esa misma línea.
   */
  private function extraerLineaIbanYBic(string $numeroCuentaLimpio, array $reglasDetalles): array {
    $resultado = ['iban' => 'N/A', 'bic' => 'N/A'];

    foreach ($this->lineas as $linea) {
      if (preg_match($reglasDetalles['iban'], $linea, $matchesIban)) {
        $ibanEncontrado = trim($matchesIban[1]);
        $ibanLimpio = str_replace(' ', '', $ibanEncontrado);

        if (strpos($ibanLimpio, $numeroCuentaLimpio) !== false) {
          $resultado['iban'] = $ibanEncontrado;

          if (preg_match($reglasDetalles['bic'], $linea, $matchesBic)) {
            $resultado['bic'] = trim($matchesBic[1]);
          }
          return $resultado;
        }
      }
    }

    // Fallback: buscar BIC por separado
    if ($resultado['bic'] === 'N/A') {
      foreach ($this->lineas as $linea) {
        if (preg_match($reglasDetalles['bic'], $linea, $matchesBic)) {
          $resultado['bic'] = trim($matchesBic[1]);
          break;
        }
      }
    }

    return $resultado;
  }

  private function limpiarSaldo($valor): float {
    if (empty($valor) || $valor === 'N/A' || trim($valor) === '') {
      return 0.0;
    }

    $clean = trim($valor);

    // Si tiene signo negativo al inicio
    $esNegativo = strpos($clean, '-') === 0;
    if ($esNegativo) {
      $clean = substr($clean, 1);
    }

    // Reemplazar espacios y puntos de separación de miles
    $clean = str_replace([' ', '.'], '', $clean);
    // Reemplazar coma decimal por punto
    $clean = str_replace(',', '.', $clean);

    $resultado = (float) $clean;

    return $esNegativo ? -$resultado : $resultado;
  }
}
