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
        // Inserir ou atualizar dados com base nos users
        User::all()->each(function ($user) {
            Destinatario::updateOrCreate(
                ['email' => $user->email],
                ['name' => $user->name]
            );
        });
    }
}
