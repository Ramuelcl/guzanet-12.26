<?php

namespace Database\Seeders;

use App\Models\backend\Pais;
use Illuminate\Database\Seeder;

class PaisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Pais::factory()->make();
        
    }
}
