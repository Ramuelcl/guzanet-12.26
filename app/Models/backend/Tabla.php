<?php
// app/Models/backend/Tabla.php

namespace App\Models\backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tabla extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tablas';

    // Menús cargados en la instancia actual del modelo
    private $menus = [];

    protected $guarded = [];

    protected $casts = [
        'tabla' => 'integer',
        'tabla_id' => 'integer',
        'is_active' => 'boolean',
        'valores' => 'array', // Decodifica automáticamente los valores JSON
    ];

    /**
     * Obtiene el menú de una tabla específica.
     *
     * @param int|null $tabla
     * @return array
     */
    public function getMenu($tabla = null): array
    {
        // Retornar desde el caché local si ya fue cargado
        if (isset($this->menus[$tabla])) {
            return $this->menus[$tabla];
        }

        // Cargar los menús si no están en caché
        $this->loadMenus($tabla);

        // Retornar el menú específico o un arreglo vacío si no existe
        return $this->menus[$tabla] ?? [];
    }

    /**
     * Carga todos los menús para una tabla específica.
     *
     * @param int|null $tabla
     */
    private function loadMenus($tabla = null): void
    {
        // Inicializar el arreglo de menús
        $this->menus = [];

        // Consulta los datos de la tabla
        $results = $this->where('tabla', $tabla)
            ->where('is_active', true)
            ->orderBy('tabla', 'asc')
            ->orderBy('tabla_id', 'asc')
            ->select('tabla', 'tabla_id as id', 'valores')
            ->get();

        // Organizar los resultados en la estructura deseada
        foreach ($results as $menu) {
            $tablaKey = $menu->tabla;
            $menuData = $menu->valores;
            $menuData['id'] = $menu->id;

            // Agregar el rol según las condiciones
            $menuData['rol'] = $this->determineRole( $tabla);
            // dd($menuData,'nada');

            // Crear la entrada para la tabla si no existe
            if (!isset($this->menus[$tablaKey])) {
                $this->menus[$tablaKey] = [];
            }

            // Agregar submenú si tiene un padre
            if (!empty($menuData['parent_id'])) {
                $parentId = $menuData['parent_id'];

                // Asegurar la existencia de la estructura de submenús
                if (!isset($this->menus[$tablaKey][$parentId]['submenu'])) {
                    $this->menus[$tablaKey][$parentId]['submenu'] = [];
                }

                $this->menus[$tablaKey][$parentId]['submenu'][] = $menuData;
            } else {
                // Agregar como menú principal
                $this->menus[$tablaKey][$menu->id] = $menuData;
            }
        }

        // Limpiar los submenús y asegurarse de que sean arreglos numerados
        foreach ($this->menus as $tablaKey => $menuGroup) {
            foreach ($menuGroup as $menuId => $menuData) {
                if (isset($menuData['submenu'])) {
                    $this->menus[$tablaKey][$menuId]['submenu'] = array_values($menuData['submenu']);
                }
            }
        }
    }

    private function determineRole( $table=10030)
    {
        if (in_array($table, [10030, 10050])) {
            return 'admin';
        }
        // Agrega más condiciones para otros roles si es necesario
        return null;
    }

    /**
     * Accesor para la columna 'valores'.
     *
     * @param mixed $value
     * @return array
     */
    public function getValoresAttribute($value): array
    {
        return json_decode($value, true) ?? [];
    }

    /**
     * Mutador para la columna 'valores'.
     *
     * @param mixed $value
     */
    public function setValoresAttribute($value): void
    {
        $this->attributes['valores'] = json_encode($value);
    }

  public function qTablas($table, $excluirUltimo = true, $campos = ['tabla_id', 'valores']) // nunca excluye el ultimo campo=99
  {
    // Obtén los registros filtrados por los campos solicitados
    $registros = Tabla::where('tabla', $table)
      ->where('is_active', true)
      ->select($campos)
      ->get()->toArray();
    // Inicializa el arreglo de resultados
    // dd($registros);
    $resultados = [];
    if ($excluirUltimo)
      $paso =  array_pop($registros);
    // dd($registros);
    // Verifica si se encontraron registros)
    if (!empty($registros)) {
      // Procesa cada registro
      $item = [];
      foreach ($registros as $registro) {
        $tabla_id = null;
        // Itera sobre $registro ya que es siempre un array
        foreach ($registro as $ind => $valor) {
          // dump($ind, $valor);
          if ($ind == 'valores') {
            foreach ($valor as $key => $value) {
              $item[$tabla_id] = $value;
            }
          } else {
            // 'tabla_id' debe mantener su indice original
            $tabla_id = $valor;
          }
        }
        // Agrega el registro procesado al arreglo de resultados
      }
      $resultados = $item;
    }
    // Retorna el arreglo con el formato solicitado
    return $resultados;
  }
}