<?php

namespace Database\Seeders;

use App\Models\backend\Tabla;
use Illuminate\Database\Seeder;

class TablaSeeder extends Seeder
{
  private $tabla_id_counter = 1; // Contador global para tabla_id
  private $indTabla; // Indice para la tabla

  public function run()
  {
    // Menus  acerca de, contacto
    $this->indTabla = $i = config('constantes.MENUS');
    Tabla::create([
      'tabla' => $this->indTabla,
      'tabla_id' => 0,
      'valores' => 'menus',
      'is_active' => false,
    ]);

    // menu public
    $menus = [
      // 'tabla',
      // 'id',
      // 'parent',
      // 'route',
      // 'icon',
      // 'active',
      // 'disabled',
      // 'submenu',
      'About' =>
      [
        'route' => 'acercade',
        'icon' => '',

      ],
      'Contact' =>
      [
        'route' => 'contacto',
        'icon' => '',
      ],
    ];
    $this->seedMenu($menus);

    // menu usuario
    $this->indTabla = $i + 10;
    $menus = [
      'Register' =>
      [
        'route' => 'register',
        'icon' => '',
      ],
      'Login' =>
      [
        'route' => 'login',
        'icon' => '',
      ],
      'Logout' =>
      [
        'route' => 'logout',
        'icon' => '',
      ],
    ];
    $this->seedMenu($menus);

    // menu usuarios
    $this->indTabla = $i + 20;
    $menus = [
      'Users' =>
      [
        'route' => 'users',
        'icon' => 'users',
        'subMenu' => [
          'Roles' =>
          [
            'parent' => $this->indTabla,
            'route' => 'user.roles.index',
            'icon' => '',
          ],
          'Permis' =>
          [
            'parent' => $this->indTabla,
            'route' => 'user.permis.index',
            'icon' => '',
          ],
        ],
      ],
    ];
    $this->seedMenu($menus);

    $this->indTabla = $i + 30;
    $menus = [
      'Users' => [
        'icono' => 'users',
        'route' => 'users.index',
        'descripcion' => 'List users',
        'subMenu' => [
          'Roles' => [
            'icono' => 'users',
            'route' => 'roles.index',
            'descripcion' => 'Manage roles',
            'subMenu' => [
              'Crear Rol' =>
              [
                'icono' => 'users',
                'route' => 'roles.create',
                'descripcion' => 'Create roles',
              ],
              'Editar Roles' =>
              [
                'icono' => 'users',
                'route' => 'roles.edit',
                'descripcion' => 'Edit roles',
              ],
            ],
          ],
          'Permisos' =>
          [
            'icono' => 'users',
            'route' => 'permisos.index',
            'descripcion' => 'Manage permissions',
            'subMenu' => [
              'Crear Permiso' =>
              [
                'descripcion' => 'Create permission',
                'icono' => 'users',
                'route' => 'permisos.create',
              ],
              'Editar Permisos' =>
              [
                'descripcion' => 'edit permissions',
                'icono' => 'users',
                'route' => 'permisos.edit',
              ],
            ],
          ],
        ],
      ],
    ];
    $this->seedMenu($menus);

    // Profesiones
    $this->indTabla =  config('constantes.PROFESIONES');
    $opciones = ['profesiones', 'Administrador', 'Medico', 'Abogado', 'Doctor', 'Empresario', 'Dibujante', 'Arquitecto', 'Analista', 'Programador', 'Enfermera', 'Contador', 'Profesor', 'Sin Profesion'];
    $this->genera($opciones, count($opciones));

    // opciones Si/No
    $this->indTabla =  config('constantes.OPCIONES_SI_NO');
    $opciones = ['Opciones', 'Si', 'No', 'Otro'];
    $this->genera($opciones, count($opciones));

    // Sexo
    $this->indTabla =  config('constantes.SEXO');
    $opciones = ['Sexo', 'Masculino', 'Femenino', 'Otro',];
    $this->genera($opciones, count($opciones));

    // Monedas
    $this->indTabla =  config('constantes.MONEDAS');
    $opciones = ['Monedas', '$', '€', '£', 'Otro',];
    $this->genera($opciones, count($opciones));

    // Medidas
    $this->indTabla =  config('constantes.UNIDADES_MEDIDAS');
    $opciones = ['Medidas', 'Un', 'Lts', 'Kg', 'Mts', 'year', 'month', 'day', 'hour', 'min', 'sec', '-'];
    $this->genera($opciones, count($opciones));

    // Tipo de datos
    $this->indTabla =  config('constantes.TIPO_DATOS');
    $opciones = ['Tipo Datos', 'casa', 'trabajo', 'particular', 'Empresa', 'otro'];
    $this->genera($opciones, count($opciones));

    // Tipo de telefono
    $this->indTabla =  config('constantes.TIPO_TELEFONO');
    $opciones = ['Tipo Telefono', 'Mobile', 'Fijo', 'Trabajo', 'Fax', 'otro'];
    $this->genera($opciones, count($opciones));

    // Tipo entidad
    $this->indTabla =  config('constantes.TIPO_ENTIDAD');
    $opciones = ['Entidades', 'Perfil', 'Cliente', 'Vendedor', 'Trabajador', 'particular', 'otro'];
    $this->genera($opciones, count($opciones));

    // Tipo bancario
    $this->indTabla =  config('constantes.BANCARIO');
    $opciones = ['pagos de clientes', 'REIGN', 'DIOURON', 'PUVIS', 'AC2 PRODUCTION', 'otro'];
    $this->genera($opciones, count($opciones));

    // Tipo bancario
    $this->indTabla += 1;
    $opciones = ['movimientos personales', 'CRUCELISA ARISTIZABAL', 'REGINA', 'MUNOZ ALBUERNO', 'otro'];
    $this->genera($opciones, count($opciones));

    // Tipo bancario
    $this->indTabla += 1;
    $opciones = ['proveedores', 'Navigo', 'SFR', 'Google YouTube', 'Orange', 'Samsung', 'Sosh', 'Free', 'otro'];
    $this->genera($opciones, count($opciones));
    // Tipo bancario
    $this->indTabla += 1;
    $opciones = ['La Poste', 'FORMULE DE COMPTE', 'FORFAITAIRE TRIMESTRIEL',  'otro'];
    $this->genera($opciones, count($opciones));
    // Tipo bancario
    $this->indTabla += 1;
    $opciones = ['IMPOTS', 'DIRECTION GENERAL', 'otro'];
    $this->genera($opciones, count($opciones));
    // Tipo bancario
    $this->indTabla += 1;
    $opciones = ['Compras Carte Blue', 'ACHAT CB', 'AMAZON', 'CDISCOUNT',  'otro'];
    $this->genera($opciones, count($opciones));
  }

  private function genera($opciones, int $sizeOf)
  {
    foreach ($opciones as $key => $value) {
      Tabla::create([
        'tabla' => $this->indTabla,
        'tabla_id' => $key == $sizeOf - 1 ? 99 : $key,
        'valores' => $key > 0 ? [
          'nombre' => $value,
        ] : $value,
        'is_active' => $key ? true : false,
      ]);
    }
  }
  private function seedMenu(array $menus, int $parent_id = 0, int $level = 0)
  {
    if ($parent_id == 0)
      $this->tabla_id_counter = 1;
    foreach ($menus as $index => $menu) {
      $tabla_id = $this->tabla_id_counter++;
      dump("tabla: $this->indTabla, tabla_id: $tabla_id, parent_id: $parent_id, index: $index");

      Tabla::create([
        'tabla' => $this->indTabla,
        'tabla_id' => $menu['tabla_id'] ?? $tabla_id,
        'valores' => [
          'titulo' => $index,
          'descripcion' => $menu['descripcion'] ?? null,
          'icono' => $menu['icono'] ?? null,
          // 'http' => $menu['http'] ?? null,
          'route' => $menu['route'] ?? null,
          'parent_id' => $parent_id ?? null,
          'disabled' => $menu['disabled'] ?? false,
        ],
        'is_active' => $menu['is_active'] ?? true,
      ]);

      if (isset($menu['subMenu']) && !empty($menu['subMenu'])) {
        $this->seedMenu($menu['subMenu'], $tabla_id, $level + 1);
      }
    }
  }
}
