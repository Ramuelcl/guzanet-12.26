<?php

namespace Database\Seeders;

use App\Models\backend\Direccion;
use Illuminate\Database\Seeder;

class DireccionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Direccion::factory(100)->create();

    }
}
