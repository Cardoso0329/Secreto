<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Origem;
use Illuminate\Support\Facades\DB;


class OrigensSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('origens')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('origens')->insert([
            ['name' => 'Telefone'],
        ]);
    }
}
