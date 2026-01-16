<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// Spatie
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    // tabla roles
    protected $roles = [
        'client',
        'seller',
        'user',
        'guest',
        'writer',
        'editor',
        'moderator',
        'admin',
        'super-admin',
        // sistemas
        'Blog',
        'Banca',
        'JobTime',
    ];
    // tabla permisos
    protected $permissions1 = ['access'];
    protected $permissions2 = [
        // CRUD
        'create',
        'read',
        'update',
        'delete',
    ];
    protected $permissions3 = [
        // otros
        'publish',
        'unpublish',
        'printer',
        'export',
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // crea todos los roles
        foreach ($this->roles as $rol) {
            Role::create(['name' => $rol]);
        }
        // crea los permisos
        $permission = array_merge($this->permissions1, $this->permissions2, $this->permissions3);
        foreach (Role::all() as $key => $rol) {
            // dump($rol);
            foreach ($permission as $p) {
                $existe = false;
                if ($rol->name == 'client' || $rol->name == 'seller' || $rol->name == 'user') {
                    $name = $rol->name . ' ' . $p;
                } else {
                    $name = $p;
                }
                $existe = Permission::where('name', $name)->get();
                // dump($name, $existe->count());
                if (!$existe->count()) {
                    Permission::create(['name' => $name]);
                }
                // dump($name);
            }
        }

        // asigna permisos a los roles
        foreach (Role::all() as $role) {
            // asigna permisos a roles
            if ($role->name == 'admin') {
                // dd($role);
                $role->syncPermissions(Permission::all());
            } elseif ($role->name == 'client' || $role->name == 'seller' || $role->name == 'user') {
                $role->syncPermissions(Permission::where('name', 'like', "%$role->name%")->get());
                // dd($role);
            } else {
                $role->syncPermissions(array_merge($this->permissions1, $this->permissions2));
                // dd($role);
            }
        }

        // if ($role->name == 'admin' || $role->name == 'user') {
        //     $role->syncPermissions(Permission::all());
        // } elseif ($role->name == 'user') {
        //     $role->syncPermissions(Permission::where('name', 'like', "%user%")->get());
        // } elseif ($role->name == 'cliente') {
        //     $role->syncPermissions(Permission::where('name', 'like', "%cliente%")->get());
        // } elseif ($role->name == 'vendedor') {
        //     $role->syncPermissions(Permission::where('name', 'like', "%vendedor%")->get());
        // } elseif (substr($role->name, 0, 4) == 'sys_') {
        //     $role->syncPermissions("access");
        // } else {
        //     $role->syncPermissions(array_rand($perm, rand(1, 3)));
        // }

        // $role = Role::create(['name' => 'writer']);
        // $permission = Permission::create(['name' => 'edit articles']);
        // Se puede asignar un permiso a un rol mediante uno de estos métodos:

        // $role->givePermissionTo($permission);
        // $permission->assignRole($role);
        // Se pueden sincronizar varios permisos con un rol mediante uno de estos métodos:

        // $role->syncPermissions($permissions);
        // $permission->syncRoles($roles);
        // Se puede eliminar un permiso de un rol mediante uno de estos métodos:

        // $role->revokePermissionTo($permission);
        // $permission->removeRole($role);
    }
}
