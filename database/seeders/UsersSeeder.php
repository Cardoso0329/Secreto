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

$users = [
    ['name' => 'Abel Freitas', 'email' => 'abel.freitas@soccsantos.pt'],
    ['name' => 'Agostinho Azevedo', 'email' => 'agostinho.azevedo@soccsantos.pt'],
    ['name' => 'Almiro Silva', 'email' => 'almiro.silva@soccsantos.pt'],
    ['name' => 'Ana Teixeira', 'email' => 'ana.teixeira@soccsantos.pt'],
    ['name' => 'Ana Silva', 'email' => 'ana.silva@soccsantos.pt'],
    ['name' => 'Anabela Jesus', 'email' => 'anabela.jesus@soccsantos.pt'],
    ['name' => 'Armando Pinto', 'email' => 'armando.pinto@soccsantos.pt'],
    ['name' => 'Bárbara Santos', 'email' => 'barbara.santos@soccsantos.pt'],
    ['name' => 'Brigida Brandão', 'email' => 'brigida.brandao@soccsantos.pt'],
    ['name' => 'Bruno Machado', 'email' => 'bruno.machado@soccsantos.pt'],
    ['name' => 'Bruno Ribeiro', 'email' => 'bruno.ribeiro@soccsantos.pt'],
    ['name' => 'Claudia Gonçalves', 'email' => 'claudia.goncalves@soccsantos.pt'],
    ['name' => 'Daniela Lopes', 'email' => 'daniela.lopes@soccsantos.pt'],
    ['name' => 'Diana Ferreirinha', 'email' => 'diana.ferreirinha@soccsantos.pt'],
    ['name' => 'Diana Sousa', 'email' => 'diana.sousa@soccsantos.pt'],
    ['name' => 'Dias Pinto', 'email' => 'dias.pinto@soccsantos.pt'],
    ['name' => 'Dulce Pinto', 'email' => 'dulce.pinto@soccsantos.pt'],
    ['name' => 'Eduardo Brito', 'email' => 'eduardo.brito@soccsantos.pt'],
    ['name' => 'Eduardo Silva', 'email' => 'eduardo.silva@soccsantos.pt'],
    ['name' => 'Emanuel Teixeira', 'email' => 'emanuel.teixeira@soccsantos.pt'],
    ['name' => 'Filipe Barbosa', 'email' => 'filipe.barbosa@soccsantos.pt'],
    ['name' => 'Francisco Ferreira', 'email' => 'francisco.ferreira@soccsantos.pt'],
    ['name' => 'Gonçalo Felgueiras', 'email' => 'goncalo.felgueiras@soccsantos.pt'],
    ['name' => 'Hugo André', 'email' => 'hugo.andre@soccsantos.pt'],
    ['name' => 'Jéssica Ferreira', 'email' => 'jessica.ferreira@soccsantos.pt'],
    ['name' => 'João Barbosa', 'email' => 'joao.barbosa@soccsantos.pt'],
    ['name' => 'João Castro', 'email' => 'joao.castro@soccsantos.pt'],
    ['name' => 'João Santos Costa', 'email' => 'joao.santos.costa@soccsantos.pt'],
    ['name' => 'Jorge Carlos', 'email' => 'jorge.carlos@soccsantos.pt'],
    ['name' => 'Jorge Carvalho', 'email' => 'jorge.carvalho@soccsantos.pt'],
    ['name' => 'Jorge Lemos', 'email' => 'jorge.lemos@soccsantos.pt'],
    ['name' => 'Jorge Silva', 'email' => 'jorge.silva@soccsantos.pt'],
    ['name' => 'José Costa', 'email' => 'josemanuel.costa@soccsantos.pt'],
    ['name' => 'Maria José Dias', 'email' => 'mjose.dias@soccsantos.pt'],
    ['name' => 'Marisa Silva', 'email' => 'marisa.silva@soccsantos.pt'],
    ['name' => 'Lilia Morais', 'email' => 'lilia.morais@soccsantos.pt'],
    ['name' => 'Marco Monteiro', 'email' => 'marco.monteiro@soccsantos.pt'],
    ['name' => 'Mariana Pinto', 'email' => 'mariana.pinto@soccsantos.pt'],
    ['name' => 'Mário Pereira', 'email' => 'mario.pereira@soccsantos.pt'],
    ['name' => 'Miguel Carvalho', 'email' => 'miguel.carvalho@soccsantos.pt'],
    ['name' => 'Miguel Valente', 'email' => 'miguel.valente@soccsantos.pt'],
    ['name' => 'Nuno Ferreira', 'email' => 'nuno.ferreira@soccsantos.pt'],
    ['name' => 'Nuno Ramalho', 'email' => 'nuno.ramalho@soccsantos.pt'],
    ['name' => 'Nuno Rebelo', 'email' => 'nuno.rebelo@soccsantos.pt'],
    ['name' => 'Nuno Rocha', 'email' => 'nuno.rocha@soccsantos.pt'],
    ['name' => 'Nuno Torres', 'email' => 'nuno.torres@soccsantos.pt'],
    ['name' => 'Paula Alexandra', 'email' => 'paula.alexandra@soccsantos.pt'],
    ['name' => 'Paula Carvalho', 'email' => 'paula.carvalho@soccsantos.pt'],
    ['name' => 'Paulo Costa', 'email' => 'paulo.costa@soccsantos.pt'],
    ['name' => 'Paulo Fontinhas', 'email' => 'paulo.fontinhas@soccsantos.pt'],
    ['name' => 'Paulo Moreno', 'email' => 'paulo.moreno@soccsantos.pt'],
    ['name' => 'Paulo Sousa', 'email' => 'paulo.sousa@soccsantos.pt'],
    ['name' => 'Pedro Almeida', 'email' => 'pedro.almeida@soccsantos.pt'],
    ['name' => 'Pedro Cardoso', 'email' => 'pedro.cardoso@soccsantos.pt'],
    ['name' => 'Raquel Magalhães', 'email' => 'raquel.magalhaes@soccsantos.pt'],
    ['name' => 'Ricardo Freitas', 'email' => 'ricardo.freitas@soccsantos.pt'],
    ['name' => 'Rúben Pereira', 'email' => 'ruben.pereira@soccsantos.pt'],
    ['name' => 'Rui Campos', 'email' => 'rui.campos@soccsantos.pt'],
    ['name' => 'Rui Silva', 'email' => 'rui.silva@soccsantos.pt'],
    ['name' => 'Salete Sousa', 'email' => 'salete.sousa@soccsantos.pt'],
    ['name' => 'Sandra Conde', 'email' => 'sandra.conde@soccsantos.pt'],
    ['name' => 'Sandra Figueiredo', 'email' => 'sandra.figueiredo@soccsantos.pt'],
    ['name' => 'Sofia Monteiro', 'email' => 'sofia.monteiro@soccsantos.pt'],
    ['name' => 'Susana Costa', 'email' => 'susana.costa@soccsantos.pt'],
    ['name' => 'Tiago Dantas', 'email' => 'tiago.dantas@soccsantos.pt'],
    ['name' => 'Tiago Martins', 'email' => 'tiago.martins@soccsantos.pt'],
    
];

foreach ($users as $u) {
    User::create([
        'name' => $u['name'],
        'email' => $u['email'],
        'password' => Hash::make('123'),
        'cargo_id' => $funcCargo->id,
    ]);
}

    }
}
