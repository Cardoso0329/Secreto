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
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('tipo_formularios')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('tipo_formularios')->insert([
            ['name' => 'Call Center'],
            ['name' => 'Central'],
        ]);
    }
}
