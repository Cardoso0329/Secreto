<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SetoresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('setores')->insert([
            // Subsetores de Vendedores
            ['name' => 'Usados', 'parent_id' => null],
            ['name' => 'Novos VLP', 'parent_id' => null],
            ['name' => 'Novos VCL', 'parent_id' => null],
            ['name' => 'Novos Smart', 'parent_id' => null],
            ['name' => 'Novos VCP', 'parent_id' => null],

            // Subsetores de Oficina
            ['name' => 'Colisão', 'parent_id' => null],
            ['name' => 'APV - VLP', 'parent_id' => null],
            ['name' => 'APV - VCL', 'parent_id' => null],
            ['name' => 'APV - VCP', 'parent_id' => null],
            ['name' => 'Peças', 'parent_id' => null],
            ['name' => 'VCL', 'parent_id' => null],
            ['name' => 'Oficina VLP', 'parent_id' => null],
            ['name' => 'Oficina Smart', 'parent_id' => null],
            ['name' => 'Oficina VCL', 'parent_id' => null],
            ['name' => 'Oficina VCP', 'parent_id' => null],
            ['name' => 'Oficina Colisão', 'parent_id' => null],
            ['name' => 'Marcações VLP', 'parent_id' => null],
            ['name' => 'Marcações Smart', 'parent_id' => null],
            ['name' => 'Marcações VCL', 'parent_id' => null],
            ['name' => 'Marcações VCP', 'parent_id' => null],
            ['name' => 'Marcações Colisão', 'parent_id' => null],
            ['name' => 'Orçamentos VLP', 'parent_id' => null],
            ['name' => 'Orçamentos Smart', 'parent_id' => null],
            ['name' => 'Orçamentos VCL', 'parent_id' => null],
            ['name' => 'Orçamentos VCP', 'parent_id' => null],
            ['name' => 'Orçamentos Colisão', 'parent_id' => null],

            // Subsetores de Backoffice
            ['name' => 'Financiamento', 'parent_id' => null],
            ['name' => 'Recursos Humanos', 'parent_id' => null],
            ['name' => 'Informática', 'parent_id' => null],
            ['name' => 'Administração', 'parent_id' => null],
            ['name' => 'Jurídico', 'parent_id' => null],
            ['name' => 'RAC', 'parent_id' => null],
            ['name' => 'Marketing', 'parent_id' => null],
            ['name' => 'Contabilidade', 'parent_id' => null],
        ]);
    }
}
