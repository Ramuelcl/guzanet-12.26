<?php

namespace Database\Seeders;

use App\Models\backend\Perfil;
use App\Models\backend\UserSetting;
use App\Models\User;

// agregamos
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
// Spatie
// use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
// use Spatie\Permission\Models\Permission;
// use Spatie\Permission\PermissionRegistrar;
// use Spatie\Permission\Models\model_has_roles;
// use Spatie\Permission\Models\model_has_permissions;

class UserSeeder extends Seeder
{
  // The User model requires this trait
  use HasRoles;

  public function __construct()
  {
    $users = [
      'admin' => [
        'name' => 'Admin',
        'email' => 'admin@mail.com',
        'profile_photo_path' => 'app/avatars/admin.png',
        'email_verified_at' => now(),
        'password' => Hash::make('password'), //bcrypt('0Admin')
        'remember_token' => Str::random(10),
        'is_active' => true,
      ],
      'guest' => [
        'name' => 'guest',
        'email' => 'guest@mail.com',
        'profile_photo_path' => 'app/avatars/guest.png',
        'email_verified_at' => now(),
        'password' => Hash::make('password'), //bcrypt('guest')
        'remember_token' => Str::random(10),
        'is_active' => true,
      ],
    ];
    //
    // dd($users);
    //
    foreach ($users as $key => $user) {
      $u = User::create($user);
      // dump('creando ' . $user['name']);
      $u->assignRole($key);

      //guardar un registro de configuracion para el usuario
      UserSetting::factory()->create([
        'user_id' => $u['id'],
      ]);
      Perfil::factory()->create([
        'user_id' => $u['id'],
      ]);
    }
  }

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    // $user = User::factory()
    //     // ->has(App\Models\backend\UserSetting::factory()->count(1), 'App\Models\backend\UserSetting')
    //     ->count(48)
    //     ->create();

    // factory(App\User::class, 25)->create()->each(function ($user) {
    //     $user->profile()->save(factory(App\UserProfile::class)->make());
    // });

    // $roles = Role::all()->pluck('name')->toArray();
    // User::factory(98)
    //     ->create()
    //     ->each(function ($user) use ($roles) {
    //         $user->assignRole(array_rand($roles, rand(1, 4)));

    //         App\Models\backend\UserSetting::factory()->create([
    //             'user_id' => $user->id,
    //         ]);
    //         // Perfil::factory()->create([
    //         //     'user_id' => $user->id,
    //         // ]);
    //     });

    $user=User::factory(1)
      ->create([
        'name' => 'cliente',
        'email' => 'cliente@mail.com',
      ])
      ->each(function ($user) {
        $user->assignRole('client');

        \App\Models\backend\UserSetting::factory()->create([
          'user_id' => $user->id,
        ]);
        Perfil::factory()->create([
          'user_id' => $user->id,
        ]);
      });
    $user =User::factory(1)
      ->create([
        'name' => 'job',
        'email' => 'job@mail.com',
      ])
      ->each(function ($user) {
        $user->assignRole('JobTime');

        \App\Models\backend\UserSetting::factory()->create([
          'user_id' => $user->id,
        ]);
        Perfil::factory()->create([
          'user_id' => $user->id,
        ]);
      });
    User::factory(1)
      ->create([
        'name' => 'vendedor',
        'email' => 'vendedor@mail.com',
      ])
      ->each(function ($user) {
        $user->assignRole('seller');

        \App\Models\backend\UserSetting::factory()->create([
          'user_id' => $user->id,
        ]);
        Perfil::factory()->create([
          'user_id' => $user->id,
        ]);
      });
  }
}
