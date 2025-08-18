<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SLA;
use Illuminate\Support\Facades\DB;


class SLASeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('slas')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            DB::table('slas')->insert([
                ['name' => 'Prioritário - 2h'],
                ['name' => 'Urgente - 4h'],
                ['name' => 'Importante - 8h'],
                ['name' => 'A resolver - 12h'],
]);


    }
}
