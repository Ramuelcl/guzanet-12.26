<?php

namespace Database\Seeders;

use App\Models\backend\Menu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        // Limpiar la tabla antes de sembrar
        Menu::truncate();
        DB::table('menu_id_counters')->truncate();

        // Insertar contadores de ID para los tipos de menú
        DB::table('menu_id_counters')->insert([
            ['menu_type' => 'util', 'next_id' => 1000],
            ['menu_type' => 'module', 'next_id' => 1100],
            ['menu_type' => 'group', 'next_id' => 1200],
            ['menu_type' => 'setting', 'next_id' => 1300],
            ['menu_type' => 'action', 'next_id' => 1400],
            ['menu_type' => 'page', 'next_id' => 1500],
            ['menu_type' => 'link', 'next_id' => 1600],
            ['menu_type' => 'user', 'next_id' => 5000],
            ['menu_type' => 'dropdownUser', 'next_id' => 5100],
            ['menu_type' => 'sys', 'next_id' => 10000],
            ['menu_type' => 'ventas', 'next_id' => 10001],
            ['menu_type' => 'compras', 'next_id' => 10100],
            ['menu_type' => 'tClock', 'next_id' => 10200],
            ['menu_type' => 'banca', 'next_id' => 10300],
            ['menu_type' => 'proyectos', 'next_id' => 10400],
            ['menu_type' => 'entidades', 'next_id' => 10500],
            ['menu_type' => 'tasks_main', 'next_id' => 20000],
        ]);

        // Crear menús de nivel superior
        $dashboard = Menu::factory()->type('setting')->create([
            'name' => 'Dashboard',
            'url' => '/dashboard',
            'icon' => 'adjustments',
            'parent_id' => null,
            'order' => 1,
            'is_active' => true,
        ]);

        $users = Menu::factory()->type('user')->create([
            'name' => 'Usuarios',
            'url' => '/users',
            'icon' => 'user-group',
            'parent_id' => null,
            'order' => 2,
            'is_active' => true,
        ]);

        $sys = Menu::factory()->type('sys')->create([
            'name' => 'Sistemas',
            'url' => '#',
            'icon' => 'archive',
            'parent_id' => null,
            'order' => 3,
            'is_active' => true,
        ]);

        // Crear submenús para sistemas
        Menu::factory()->type('compras')->create([
            'name' => 'Compras',
            'url' => '/compras/listar',
            'icon' => 'shopping-bag',
            'parent_id' => $sys->id,
            'order' => 1,
        ]);
        Menu::factory()->type('ventas')->create([
            'name' => 'Ventas',
            'url' => '/ventas/listar',
            'icon' => 'shopping-bag',
            'parent_id' => $sys->id,
            'order' => 2,
        ]);

        Menu::factory()->type('proyectos')->create([
            'name' => 'Proyectos',
            'url' => route('proyectos.index', [], false),
            'icon' => 'briefcase',
            'parent_id' => $sys->id,
            'order' => 3,
        ]);

        Menu::factory()->type('entidades')->create([
            'name' => 'Entidades',
            'url' => route('entidades.index', [], false),
            'icon' => 'library',
            'parent_id' => $sys->id,
            'order' => 4,
        ]);

        $tClock = Menu::factory()->type('tClock')->create([
            'name' => 'tClock',
            'url' => '/tClock/listar',
            'icon' => 'clock',
            'parent_id' => $sys->id,
            'order' => 5,
        ]);

        Menu::factory()->type('tClock')->create([
            'name' => 'Otros1',
            'url' => '/tClock/otros',
            'icon' => 'clock',
            'parent_id' => $tClock->id,
            'order' => 1,
        ]);
        Menu::factory()->type('tClock')->create([
            'name' => 'Otros2',
            'url' => '/tClock/otros2',
            'icon' => 'clock',
            'parent_id' => $tClock->id,
            'order' => 2,
        ]);
        Menu::factory()->type('tClock')->create([
            'name' => 'Otros3',
            'url' => '/tClock/otros3',
            'icon' => 'clock',
            'parent_id' => $tClock->id,
            'order' => 3,
        ]);

        Menu::factory()->type('entidades')->create([
            'name' => 'Entidades',
            'url' => 'jobtime.entidades',
            'icon' => 'library',
            'parent_id' => $tClock->id,
            'order' => 4,
        ]);

        // Crear submenús para Usuarios
        Menu::factory()->type('user')->create([
            'name' => 'Listar Usuarios',
            'url' => 'users.index',
            'icon' => 'users',
            'parent_id' => $users->id,
            'order' => 1,
            'is_active' => true,
        ]);
        Menu::factory()->type('user')->create([
            'name' => 'Roles',
            'url' => '/users/roles',
            'icon' => 'user-circle',
            'parent_id' => $users->id,
            'order' => 2,
        ]);
        Menu::factory()->type('user')->create([
            'name' => 'Permisos',
            'url' => '/users/permisos',
            'icon' => 'key',
            'parent_id' => $users->id,
            'order' => 3,
        ]);

        // Menú para la aplicación de Tareas
        $tasksMain = Menu::factory()->type('tasks_main')->create([
            'name' => 'Tareas',
            'url' => route('tasks.index', [], false),
            'icon' => 'clipboard-list',
            'parent_id' => null,
            'order' => 4,
            'is_active' => true,
        ]);

        Menu::factory()->type('tasks_main')->create([
            'name' => 'Listar Tareas',
            'url' => route('tasks.index', [], false),
            'icon' => 'list-bullet',
            'parent_id' => $tasksMain->id,
            'order' => 1,
        ]);
        Menu::factory()->type('tasks_main')->create([
            'name' => 'Crear Tarea',
            'url' => 'tasks.actions.mode',
            'icon' => 'plus-circle',
            'parent_id' => $tasksMain->id,
            'order' => 2,
        ]);
    }
}