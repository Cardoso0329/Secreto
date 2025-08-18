<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Estado;
use Illuminate\Support\Facades\DB;


class EstadosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
            public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('estados')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('estados')->insert([
            ['name' => 'Aguardar'],
            ['name' => 'Pendente'],
            ['name' => 'Tratado'],
        ]);
    }

}
