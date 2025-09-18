<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\{
    SLAController, SetorController, OrigemController,
    DepartamentoController, DestinatarioController,
    AvisoController, EstadoController, TipoController,
    RecadoController, UserController, PainelController,
    ProfileController, CargoController, GrupoController
};

// Página inicial
Route::get('/', function () {
    return view('welcome');
});

// Rotas de autenticação (login, registro, etc)
require __DIR__.'/auth.php';

// Rotas protegidas - apenas usuários autenticados
Route::middleware('auth')->group(function () {

    // Painel principal
    Route::get('/painel', [PainelController::class, 'index'])->name('painel');
    Route::get('/users/search', [UserController::class, 'search'])->name('users.search');


    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


});
Route::resource('recados', RecadoController::class);

        Route::resource('grupos', GrupoController::class);
        Route::resource('cargos', CargoController::class);
        Route::resource('users', UserController::class);
        Route::resource('slas', SLAController::class);
        Route::resource('setores', SetorController::class)->parameters(['setores' => 'setor']);
        Route::resource('origens', OrigemController::class)->parameters(['origens' => 'origem']);
        Route::resource('departamentos', DepartamentoController::class)->parameters(['departamentos' => 'departamento']);
        Route::resource('destinatarios', DestinatarioController::class)->parameters(['destinatarios' => 'destinatario']);
        Route::resource('avisos', AvisoController::class)->parameters(['avisos' => 'aviso']);
        Route::resource('estados', EstadoController::class)->parameters(['estados' => 'estado']);
        Route::resource('tipos', TipoController::class)->parameters(['tipos' => 'tipo']);
        Route::put('/recados/{recado}/observacoes', [RecadoController::class, 'adicionarComentario'])->name('recados.observacoes.update');

// Atualiza estado pelo select
Route::put('/recados/{recado}/estado', [RecadoController::class, 'updateEstado'])->name('recados.estado.update');

// Concluir o recado (muda estado para "Tratado")
Route::put('/recados/{recado}/concluir', [RecadoController::class, 'concluir'])->name('recados.concluir');

Route::put('/grupos/{grupo}/users', [GrupoController::class, 'updateUsers'])->name('grupos.updateUsers');
Route::get('/grupos/{grupo}/users', [GrupoController::class, 'users'])->name('grupos.users');
Route::post('/grupos/{grupo}/users/remover', [GrupoController::class, 'removerUsers'])->name('grupos.users.remover');

Route::get('users-export', [UserController::class, 'export'])->name('users.export');
Route::get('users-import', [UserController::class, 'importForm'])->name('users.importForm');
Route::post('/users/import', [UserController::class, 'import'])->name('users.import');

Route::get('recados-export', [RecadoController::class, 'export'])
    ->name('recados.export');

Route::get('/recados/export/filtered', [RecadoController::class, 'exportFiltered'])->name('recados.export.filtered');


// Mostrar o recado para convidado (com token)
Route::get('/recados/guest/{token}', [RecadoController::class, 'guestView'])->name('recados.guest');

// Atualizar o recado via convidado (formulário PUT)
Route::match(['put', 'post'], '/recados/guest/{token}', [RecadoController::class, 'guestUpdate'])
    ->name('recados.guest.update');

// **Novo** - comentário por convidado (POST)
Route::post('/recados/guest/{token}/comment', [RecadoController::class, 'guestComment'])
     ->name('recados.guest.comment');




       
