<?php
// C:\laragon\www\laravel\guzanet-12.26\app\Services\BankParserService.php

namespace App\Services;

use Illuminate\Support\BinaryCodec;
use Illuminate\Support\Facades\Log;

class BankParserService {
  private array $lineas = [];
  private string $text = '';
  private array $configBanca = [];
  private array $resultado = [];
  private array $reglas = [];

  public function __construct() {
    $this->configBanca = config('banca.pdf') ?? [];
    $this->inicializarLogger();

    // Verificar configuración
    if (empty($this->configBanca)) {
      throw new \Exception("Configuración de banca no encontrada. Verifica config/banca.php");
    }

    Log::channel('parser')->info('BankParserService inicializado');
  }
  private function inicializarLogger(): void {
    // Crear canal de log específico para el parser
    config(['logging.channels.parser' => [
      'driver' => 'single',
      'path' => storage_path('logs/parser.log'),
      'level' => env('LOG_LEVEL', 'debug'),
    ]]);
  }
  /**
   * Procesa el texto del PDF de forma secuencial
   */
  public function parseBancas(string $text): array {
    $this->text = $text;
    $this->prepararLineas($this->text);
    $this->resultado = [];

    foreach ($this->configBanca as $bancoKey => $reglas) {
      try {
        return $this->procesarSecuencialmente($reglas);
      } catch (\Exception $e) {
        Log::warning("Error con configuración $bancoKey: " . $e->getMessage());
        continue;
      }
    }

    throw new \Exception("No se pudo procesar el documento");
  }

  /**
   * Procesa el documento línea por línea de forma secuencial
   */
  private function procesarSecuencialmente(array $reglas): array {
    // Paso 1: Información del banco
    $releveNumero = $this->extraerCampo($this->text, $reglas['releve_numero']);
    $nombreBanco = 'La Banque Postale'; // Hardcodeado para este banco

    // Paso 2: Información del cliente
    $idCliente = $this->extraerCampo($this->text, $reglas['titular']['id_cliente']);
    $nombreCliente = $this->extraerCampo($this->text, $reglas['titular']['nombre']);
    $direccionCliente = $this->extraerCampo($this->text, $reglas['titular']['direccionCliente'] ?? '/N/A/');

    // Limpiar y parsear dirección
    $direccionCliente = $this->limpiarDireccion($direccionCliente);
    $direccionParseada = $this->parsearDireccion($direccionCliente, $reglas['direccion_parseada'] ?? []);

    // Fecha del relevé
    $fechaMatches = [];
    $releveFecha = now()->format('d/m/Y');
    if (preg_match($reglas['releve_fecha'], $this->text, $fechaMatches)) {
      $releveFecha = $this->convertirFechaFrancesa($fechaMatches[1], $fechaMatches[2], $fechaMatches[3]);
    }

    // Inicializar estructura base
    $this->resultado = [
      'releveNumero' => $releveNumero,
      'releveFecha' => $releveFecha,
      'nombreBanco' => $nombreBanco,
      'idCliente' => $idCliente,
      'nombreCliente' => $nombreCliente,
      'direccionCliente' => $direccionCliente,
      'numero_y_calle' => $direccionParseada['numero_y_calle'],
      'codigo_postal' => $direccionParseada['codigo_postal'],
      'ciudad' => $direccionParseada['ciudad'],
      'cuentas' => [], // Se llenará secuencialmente
    ];
    // dd($this->resultado);
    // Paso 3-5: Procesar cuentas secuencialmente
    $this->procesarCuentasSecuencialmente($reglas);

    return $this->resultado;
  }

  /**
   * Procesa cuentas de forma secuencial
   */
  private function procesarCuentasSecuencialmente(array $reglas): void {
    // POSIBLE ERROR: Si no hay líneas con "CCP" o "Livret A", el array $cuentas queda vacío
    // SOLUCIÓN: Agregar validación al inicio
    if (empty($this->lineas)) {
      Log::warning("No hay líneas para procesar");
      return;
    }
    $indice = 0;
    $totalLineas = count($this->lineas);

    $iban = $reglas['detalles']['iban'] ?? null;
    $bic = $reglas['detalles']['bic'] ?? null;
    $cuentaActual = null;
    $enOperaciones = false;
    $operacionesActuales = [];

    while ($indice < $totalLineas) {
      foreach ($reglas['cuentas'] as $cuentaKey => $cuentaConfig) {
        $this->reglas =  [$reglas['detalles'], $reglas['operaciones']]; // Guardar reglas para debug
        // dd($this->reglas);

        // dd(['cuentaKey' => $cuentaKey, 'cuentaConfig' => $cuentaConfig,]);

        // Paso 3: Buscar nueva cuenta
        // dump([
        //   'indice' => $indice,
        //   'cuentaKey' => $cuentaKey,
        //   $iban,
        //   $bic,
        // ]);
        $nuevaCuenta = $this->detectarNuevaCuenta($this->lineas, $indice, $cuentaConfig['regex']);
        dump([
          'indice' => $indice,
          'cuentaKey' => $cuentaKey,
          'nuevaCuenta' => $nuevaCuenta,
        ]);

        if ($nuevaCuenta) {
          $nuevaCuenta['label'] = $cuentaConfig['label'];
          // Buscar IBAN para esta cuenta
          $nuevaCuenta['iban'] = $this->detectarIbanBic($this->lineas, $indice, $iban);

          // Buscar BIC para esta cuenta
          $nuevaCuenta['bic'] = $this->detectarIbanBic($this->lineas, $indice, $bic);

          // dd($nuevaCuenta, $indice);

          $operaciones = [];

          Log::info("cuenta detectada: {$nuevaCuenta['label']} - {$nuevaCuenta['numero']}");

          // Parsear línea de operación
          $operaciones = $this->parsearOperaciones($this->lineas, $indice);
          $nuevaCuenta['operaciones'] = $operaciones;
        }
        Log::info("Última cuenta guardada: {$nuevaCuenta['label']}");
      }
    }
  }

  /**
   * Detecta si una línea es el inicio de una nueva cuenta
   */
  private function detectarNuevaCuenta(array $lineas, int &$indice, string $regex): ?array {
    // Buscar desde la posición actual del índice hasta el final
    for (; $indice < count($lineas); $indice++) {
      $linea = $lineas[$indice];

      if (preg_match($regex, $linea, $matches)) {
        $numero = isset($matches[1]) ? str_replace(' ', '', trim($matches[1])) : 'N/A';
        $saldoTexto = isset($matches[2]) ? trim(str_replace(' ', '', trim($matches[2]))) : '0,00';

        // Incrementamos después de procesar porque queremos que la próxima llamada empiece desde la siguiente línea
        $indice++;
        // dd([
        //   'indice' => $indice,
        //   'numero' => $numero,
        //   'saldoTexto' => $saldoTexto,
        // ]);
        return [
          'numero' => $numero,
          'saldo' => $saldoTexto,
        ];
      }
    }

    return null;
  }
  private function detectarIbanBic(array $lineas, int $indice, string $regex): string {
    for (; $indice < count($lineas); $indice++) {
      $linea = $lineas[$indice];
      if (preg_match($regex, $linea, $matches)) {
        return trim($matches[1]);
      }
    }
    return 'N/A';
  }

  /**
   * Detecta inicio de sección de operaciones
   */
  private function esInicioOperaciones(array $lineas, int &$indice, array $patronesInicio): int {
    foreach ($patronesInicio as $patron) {
      $paso = preg_match($patron, $lineas[$indice]);
      dd([
        'indice' => $indice,
        'linea' => $lineas[$indice],
        'patron' => $patron,
        'paso' => $paso,
      ]);
      if ($paso) {
        return $indice;
      }
    }

    return 1;
  }

  /**
   * Detecta fin de sección de operaciones
   */
  private function esFinOperaciones(string $linea): bool {
    $patronesFin = [
      '/^Total des opérations/i',
      '/^Nouveau solde/i',
      '/^Ancien solde/i',
      '/^\s*$/', // Línea vacía
      '/^Situation de vos comptes/i',
    ];

    foreach ($patronesFin as $patron) {
      if (preg_match($patron, $linea)) {
        return true;
      }
    }

    return false;
  }

  // POSIBLE ERROR: Diferentes formatos de operaciones
  // SOLUCIÓN: Múltiples patrones de parseo
  private function parsearOperaciones(array $lineas, int &$indice): ?array {
    // Lista de patrones en orden de prioridad
    $patrones = [
      // Patrón 1: "05/01PRELEVEMENT DE SFR5,99-39,29"=>05/01 =>PRELEVEMENT DE SFR =>5,99 =>-39,29
      '/^(\d{2}\/\d{2})(.+?)([\d\s\.,]+)\s+([+-]?[\d\s\.,]+)$/',

      // Patrón 2: "05/01  PRELEVEMENT DE SFR  5,99  -39,29"
      '/^(\d{2}\/\d{2})\s+(.+?)\s+([\d\s\.,]+)\s+([+-]?[\d\s\.,]+)$/',

      // Patrón 3: Con columnas separadas
      '/^(\d{2}\/\d{2})\s+(.+?)\s+([\d\s\.,]+)\s+([\d\s\.,]+)?\s+([+-]?[\d\s\.,]+)$/',

      // Patrón 4: Solo descripción (líneas de referencia)
      '/^(\d{2}\/\d{2})\s+(.+)$/',
    ];
    $operaciones = [];
    $indice = $this->esInicioOperaciones($lineas, $indice, $this->reglas['operaciones']['inicio']);
    dd([
      'indice' => $indice,
      'lineas' => array_slice($lineas, $indice, 10), // Mostrar las próximas 10 líneas para contexto
    ]);
    while (!$this->esFinOperaciones($lineas[$indice])) {
      dump([
        'indice' => $indice,
        'linea' => $lineas[$indice],
      ]);

      foreach ($patrones as $patronIndex => $patron) {
        if (preg_match($patron, $lineas[$indice], $matches)) {
          Log::debug("Operación parseada con patrón $patronIndex: $lineas[$indice]");

          $fecha = trim($matches[1]) . '/2017'; // TODO: Determinar año dinámicamente
          $descripcion = trim($matches[2]);

          // Determinar montos según el patrón
          $debito = 0;
          $credito = 0;
          $saldo = 0;

          switch ($patronIndex) {
            case 0: // "5,99-39,29"
            case 1:
              $montoRaw = trim($matches[3]);
              $saldoRaw = trim($matches[4]);
              $monto = $this->limpiarSaldo($montoRaw);
              $saldo = $this->limpiarSaldo($saldoRaw);

              $debito = $saldo < 0 ? abs($monto) : 0;
              $credito = $saldo > 0 ? $monto : 0;
              break;

            case 2: // Débito y crédito separados
              $debitoRaw = trim($matches[3]);
              $creditoRaw = isset($matches[4]) ? trim($matches[4]) : '0';
              $saldoRaw = trim($matches[5]);

              $debito = $this->limpiarSaldo($debitoRaw);
              $credito = $this->limpiarSaldo($creditoRaw);
              $saldo = $this->limpiarSaldo($saldoRaw);
              break;

            case 3: // Solo descripción
              return [
                'Date' => $fecha,
                'Opérations' => $descripcion,
                'Débit' => 0,
                'Crédit' => 0,
                'francs' => 0,
                'tipo' => 'informacion',
                'importe' => 0,
              ];
          }
          $operaciones[] =   [
            'Date' => $fecha,
            'Opérations' => $descripcion,
            'Débit' => $debito,
            'Crédit' => $credito,
            'francs' => $saldo,
            'tipo' => $debito > 0 ? 'debito' : ($credito > 0 ? 'credito' : 'neutro'),
            'importe' => $debito > 0 ? $debito : $credito,
          ];
        }

        Log::debug("No se pudo parsear operación: $lineas[$indice] con patrón $patronIndex");
        return null;
      }
      $indice++;
    }
    return $operaciones;
  }


  private function limpiarSaldo(string $saldo): float {
    $limpio = str_replace([' ', '.'], '', $saldo);
    $limpio = str_replace(',', '.', $limpio);
    return floatval($limpio);
  }

  /**
   * Limpia una dirección
   */
  private function limpiarDireccion(string $direccion): string {
    if ($direccion === 'N/A') return $direccion;

    $patronesLimpiar = [
      '/Situation\s+de\s+vos\s+comptes.*$/i',
      '/au\s+\d{1,2}\s+\w+\s+\d{4}.*$/i',
      '/\s+Solde.*$/i',
    ];

    $limpia = trim($direccion);
    foreach ($patronesLimpiar as $patron) {
      $limpia = preg_replace($patron, '', $limpia);
    }

    return preg_replace('/\s+/', ' ', trim($limpia));
  }

  // POSIBLE ERROR: Dirección en múltiples formatos
  // SOLUCIÓN: Regex más flexibles
  private function parsearDireccion(string $direccion, array $config): array {
    $resultado = [
      'numero_y_calle' => $direccion,
      'codigo_postal' => '',
      'ciudad' => ''
    ];

    if ($direccion === 'N/A' || empty(trim($direccion))) {
      return $resultado;
    }

    // Intentar múltiples patrones para código postal
    $patronesCP = [
      '/\b(\d{5})\b/', // 75016
      '/\b(\d{2}\s?\d{3})\b/', // 75 016
      '/CP[\s:]*(\d{5})/i', // CP: 75016
      '/Postal[\s:]*(\d{5})/i', // Postal: 75016
    ];

    foreach ($patronesCP as $patron) {
      if (preg_match($patron, $direccion, $matches)) {
        $resultado['codigo_postal'] = str_replace(' ', '', trim($matches[1]));
        break;
      }
    }

    // Extraer ciudad (buscar después del código postal)
    if ($resultado['codigo_postal']) {
      $partes = explode($resultado['codigo_postal'], $direccion, 2);
      if (isset($partes[1])) {
        // Tomar la primera palabra después del CP como ciudad
        $despuesCP = trim($partes[1]);
        if (preg_match('/^([A-ZÀ-ÿ\s]+)/u', $despuesCP, $ciudadMatches)) {
          $resultado['ciudad'] = trim($ciudadMatches[1]);

          // Limpiar texto adicional (ej: "PARIS Situation de vos comptes")
          if (preg_match('/^([A-ZÀ-ÿ]+)\b/u', $resultado['ciudad'], $ciudadLimipia)) {
            $resultado['ciudad'] = $ciudadLimipia[1];
          }
        }
      }
    }

    // Calcular número y calle
    if ($resultado['codigo_postal'] && $resultado['ciudad']) {
      $sinCP = preg_replace('/\s*' . preg_quote($resultado['codigo_postal'], '/') . '\s*/', ' ', $direccion);
      $sinCiudad = preg_replace('/\s*' . preg_quote($resultado['ciudad'], '/') . '\s*/', ' ', $sinCP);
      $resultado['numero_y_calle'] = trim(preg_replace('/\s+/', ' ', $sinCiudad));
    }

    return $resultado;
  }

  /**
   * Métodos auxiliares (mantener los que ya tienes)
   */
  private function prepararLineas(string $text): void {
    $text = str_replace(["\t", "\r", "\xc2\xa0", "\xa0"], [" ", "", " ", " "], $text);
    $this->lineas = array_values(array_filter(array_map('trim', explode("\n", $text))));
    Log::info("Total líneas: " . count($this->lineas));
  }

  private function extraerCampo(string $text, string $regex): string {
    if (preg_match($regex, $text, $matches)) {
      return trim(end($matches));
    }
    return 'N/A';
  }

  // POSIBLE ERROR: Las fechas en el PDF pueden estar en diferente formato
  // SOLUCIÓN: Agregar múltiples formatos de fecha
  private function convertirFechaFrancesa(string $dia, string $mesPalabra, string $anio): string {
    // Validar que los parámetros no estén vacíos
    if (empty($dia) || empty($mesPalabra) || empty($anio)) {
      Log::error("Parámetros de fecha vacíos: dia=$dia, mes=$mesPalabra, anio=$anio");
      return now()->format('d/m/Y');
    }

    $meses = [
      'janvier' => '01',
      'février' => '02',
      'mars' => '03',
      'avril' => '04',
      'mai' => '05',
      'juin' => '06',
      'juillet' => '07',
      'août' => '08',
      'septembre' => '09',
      'octobre' => '10',
      'novembre' => '11',
      'décembre' => '12',
      // Variantes posibles
      'jan' => '01',
      'fév' => '02',
      'mar' => '03',
      'avr' => '04',
      'juil' => '07',
      'sept' => '09',
      'oct' => '10',
      'nov' => '11',
      'déc' => '12'
    ];

    $mesPalabraLower = strtolower(trim($mesPalabra));
    $mesNumero = $meses[$mesPalabraLower] ?? '01';

    // Validar que el día sea numérico
    if (!is_numeric($dia)) {
      Log::warning("Día no numérico: $dia, usando 01");
      $dia = '01';
    }

    $diaFormateado = str_pad($dia, 2, '0', STR_PAD_LEFT);

    return "$diaFormateado/$mesNumero/$anio";
  }
}
