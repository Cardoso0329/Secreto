<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SLASeeder extends Seeder
{
    public function run(): void
    {

        DB::table('slas')->insert([
            ['name' => 'Prioritário - 2h'],
            ['name' => 'Urgente - 4h'],
            ['name' => 'Importante - 8h'],
            ['name' => 'A resolver - 12h'],
        ]);
    }
}
