<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartamentosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('departamentos')->insert([
            ['name' => 'Comercial'],
            ['name' => 'Oficina'],
            ['name' => 'Peças'],
            ['name' => 'Administrativo'],
        ]);
    }
}
