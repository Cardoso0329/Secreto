<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Limpa a tabela de forma segura conforme o driver.
     */
    protected function truncateTable(string $table)
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table($table)->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } else {
            // SQLite ou outros drivers
            DB::table($table)->delete();
        }
    }

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Limpar tabelas antes de popular
        $tables = [
            'slas',
            'setores',
            'origens',
            'departamentos',
            'destinatarios',
            'avisos',
            'estados',
            'tipos',
            'cargos',
            'users',
            'tipo_formularios',
            // adiciona outras tabelas seeders aqui, se houver
        ];

        foreach ($tables as $table) {
            $this->truncateTable($table);
        }

        // Executar os seeders normalmente
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
