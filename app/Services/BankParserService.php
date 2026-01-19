<?php
// C:\laragon\www\laravel\guzanet-12.26\app\Services\BankParserService.php

namespace App\Services;

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
    // PROACTIVO: Extraemos los detalles generales del banco (IBAN/BIC)
    $ibanGeneral = $this->extraerCampo($text, $reglas['detalles']['iban']);
    $bicGeneral = $this->extraerCampo($text, $reglas['detalles']['bic']);
    $paso = [
      'banco_nombre_real' => $nombreBanco, // El texto extraído (Ej: LA BANQUE POSTALE)
      'banco_key' => $bancoKey,           // La llave interna (Ej: banco1)
      'cliente' => [
        'nombre' => $this->extraerCampo($text, $reglas['titular']['nombre']),
        'id'     => $this->extraerCampo($text, $reglas['titular']['id_cliente']),
      ],
      // Pasamos el IBAN y BIC extraídos al procesador de cuentas
      'cuentas' => $this->procesarCuentas($text, $reglas),
    ];
    dd($paso);
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
            ]
          ];
          break;
        }
      }
    }
    return $cuentasEncontradas;
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
