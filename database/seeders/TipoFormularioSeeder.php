<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoFormularioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('tipo_formularios')->insert([
            ['name' => 'Call Center'],
            ['name' => 'Central'],
        ]);
    }
}
