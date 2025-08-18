<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cargo;

class CargosSeeder extends Seeder
{
    public function run(): void
    {
        $cargos = ['admin', 'Funcionário'];

        foreach ($cargos as $name) {
            Cargo::firstOrCreate(['name' => $name]);
        }
    }
}
