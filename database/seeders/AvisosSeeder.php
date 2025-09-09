<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AvisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('avisos')->insert([
            ['name' => '1º aviso'],
            ['name' => '2º aviso'],
            ['name' => '3º aviso'],
            ['name' => '4º aviso'],
        ]);
    }
}
