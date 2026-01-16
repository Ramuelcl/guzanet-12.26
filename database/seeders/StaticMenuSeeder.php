<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\backend\Menu;
use Illuminate\Support\Facades\DB;

class StaticMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asegurar que el contador para el tipo 'main' existe
        DB::table('menu_id_counters')->updateOrInsert(
            ['menu_type' => 'main'],
            ['next_id' => 1] // Empezamos en 1 o el número que corresponda
        );

        Menu::updateOrCreate(
            ['name' => 'Acerca de', 'type' => 'main'],
            [
                'url' => 'acercade',
                'is_active' => true,
                'order' => 100,
            ]
        );

        Menu::updateOrCreate(
            ['name' => 'Contacto', 'type' => 'main'],
            [
                'url' => 'contacto',
                'is_active' => true,
                'order' => 101,
            ]
        );
    }
}
