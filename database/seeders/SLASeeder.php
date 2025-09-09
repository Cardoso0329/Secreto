<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SLASeeder extends Seeder
{
    public function run(): void
    {
        // Truncate ou delete conforme o driver
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('slas')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } else {
            // Para SQLite ou outros drivers
            DB::table('slas')->delete();
        }

        DB::table('slas')->insert([
            ['name' => 'Prioritário - 2h'],
            ['name' => 'Urgente - 4h'],
            ['name' => 'Importante - 8h'],
            ['name' => 'A resolver - 12h'],
        ]);
    }
}
