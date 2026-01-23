<?php
// C:\laragon\www\laravel\guzanet-12.26\app\Services\BankParserService.php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class BankParserService {
  private array $lineas = [];
  private array $configBanca = [];

  public function __construct() {
    // Cargamos la configuración una sola vez al instanciar
    $this->configBanca = config('banca.pdf') ?? [];
  }

  /**
   * Procesa el texto del PDF y devuelve los datos estructurados.
   */
  public function parseBancas(string $text): array {
    $this->prepararLineas($text);

    // Buscamos el nombre del banco dinámicamente
    $nombreBanco = $this->identificarBanco($text);
    // PROACTIVO: Para seguir con el parseo, necesitamos saber a qué bloque 
    // de reglas pertenece este nombre encontrado.
    $bancoKey = $this->obtenerKeyPorNombre($text);

    $reglas = $this->configBanca[$bancoKey];

    $paso = [
      'banco_nombre_real' => $nombreBanco, // El texto extraído (Ej: LA BANQUE POSTALE)
      'banco_key' => $bancoKey,           // La llave interna (Ej: banco1)
      'cliente' => [
        'nombre' => $this->extraerCampo($text, $reglas['titular']['nombre']),
        'id'     => $this->extraerCampo($text, $reglas['titular']['id_cliente']),
      ],
      'cuentas' => $this->procesarCuentas($text, $reglas),
    ];
    Log::info('Resultado del parsing completo: ' . json_encode($paso));
    return $paso;
  }

  /**
   * Extrae el nombre del banco usando EXCLUSIVAMENTE los patrones de banca.php
   */
  private function identificarBanco(string $text): string {
    foreach ($this->configBanca as $config) {
      // Usamos el patrón definido en la config: '/Courrier\s*:\s*([A-Z0-9\s]{15,})/i'
      if (isset($config['nombre_banco']) && preg_match($config['nombre_banco'], $text, $matches)) {
        // Retornamos el contenido del primer paréntesis capturado
        return trim($matches[1]);
      }
    }

    throw new \Exception("No se pudo extraer el nombre del banco. El texto no coincide con ningún patrón de 'Courrier' en banca.php");
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
    // Asegurar que el texto esté en UTF-8
    $text = iconv('ISO-8859-1', 'UTF-8//TRANSLIT', $text);
    // PROACTIVO: Eliminamos espacios de no ruptura y normalizamos tabulaciones
    $text = str_replace(["\t", "\r", "\xc2\xa0", "\xa0"], [" ", "", " ", " "], $text);
    $this->lineas = array_values(array_filter(array_map('trim', explode("\n", $text))));
  }

  private function extraerCampo(string $text, string $regex): string {
    if (preg_match($regex, $text, $matches)) {
      return trim(end($matches));
    }
    return 'N/A';
  }

  /**
   * Busca cuentas y sus detalles específicos (IBAN/BIC) con validación de línea única.
   */
  private function procesarCuentas(string $fullText, array $reglas): array {
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

          // 1. Buscamos la línea que contiene el IBAN de ESTA cuenta específica
          $datosBancarios = $this->extraerLineaIbanYBic($numeroCuentaLimpio, $reglas['detalles']);
          $cuentasEncontradas[] = [
            'tipo_key' => $tipo,
            'label'    => $conf['label'],
            'detalle'  => [
              'numero' => $numeroCuenta,
              'saldo'  => $this->limpiarSaldo($saldoTexto),
              'tipo'   => $conf['label'],
              'iban'   => $datosBancarios['iban'],
              'bic'    => $datosBancarios['bic'],
            ],
            'operaciones' => $this->extraerOperaciones($index, $this->lineas, $tipo)
          ];
          break;
        }
      }
    }
    return $cuentasEncontradas;
  }

  public function extraerOperaciones(int $index, array $lineas, string $tipo): array {
    // Log::info("Extrayendo operaciones desde línea {$index} con reglas movimientos: " . json_encode($reglasMov));
    $operaciones = [];
    $inicioEncontrado = false;
    $descripcionAcumulada = '';
    $fechaActual = null;
    $anoBase = $this->extraerAnoBase($index);
    $indiceOperacion = 0; // Contador para el índice de la operación
    $reglasMov = $this->configBanca[$tipo]['movimientos']; // Asumiendo bancoN
    $delimitadores = $reglasMov['delimitadores'];

    for ($i = $index + 1; $i < count($lineas); $i++) {
      $lineaActual = trim($this->lineas[$i]);

      if (!$inicioEncontrado && strpos($lineaActual, $delimitadores['inicio']) !== false) {
        Log::info("Inicio encontrado: '{$lineaActual}'");
        $inicioEncontrado = true;
        continue;
      }

      if ($inicioEncontrado) {
        if (strpos($lineaActual, $delimitadores['fin']) !== false) {
          Log::info("Fin encontrado: '{$lineaActual}'");
          // Procesar la última operación acumulada si existe
          if ($fechaActual) {
            Log::info("Descripción final: '{$descripcionAcumulada}'");
            $operacion = $this->parsearOperacion($fechaActual, $descripcionAcumulada, $anoBase, $indiceOperacion);
            $operaciones[] = $operacion;
            Log::info("Operación final parseada: " . json_encode($operacion));
            $indiceOperacion++;
          }
          break;
        }

        // Verificar si la línea contiene una fecha (DD/MM o DD/MM/YYYY)
        if (preg_match('/^(\d{2}\/\d{2}(?:\/\d{4})?)/', $lineaActual, $matchesFecha)) {
          // Si ya hay una fecha anterior, procesar la operación acumulada
          if ($fechaActual) {
            Log::info("Descripción final: '{$descripcionAcumulada}'");
            $operacion = $this->parsearOperacion($fechaActual, $descripcionAcumulada, $anoBase, $indiceOperacion);
            $operaciones[] = $operacion;
            Log::info("Operación parseada: " . json_encode($operacion));
            $indiceOperacion++;
          }
          // Iniciar nueva operación
          $fechaActual = $matchesFecha[1];
          $descripcionAcumulada = $lineaActual;
        } else {
          // Concatenar a la descripción acumulada (para descripciones multilínea)
          $descripcionAcumulada .= ' ' . $lineaActual;
        }
      }
    }

    Log::info('Operaciones extraídas total: ' . count($operaciones));
    return $operaciones;
  }

  private function extraerAnoBase(int $lineaCuentaIndex): int {
    // Buscar la línea "Ancien solde au DD/MM/YYYY" para extraer el año base
    for ($i = $lineaCuentaIndex; $i < count($this->lineas); $i++) {
      $linea = $this->lineas[$i];
      if (preg_match('/Ancien solde au (\d{2}\/\d{2}\/(\d{4}))/', $linea, $matches)) {
        return (int) $matches[2];
      }
    }
    // Si no encuentra, usar el año actual
    return date('Y');
  }

  private function parsearOperacion(string $fecha, string $descripcionCompleta, int $anoBase, int $indiceOperacion = 0): array {
    Log::info("Parseando operación #{$indiceOperacion} - Descripción: '{$descripcionCompleta}'");
    // Limpiar la descripción
    $descripcionCompleta = trim($descripcionCompleta);

    // Obtener reglas de movimientos para usar regex_fila
    $reglasMov = $this->configBanca['banco1']['movimientos']; // Asumiendo banco1, ajustar si hay múltiples bancos
    $pattern = $reglasMov['regex_fila'];

    if (preg_match($pattern, $descripcionCompleta, $matches)) {
      $fechaExtraida = $matches[1];
      $concepto = trim($matches[2]);
      $debito = $this->limpiarSaldo($matches[3]);
      $credito = isset($matches[4]) ? $this->limpiarSaldo($matches[4]) : 0.0;
      $soldeFrancs = $this->limpiarSaldo($matches[5]);

      $resultado = [
        'Date' => $this->normalizarFecha($fechaExtraida, $anoBase),
        'Opérations' => $concepto,
        'Débit' => $debito,
        'Crédit' => $credito,
        'francs' => $soldeFrancs,
      ];
      Log::info("Operación #{$indiceOperacion} parseada exitosamente: " . json_encode($resultado));
      return $resultado;
    } else {
      // Si no coincide el patrón, devolver con valores por defecto
      $resultado = [
        'Date' => $this->normalizarFecha($fecha, $anoBase),
        'Opérations' => $descripcionCompleta,
        'Débit' => 0.0,
        'Crédit' => 0.0,
        'francs' => 0.0,
      ];
      Log::info("Operación #{$indiceOperacion} no parseada (patrón no coincide): " . json_encode($resultado));
      return $resultado;
    }
  }

  private function normalizarFecha(string $fecha, int $anoBase): string {
    // Si la fecha es DD/MM, agregar el año base o ajustado
    if (preg_match('/^(\d{2})\/(\d{2})$/', $fecha, $matches)) {
      $dia = (int) $matches[1];
      $mes = (int) $matches[2];
      $ano = $anoBase;

      // Si la fecha supera 31/12 del año base, sumar 1 al año
      if ($mes > 12 || ($mes == 12 && $dia > 31)) {
        $ano = $anoBase + 1;
      }

      return sprintf('%02d/%02d/%04d', $dia, $mes, $ano);
    }
    return $fecha;
  }

  /**
   * PROACTIVO: Localiza la línea del IBAN que corresponde a la cuenta y extrae el BIC de esa misma línea.
   */
  private function extraerLineaIbanYBic(string $numeroCuentaLimpio, array $reglasDetalles): array {
    $resultado = ['iban' => 'N/A', 'bic' => 'N/A'];

    foreach ($this->lineas as $linea) {
      // Buscamos si la línea tiene un IBAN
      if (preg_match($reglasDetalles['iban'], $linea, $matchesIban)) {
        $ibanEncontrado = trim($matchesIban[1]);
        $ibanLimpio = str_replace(' ', '', $ibanEncontrado);

        // Verificamos si este IBAN pertenece a la cuenta actual
        if (strpos($ibanLimpio, $numeroCuentaLimpio) !== false) {
          $resultado['iban'] = $ibanEncontrado;

          // PROACTIVO: Buscamos el BIC en ESTA MISMA línea para evitar duplicados de otras cuentas
          if (preg_match($reglasDetalles['bic'], $linea, $matchesBic)) {
            $resultado['bic'] = trim($matchesBic[1]);
          }

          return $resultado; // Retornamos inmediatamente al encontrar la pareja correcta
        }
      }
    }

    return $resultado;
  }

  private function limpiarSaldo($valor) {
    // Convierte "1 250,80" o "1.250,80" en 1250.80
    $clean = str_replace([' ', '.'], '', $valor);
    return (float) str_replace(',', '.', $clean);
  }
}
