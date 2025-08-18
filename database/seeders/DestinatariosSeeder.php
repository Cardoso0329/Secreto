<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Destinatario;
use Illuminate\Support\Facades\DB;


class DestinatariosSeeder extends Seeder
{
    public function run()
{
    // Desativa verificações de FK
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    // Trunca a tabela
    Destinatario::truncate();

    // Reativa verificações de FK
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    // Agora insere os dados
    User::all()->each(function ($user) {
        Destinatario::updateOrCreate(
            ['email' => $user->email],
            ['name' => $user->name]
        );
    });
}

}
