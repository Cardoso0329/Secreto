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

    DB::table('tipos')->insert([
        ['name' => 'Pedido de Informação Sem Lead'],
        ['name' => 'Reclamação/Insatisfação'],
        ['name' => 'Pedido de Contacto'],
        ['name' => 'Pedido de Informação'],
        ['name' => 'Pedido de Marcação'],
        ['name' => 'Pedido de Orçamento'],
        ['name' => 'Tomada de Conhecimento'],

    ]);

}

}
