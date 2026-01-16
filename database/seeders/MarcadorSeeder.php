<?php

namespace Database\Seeders;

use App\Models\backend\Marcador;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class MarcadorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $m = Marcador::factory()->make();
    }
}
