<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;

class UsersImport implements OnEachRow
{
    public function onRow(Row $row)
    {
        $row = $row->toArray();

        // Verifica se já existe um utilizador com este email
        if (User::where('email', $row[2])->exists()) {
            return; // Ignora se já existe
        }

        // Cria o utilizador
        User::create([
            'name' => $row[1],
            'email' => $row[2],
            'password' => Hash::make('123456'), // Password padrão
            'cargo_id' => 2, // Cargo padrão
        ]);
    }
}
