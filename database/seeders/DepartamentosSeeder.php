<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Departamento;
use Illuminate\Support\Facades\DB;


class DepartamentosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('departamentos')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            DB::table('departamentos')->insert([
            ['name' => 'Comercial'],
            ['name' => 'Oficina'],
            ['name' => 'Peças'],
            ['name' => 'Administrativo'],
        ]);


    }
}
