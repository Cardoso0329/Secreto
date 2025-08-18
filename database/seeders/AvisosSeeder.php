<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Aviso;
use Illuminate\Support\Facades\DB;


class AvisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('avisos')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

         DB::table('avisos')->insert([
            ['name' => '1º aviso'],
            ['name' => '2º aviso'],
            ['name' => '3º aviso'],
            ['name' => '4º aviso'],
         ]);


    }
}
