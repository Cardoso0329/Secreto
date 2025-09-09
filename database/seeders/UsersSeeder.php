<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Cargo;
use Illuminate\Support\Facades\DB;
 

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // Busca cargos
        $adminCargo = Cargo::where('name', 'admin')->first();
        $funcCargo = Cargo::where('name', 'Funcionário')->first();


        User::create([
            'name' => 'Admin Principal',
            'email' => 'admin@empresa.com',
            'password' => Hash::make('admin123'),
            'cargo_id' => $adminCargo->id,
        ]);

        User::create([
            'name' => 'Carlos Funcionário',
            'email' => 'funcionario@empresa.com',
            'password' => Hash::make('func123'),
            'cargo_id' => $funcCargo->id,
        ]);
    }
}
