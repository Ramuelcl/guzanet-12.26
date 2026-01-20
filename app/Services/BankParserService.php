<?php
// C:\laragon\www\laravel\guzanet-12.26\app\Services\BankParserService.php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class BankParserService {
  private array $lineas = [];
  private array $configBanca = [];

  public function __construct() {
    // PROACTIVO: Validamos que la configuración exista para evitar errores fatales posteriores
    $this->configBanca = config('banca.pdf') ?? [];
    if (empty($this->configBanca)) {
      Log::error("BankParserService: La configuración 'banca.pdf' está vacía o no existe.");
    }
  }

  /**
   * Procesa el texto del PDF y devuelve los datos estructurados.
   */
  public function parseBancas(string $text): array {
    if (empty(trim($text))) {
      throw new Exception("El contenido del PDF está vacío o no se pudo extraer texto.");
    }

    $this->prepararLineas($text);

    // Identificación dinámica
    $nombreBanco = $this->identificarBanco($text);
    $bancoKey = $this->obtenerKeyPorNombre($text);

    $reglas = $this->configBanca[$bancoKey] ?? null;
    if (!$reglas) {
      throw new Exception("No se encontraron reglas de procesamiento para: {$bancoKey}");
    }

    $paso = [
      'banco_nombre_real' => $nombreBanco,
      'banco_key' => $bancoKey,
      'cliente' => [
        'nombre' => $this->extraerCampo($text, $reglas['titular']['nombre'] ?? ''),
        'id'     => $this->extraerCampo($text, $reglas['titular']['id_cliente'] ?? ''),
      ],
      'cuentas' => $this->procesarCuentas($text, $reglas),
    ];

    // Aviso proactivo: Si no se encontraron cuentas, podría ser un error de lectura o de Regex
    if (empty($paso['cuentas'])) {
      Log::warning("BankParserService: No se detectaron cuentas para el banco {$nombreBanco}. Revise los patrones en banca.php.");
    }

    return $paso;
  }

  private function identificarBanco(string $text): string {
    foreach ($this->configBanca as $config) {
      if (isset($config['nombre_banco']) && preg_match($config['nombre_banco'], $text, $matches)) {
        return trim($matches[1]);
      }
    }
    throw new Exception("La entidad bancaria no pudo ser identificada tras el patrón 'Courrier'.");
  }

  private function obtenerKeyPorNombre(string $text): string {
    foreach ($this->configBanca as $key => $config) {
      if (preg_match($config['nombre_banco'], $text)) {
        return $key;
      }
    }
    throw new Exception("Llave de configuración no hallada.");
  }

  private function prepararLineas(string $text): void {
    // Normalización proactiva de caracteres invisibles y espacios de no ruptura (UTF-8)
    $text = str_replace(["\t", "\r", "\xc2\xa0", "\xa0"], [" ", "", " ", " "], $text);
    $this->lineas = array_values(array_filter(array_map('trim', explode("\n", $text))));
  }

  private function extraerCampo(string $text, string $regex): string {
    if (!empty($regex) && preg_match($regex, $text, $matches)) {
      return trim(end($matches));
    }
    return 'N/A';
  }

  private function procesarCuentas(string $fullText, array $reglas): array {
    $cuentasEncontradas = [];
    $cuentasConfig = $reglas['cuentas'] ?? [];

    foreach ($cuentasConfig as $tipo => $conf) {
      $regex = $conf['regex'] ?? null;
      if (!$regex) continue;

      foreach ($this->lineas as $index => $linea) {
        // Limpieza de caracteres no imprimibles que rompen el Regex
        $lineaLimpia = preg_replace('/[[:^print:]]/', ' ', $linea);

        if (preg_match($regex, $lineaLimpia, $matches)) {
          $numeroCuenta = trim($matches[1]);
          $saldoTexto = trim($matches[2]);
          $numeroCuentaLimpio = str_replace(' ', '', $numeroCuenta);

          // Búsqueda proactiva de IBAN/BIC vinculados a esta cuenta
          $datosBancarios = $this->extraerLineaIbanYBic($numeroCuentaLimpio, $reglas['detalles'], $index);

          $cuentasEncontradas[] = [
            'tipo_key' => $tipo,
            'label'    => $conf['label'] ?? 'Cuenta',
            'detalle'  => [
              'numero' => $numeroCuenta,
              'saldo'  => $this->limpiarSaldo($saldoTexto),
              'tipo'   => $conf['label'] ?? 'N/A',
              'iban'   => $datosBancarios['iban'],
              'bic'    => $datosBancarios['bic'],
            ]
          ];
          break;
        }
      }
    }
    return $cuentasEncontradas;
  }

  /**
   * Busca el IBAN y BIC con sistema de seguridad Fallback.
   */
  private function extraerLineaIbanYBic(string $numeroCuentaLimpio, array $reglasDetalles, int $indexLineaActual): array {
    $resultado = ['iban' => 'N/A', 'bic' => 'N/A'];

    foreach ($this->lineas as $linea) {
      if (preg_match($reglasDetalles['iban'], $linea, $matchesIban)) {
        $ibanEncontrado = trim($matchesIban[1]);
        $ibanLimpio = str_replace(' ', '', $ibanEncontrado);

        // Verificamos pertenencia a la cuenta actual
        if (strpos($ibanLimpio, $numeroCuentaLimpio) !== false) {
          $resultado['iban'] = $ibanEncontrado;

          // INTENTO A: Misma línea
          if (preg_match($reglasDetalles['bic'], $linea, $matchesBic)) {
            $resultado['bic'] = trim($matchesBic[1]);
          }
          // INTENTO B: Fallback en fragmento (bloque de 25 líneas)
          else {
            $fragmento = implode("\n", array_slice($this->lineas, $indexLineaActual, 25));
            $resultado['bic'] = $this->extraerCampo($fragmento, $reglasDetalles['bic']);
          }
          return $resultado;
        }
      }
    }
    return $resultado;
  }

  private function limpiarSaldo($valor) {
    $clean = str_replace([' ', '.'], '', $valor);
    return (float) str_replace(',', '.', $clean);
  }
}
