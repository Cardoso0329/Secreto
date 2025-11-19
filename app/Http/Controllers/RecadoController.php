<?php

namespace App\Http\Controllers;

use App\Models\SLA;
use App\Models\Recado;
use App\Models\Setor;
use App\Models\Origem;
use App\Models\Departamento;
use App\Models\Aviso;
use App\Models\Estado;
use App\Models\Tipo;
use App\Models\User;
use App\Models\Destinatario;
use App\Models\TipoFormulario;
use App\Models\Grupo;
use App\Models\RecadoGuestToken;
use App\Mail\RecadoCriadoMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Exports\RecadosExport;
use App\Imports\RecadosImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class RecadoController extends Controller
{
    public function create(Request $request)
    {
        $tipoFormularioName = $request->query('tipo_formulario');
        $tipoFormulario = TipoFormulario::where('name', $tipoFormularioName)->first();

        if (!$tipoFormulario) {
            return redirect()->route('recados.index')->with('error', 'Tipo de formulário inválido ou não selecionado.');
        }

        $setores = Setor::all();
        $origens = Origem::all();
        $departamentos = Departamento::all();
        $slas = SLA::all();
        $tipos = Tipo::whereIn('name', [
            'Pedido de Contacto',
            'Pedido de Informação',
            'Pedido de Marcação',
            'Pedido de Orçamento',
            'Tomada de Conhecimento',
            'Reclamação/Insatisfação',
        ])->get();
        $estados = Estado::all();
        $avisos = Aviso::all();
        $destinatarios = Destinatario::with('user')->get();
        $tipoFormularioId = $tipoFormulario->id;

        $view = $tipoFormularioName === 'Central' 
            ? 'recados.create_central'
            : ($tipoFormularioName === 'Call Center' ? 'recados.create_callcenter' : null);

        if (!$view) return redirect()->route('recados.index')->with('error', 'Tipo de formulário inválido.');

        return view($view, compact(
            'setores', 'origens', 'departamentos', 'slas', 'tipos',
            'estados', 'avisos', 'destinatarios', 'tipoFormularioId'
        ));
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $estados = Estado::all();
        $tiposFormulario = TipoFormulario::all();

        $sortBy = $request->get('sort_by', 'id');
        $sortDir = $request->get('sort_dir', 'desc');

        $allowedSorts = ['id', 'created_at', 'name'];
        if (!in_array($sortBy, $allowedSorts)) $sortBy = 'id';
        if (!in_array($sortDir, ['asc','desc'])) $sortDir = 'desc';

        $recados = Recado::with([
            'setor', 'origem', 'departamento', 'destinatarios', 'estado',
            'sla', 'tipo', 'aviso', 'tipoFormulario', 'grupos', 'guestTokens'
        ])
        ->when(
            $user->cargo?->name !== 'admin',
            function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhereHas('destinatarios', fn($q2) => $q2->where('users.id', $user->id))
                      ->orWhereHas('grupos.users', fn($q3) => $q3->where('users.id', $user->id));
                });
            }
        )
        ->when($request->filled('estado_id'), fn($q) => $q->where('estado_id', $request->estado_id))
        ->when($request->filled('tipo_formulario_id'), fn($q) => $q->where('tipo_formulario_id', $request->tipo_formulario_id))
        ->when($request->filled('id'), fn($q) => $q->where('id', $request->id))
        ->when($request->filled('contact_client'), fn($q) => $q->where('contact_client', 'like', '%'.$request->contact_client.'%'))
        ->when($request->filled('plate'), fn($q) => $q->where('plate', 'like', '%'.$request->plate.'%'))
        ->orderBy($sortBy, $sortDir)
        ->paginate(10)
        ->withQueryString();

        return view('recados.index', compact('recados','estados','tiposFormulario'));
    }

    public function store(Request $request)
    {
       $validated = $request->validate([
        'name' => 'required|string|max:255',
        'contact_client' => 'required|string|max:255',
        'plate' => 'nullable|string|max:255',
        'operator_email' => 'nullable|email',
        'sla_id' => 'required|exists:slas,id',
        'tipo_id' => 'required|exists:tipos,id',
        'origem_id' => 'required|exists:origens,id',
        'setor_id' => 'required|exists:setores,id',
        'departamento_id' => 'required|exists:departamentos,id',
        'mensagem' => 'required|string',
        'ficheiro' => 'nullable|file',
        'aviso_id' => 'nullable|exists:avisos,id',
        'estado_id' => 'required|exists:estados,id',
        'observacoes' => 'nullable|string',
        'abertura' => 'nullable|date',
        'termino' => 'nullable|date',
        'destinatarios_users' => 'array',
        'destinatarios_users.*' => 'exists:users,id',
        'destinatarios_grupos' => 'array',
        'destinatarios_grupos.*' => 'exists:grupos,id',
        'tipo_formulario_id' => 'nullable|exists:tipo_formularios,id',
        'wip' => 'nullable|string|max:255',
        'destinatarios_livres' => 'array',
        'destinatarios_livres.*' => 'email',
    ]);
        $validated['user_id'] = auth()->id();
        if ($request->hasFile('ficheiro')) {
            $validated['ficheiro'] = basename($request->file('ficheiro')->store('recados', 'public'));
        }

        $recado = Recado::create($validated);

        // DESTINATÁRIOS USERS
        if ($request->filled('destinatarios_users')) {
            $recado->destinatariosUsers()->sync($request->destinatarios_users);
        }

        // DESTINATÁRIOS LIVRES
        if ($request->filled('destinatarios_livres')) {
            foreach ($request->destinatarios_livres as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $token = Str::random(60);
                    RecadoGuestToken::create([
                        'recado_id' => $recado->id,
                        'email' => $email,
                        'token' => $token,
                        'expires_at' => now()->addMonth(),
                        'is_active' => true,
                    ]);
                    Mail::to($email)->send(new RecadoCriadoMail($recado, route('recados.guest', $token)));
                }
            }
        }

        // DESTINATÁRIOS GRUPOS
        if ($request->filled('destinatarios_grupos')) {
            $recado->grupos()->sync($request->destinatarios_grupos);

            $emails = User::whereHas('grupos', function ($q) use ($request) {
                $q->whereIn('grupos.id', $request->destinatarios_grupos);
            })->pluck('email')->toArray();

            foreach (array_unique($emails) as $email) {
                Mail::to($email)->send(new RecadoCriadoMail($recado));
            }
        }

        // Define estado como Pendente
        $recado->estado_id = Estado::where('name','Pendente')->first()->id ?? $recado->estado_id;
        $recado->save();

        return redirect()->route('recados.index')->with('success', 'Recado criado e emails enviados.');
    }
    public function show($id)
    {
        $recado = Recado::with([
            'sla', 'tipo', 'origem', 'setor', 'departamento', 'destinatarios', 'aviso', 'estado', 'tipoFormulario', 'guestTokens', 'grupos'
        ])->findOrFail($id);

       $user = auth()->user();

// Se não for admin e não for criador do recado
if ($user->cargo?->name !== 'admin' && $recado->user_id !== $user->id) {

    // Verifica se é destinatário direto
    $isDestinatario = $recado->destinatarios->contains($user->id);

    // Verifica se pertence a algum grupo que recebeu o recado
    $isGrupo = $recado->grupos->pluck('users')->flatten()->pluck('id')->contains($user->id);

    if (!$isDestinatario && !$isGrupo) {
        abort(403, 'Acesso negado. Este recado não é seu.');
    }
}



        $estados = Estado::all();
        return view('recados.show', compact('recado','estados'));
    }

    public function destroy($id)
    {
        $user = auth()->user();
        if ($user->cargo?->name !== 'admin') abort(403, 'Acesso não autorizado');

        $recado = Recado::findOrFail($id);
        $recado->delete();

        return redirect()->route('recados.index')->with('success','Recado apagado com sucesso!');
    }

    public function adicionarComentario(Request $request, Recado $recado)
    {
        $request->validate(['comentario'=>'required|string']);
        $novaLinha = now()->format('d/m/Y H:i') . ' - ' . auth()->user()->name . ': ' . $request->comentario;

        $recado->observacoes = $recado->observacoes
            ? $recado->observacoes . "\n" . $novaLinha
            : $novaLinha;

        $estadoPendente = Estado::where('name','Pendente')->first();
        if ($estadoPendente) $recado->estado_id = $estadoPendente->id;

        $recado->save();

        RecadoGuestToken::where('recado_id', $recado->id)
            ->where('is_active', true)
            ->update(['is_active'=>false]);

        return redirect()->back()->with('success','Comentário adicionado.');
    }

    public function updateEstado(Request $request, Recado $recado)
{
    $request->validate(['estado_id' => 'required|exists:estados,id']);

    $estadoAntigo = $recado->estado;
    $novoEstado = Estado::find($request->estado_id);
    $user = auth()->user();

    if (!$novoEstado) {
        return redirect()->back()->with('error', 'Estado inválido.');
    }

    $recado->estado_id = $novoEstado->id;
    $comentarioSistema = null;

    // 🟡 Reaberto (Tratado -> Pendente)
    if (
        $estadoAntigo &&
        strtolower($estadoAntigo->name) === 'tratado' &&
        strtolower($novoEstado->name) === 'pendente'
    ) {
        $comentarioSistema = now()->format('d/m/Y H:i') .
            ' - Sistema: Recado reaberto por ' . ($user->name ?? 'Utilizador') . '.';
    }

    // 🟢 Concluído (novo estado = Tratado)
    if (strtolower($novoEstado->name) === 'tratado') {
        $comentarioSistema = now()->format('d/m/Y H:i') .
            ' - Sistema: Recado concluído por ' . ($user->name ?? 'Utilizador') . '.';
        $recado->termino = now();
    }

    if ($comentarioSistema) {
        $recado->observacoes = $recado->observacoes
            ? $recado->observacoes . "\n" . $comentarioSistema
            : $comentarioSistema;
    }

    $recado->save();

    RecadoGuestToken::where('recado_id', $recado->id)
        ->where('is_active', true)
        ->update(['is_active' => false]);

    return redirect()->back()->with('success', 'Estado atualizado com sucesso.');
}


    public function guestView($token)
    {
        $guestToken = RecadoGuestToken::where('token',$token)->firstOrFail();
        if (!$guestToken->isValid()) abort(403,'Link expirado ou inválido.');

        $recado = $guestToken->recado;
        $estados = Estado::all();

        return view('emails.recados.guest', compact('recado','token','estados'));
    }

    public function guestUpdate(Request $request, $token)
    {
        $guestToken = RecadoGuestToken::where('token',$token)->where('is_active',true)->firstOrFail();
        if (!$guestToken->isValid()) abort(403,'Link expirado ou inválido.');

        $recado = $guestToken->recado;

        $validated = $request->validate([
            'mensagem'=>'nullable|string|max:5000',
            'estado_id'=>'nullable|exists:estados,id',
            'comentario'=>'nullable|string|max:2000',
        ]);

        if (!empty($validated['mensagem'])) $recado->mensagem = $validated['mensagem'];

        if (!empty($validated['estado_id'])) {
            $novo = Estado::find($validated['estado_id']);
            if ($novo && strtolower($novo->name)!=='concluído' && strtolower($novo->name)!=='tratado') {
                $recado->estado_id = $novo->id;
            }
        }

        if (!empty($validated['comentario'])) {
            $nomeOuEmail = $recado->destinatario_livre ?? 'Convidado';
            $novaLinha = now()->format('d/m/Y H:i') . ' - ' . $nomeOuEmail . ': ' . $validated['comentario'];
            $recado->observacoes = $recado->observacoes
                ? $recado->observacoes . "\n" . $novaLinha
                : $novaLinha;
        }

        
        $recado->save();

        if ($recado->estado && strtolower($recado->estado->name)==='tratado') {
            $guestToken->update(['is_active'=>false]);
        }

        return redirect()->route('recados.guest',['token'=>$token])->with('success','Recado atualizado com sucesso!');
    }

    public function guestComment(Request $request, $token)
    {
        $request->validate(['comentario'=>'required|string|max:2000']);

        $guestToken = RecadoGuestToken::where('token',$token)->where('is_active',true)->firstOrFail();
        if (!$guestToken->isValid()) abort(403,'Link expirado ou inválido.');

        $recado = $guestToken->recado;
        if ($recado->estado && strtolower($recado->estado->name)==='concluído') {
            return redirect()->route('recados.guest',['token'=>$token])->with('error','Este recado já está concluído. Não são permitidos comentários.');
        }

        $nome = $request->input('nome') ?: 'Convidado';
        $novaLinha = now()->format('d/m/Y H:i') . ' - ' . $nome . ': ' . $request->comentario;
        $recado->observacoes = $recado->observacoes
            ? $recado->observacoes . "\n" . $novaLinha
            : $novaLinha;

        $estadoPendente = Estado::where('name','Pendente')->first();
        if ($estadoPendente) $recado->estado_id = $estadoPendente->id;

        $recado->save();

        return redirect()->route('recados.guest',['token'=>$token])->with('success','Comentário enviado. Obrigado!');
    }


public function exportFiltered(Request $request)
{
    // Guardar filtros do request
    $filters = $request->only(['id','contact_client','plate','estado_id','tipo_formulario_id']);

    // Criar query base
    $query = Recado::with([
        'estado',
        'tipoFormulario',
        'destinatarios',   // users
        'grupos.users',    // grupos + users
    ]);

    // Aplicar filtros
    if(!empty($filters['id'])) {
        $query->where('id', $filters['id']);
    }
    if(!empty($filters['contact_client'])) {
        $query->where('contact_client', 'like', '%' . $filters['contact_client'] . '%');
    }
    if(!empty($filters['plate'])) {
        $query->where('plate', 'like', '%' . $filters['plate'] . '%');
    }
    if(!empty($filters['estado_id'])) {
        $query->where('estado_id', $filters['estado_id']);
    }
    if(!empty($filters['tipo_formulario_id'])) {
        $query->where('tipo_formulario_id', $filters['tipo_formulario_id']);
    }

    // Obter resultados filtrados
    $recados = $query->get();

    // Export usando Maatwebsite Excel
    return Excel::download(new RecadosExport($recados), 'recados_filtrados.xlsx');
}


public function concluir(Recado $recado)
{
    $estadoTratado = Estado::where('name', 'Tratado')->first();
    if (!$estadoTratado) {
        return redirect()->back()->with('error', 'Estado "Tratado" não encontrado.');
    }

    $user = auth()->user();

    // Atualiza estado e data de término
    $recado->estado_id = $estadoTratado->id;
    $recado->termino = now();

    // Adiciona comentário do sistema
    $comentarioSistema = now()->format('d/m/Y H:i') .
        ' - Sistema: Recado concluído por ' . ($user->name ?? 'Utilizador') . '.';

    $recado->observacoes = $recado->observacoes
        ? $recado->observacoes . "\n" . $comentarioSistema
        : $comentarioSistema;

    $recado->save();

    // Desativar tokens de convidados ativos
    RecadoGuestToken::where('recado_id', $recado->id)
        ->where('is_active', true)
        ->update(['is_active' => false]);

    return redirect()->back()->with('success', 'Recado concluído com sucesso.');
}








}
