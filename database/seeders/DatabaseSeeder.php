<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SLASeeder::class,
            SetoresSeeder::class,
            OrigensSeeder::class,
            DepartamentosSeeder::class,
            DestinatariosSeeder::class,
            AvisosSeeder::class,
            EstadosSeeder::class,
            TiposSeeder::class,
            CargosSeeder::class,
            UsersSeeder::class,
            TipoFormularioSeeder::class,

            
        ]);

    }
}
