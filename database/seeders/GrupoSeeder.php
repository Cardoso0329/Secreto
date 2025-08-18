<?php
namespace Database\Seeders;

use App\Models\Grupo;
use Illuminate\Database\Seeder;

class GrupoSeeder extends Seeder
{
    public function run(): void
    {
        
        Grupo::create(['name' => 'Desenvolvimento']);
        Grupo::create(['name' => 'Marketing']);
    }
}
