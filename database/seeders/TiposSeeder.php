<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class TiposSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
        public function run(): void
{
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('tipos')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    DB::table('tipos')->insert([
        ['name' => 'Pedido de Informação Sem Lead'],
        ['name' => 'Evento Dias Mercedes'],
        ['name' => 'Reclamação/Insatisfação'],
        ['name' => 'Pedido de Contacto'],
        ['name' => 'Pedido de Informação'],
        ['name' => 'Pedido de Marcação'],
        ['name' => 'Pedido de Orçamento'],
        ['name' => 'Tomada de Conhecimento'],

    ]);

}

}
