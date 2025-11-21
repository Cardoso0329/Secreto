<?php

namespace App\Http\Controllers;
use App\Mail\RecadoAvisoMail;

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
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use App\Mail\RecadoAvisoMail; // (vamos criar já a seguir)


class RecadoController extends Controller
{

    public function create(Request $request)
{
    // Pega tipo do query ou da sessão
    $tipoFormularioName = $request->query('tipo_formulario') ?? $request->session()->get('local_trabalho');

    if (!$tipoFormularioName) {
        return redirect()->route('recados.index')->with('error', 'Tipo de formulário inválido ou não selecionado.');
    }

    $tipoFormulario = TipoFormulario::where('name', $tipoFormularioName)->first();

    if (!$tipoFormulario) {
        return redirect()->route('recados.index')->with('error', 'Tipo de formulário inválido.');
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

    // Carrega dados que a view sempre precisa
    $estados = Estado::all();
    $tiposFormulario = TipoFormulario::all();

    // Query base dos recados
    $recados = Recado::with([
        'setor', 'origem', 'departamento', 'destinatarios', 'estado',
        'sla', 'tipo', 'aviso', 'tipoFormulario', 'grupos.users', 'guestTokens'
    ]);

    // Restrição para não-admin
    if ($user->cargo?->name !== 'admin') {
        $recados->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereHas('destinatarios', fn($q2) => $q2->where('users.id', $user->id))
              ->orWhereHas('grupos.users', fn($q3) => $q3->where('users.id', $user->id));
        });
    }

    // Se ainda não escolheu local, mostrar popup
    $showPopup = !$request->session()->has('local_trabalho');

    // Filtros: prioriza query string, senão sessão
    $filtros = $request->query() ?: session('recados_filtros', []);

    // Guarda filtros da query em sessão
    if ($request->query()) {
        session(['recados_filtros' => $filtros]);
    }

    // Se já escolheu local e não veio filtro na query, aplica filtros automáticos
    if (!$showPopup && empty($request->query())) {
        $local = $request->session()->get('local_trabalho');

        $estadoPendente = Estado::where('name', 'Pendente')->first();
        $tipoFormularioLocal = TipoFormulario::where('name', $local)->first();

        if ($estadoPendente) $filtros['estado_id'] = $estadoPendente->id;
        if ($tipoFormularioLocal) $filtros['tipo_formulario_id'] = $tipoFormularioLocal->id;
    }

    // Aplica filtros individualmente
    if (!empty($filtros['id'])) $recados->where('id', $filtros['id']);
    if (!empty($filtros['contact_client'])) $recados->where('contact_client', 'like', '%' . $filtros['contact_client'] . '%');
    if (!empty($filtros['plate'])) $recados->where('plate', 'like', '%' . $filtros['plate'] . '%');
    if (!empty($filtros['estado_id'])) $recados->where('estado_id', $filtros['estado_id']);
    if (!empty($filtros['tipo_formulario_id'])) $recados->where('tipo_formulario_id', $filtros['tipo_formulario_id']);

    // Ordenação
    $sortBy = $filtros['sort_by'] ?? 'id';
    $sortDir = $filtros['sort_dir'] ?? 'desc';
    $allowedSorts = ['id', 'created_at', 'name'];
    if (!in_array($sortBy, $allowedSorts)) $sortBy = 'id';
    if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'desc';

    $recados = $recados->orderBy($sortBy, $sortDir)
                       ->paginate(10)
                       ->withQueryString();

    // Retorna a view com tudo que ela precisa
    return view('recados.index', compact('recados','estados','tiposFormulario','filtros','showPopup'));
}


   public function store(Request $request)
{
    // Validação
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
        'destinatarios_livres' => 'array',
        'destinatarios_livres.*' => 'email',
        'tipo_formulario_id' => 'required|exists:tipo_formularios,id', // <- obrigatório agora
        'wip' => 'nullable|string|max:255',
        'assunto' => 'required|string|max:255',
    ]);

    // Associa usuário logado
    $validated['user_id'] = auth()->id();

    // Armazena ficheiro se existir
    if ($request->hasFile('ficheiro')) {
        $validated['ficheiro'] = basename($request->file('ficheiro')->store('recados', 'public'));
    }

    // Cria recado
    $recado = Recado::create($validated);

    // DESTINATÁRIOS USERS
    if ($request->filled('destinatarios_users')) {
        $recado->destinatariosUsers()->sync($request->destinatarios_users);

        $emails = User::whereIn('id', $request->destinatarios_users)->pluck('email')->toArray();
        foreach ($emails as $email) {
            Mail::to($email)->send(new RecadoCriadoMail($recado));
        }
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

    // Estado Pendente padrão se não definido
    $estadoPendente = Estado::where('name', 'Pendente')->first();
    if ($estadoPendente) {
        $recado->estado_id = $estadoPendente->id;
        $recado->save();
<<<<<<< HEAD

        return redirect()->route('recados.index')->with('success', 'Recado criado e emails enviados.');

=======
>>>>>>> main
    }

    return redirect()->route('recados.index')->with('success', 'Recado criado e emails enviados.');
}


    public function show($id)
<<<<<<< HEAD
{
    // Carrega o recado com todas as relações necessárias
    $recado = Recado::with([
        'sla',
        'tipo',
        'origem',
        'setor',
        'departamento',
        'destinatarios',
        'aviso',
        'estado',
        'tipoFormulario',
        'guestTokens',
        'grupos'
    ])->findOrFail($id);

    $user = auth()->user();

    // 🔒 Verifica se o utilizador pode ver este recado
    if (
        $user->cargo?->name !== 'admin' &&
        $recado->user_id !== $user->id &&
        !$recado->destinatarios->contains($user->id)
    ) {
        abort(403, 'Acesso negado. Este recado não é seu.');
    }

    // 🔹 Carrega listas para selects (Estados e Avisos)
    $estados = Estado::orderBy('name')->get();
    $avisos  = Aviso::orderBy('name')->get();

    // 🔹 Retorna a view com todas as variáveis
    return view('recados.show', compact('recado', 'estados', 'avisos'));
}
=======
    {
        $recado = Recado::with([
            'sla', 'tipo', 'origem', 'setor', 'departamento',
            'destinatarios', 'aviso', 'estado', 'tipoFormulario',
            'guestTokens', 'grupos.users'
        ])->findOrFail($id);

        $user = auth()->user();
>>>>>>> main

        if ($user->cargo?->name !== 'admin' && $recado->user_id !== $user->id) {
            $isDestinatario = $recado->destinatarios->contains($user->id);
            $isGrupo = $recado->grupos->pluck('users')->flatten()->pluck('id')->contains($user->id);

            if (!$isDestinatario && !$isGrupo) {
                abort(403, 'Acesso negado. Este recado não é seu.');
            }
        }
            $avisos = Aviso::all(); // <-- adiciona isto

<<<<<<< HEAD
=======
        $estados = Estado::all();
        return view('recados.show', compact('recado','estados','avisos'));
    }


>>>>>>> main
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
    $recado = Recado::with(['estado', 'sla', 'tipo', 'origem', 'setor', 'departamento', 'aviso', 'destinatarios', 'grupos', 'guestTokens'])
        ->whereHas('guestTokens', fn($q) => $q->where('token', $token))
        ->firstOrFail();

    $estados = Estado::all();
    return view('emails.recados.guest', compact('recado', 'estados', 'token'));
}

   public function guestUpdate(Request $request, $token)
{
    $recado = Recado::whereHas('guestTokens', fn($q) => $q->where('token', $token))->firstOrFail();

    if ($request->filled('estado_id')) {
        $recado->estado_id = $request->estado_id;
        $recado->save();
    }

    return back()->with('success', 'Estado atualizado com sucesso!');
}

    public function guestComment(Request $request, $token)
{
    $recado = Recado::whereHas('guestTokens', fn($q) => $q->where('token', $token))->firstOrFail();

    $comentario = trim($request->comentario);
    if ($comentario) {
        $autor = 'Convidado';
        $linha = now()->format('d') . " - {$autor}: {$comentario}\n";
        $recado->observacoes .= $linha;
        $recado->save();
    }

    return back()->with('success', 'Comentário adicionado!');
}


public function exportFiltered(Request $request)
{
    $filters = $request->only(['id', 'contact_client', 'plate', 'estado_id', 'tipo_formulario_id']);

<<<<<<< HEAD
    $query = Recado::query()->with([
        'estado',
        'tipoFormulario',
        'destinatarios',
        'grupos.users',
        'guestTokens'
    ]);

    if (!empty($filters['id'])) {
=======
    // Criar query base
    $query = Recado::with([
        'estado',
        'tipoFormulario',
        'destinatarios',   // users
        'grupos.users',    // grupos + users
    ]);

    // Aplicar filtros
    if(!empty($filters['id'])) {
>>>>>>> main
        $query->where('id', $filters['id']);
    }
    if (!empty($filters['contact_client'])) {
        $query->where('contact_client', 'like', '%' . $filters['contact_client'] . '%');
    }
    if (!empty($filters['plate'])) {
        $query->where('plate', 'like', '%' . $filters['plate'] . '%');
    }
    if (!empty($filters['estado_id'])) {
        $query->where('estado_id', $filters['estado_id']);
    }
    if (!empty($filters['tipo_formulario_id'])) {
        $query->where('tipo_formulario_id', $filters['tipo_formulario_id']);
    }

    // Obter resultados filtrados
    $recados = $query->get();

    return Excel::download(new RecadosExport($recados), 'recados_filtrados.xlsx');
}

<<<<<<< HEAD
public function enviarAvisoEmail(Recado $recado)
=======

public function concluir(Recado $recado)
>>>>>>> main
{
    // Recolhe todos os emails dos destinatários (users + tokens)
    $emails = $recado->destinatarios->pluck('email')->toArray();

    if ($recado->guestTokens?->count()) {
        $emails = array_merge($emails, $recado->guestTokens->pluck('email')->toArray());
    }

    foreach ($emails as $email) {
        Mail::to($email)->queue(new RecadoAvisoMail($recado));
    }

    return back()->with('success', 'Aviso enviado por email aos destinatários!');
}

public function updateAviso(Request $request, Recado $recado)
{
    $request->validate([
        'aviso_id' => 'required|exists:avisos,id',
    ]);

    $recado->aviso_id = $request->aviso_id;
    $recado->save();

    return redirect()->back()->with('success', 'Aviso atualizado com sucesso!');
}

public function concluir($id)
{
    $recado = Recado::findOrFail($id);
    $user = auth()->user();

    // Altera o estado para "Tratado"
    $estadoTratado = Estado::where('name', 'Tratado')->first();

    if (!$estadoTratado) {
        return redirect()->back()->with('error', 'O estado "Tratado" não foi encontrado.');
    }

    $recado->estado_id = $estadoTratado->id;
    $recado->termino = now();

    // Regista nos comentários a conclusão
    $comentarioSistema = now()->format('d/m/Y H:i') . 
        ' - Sistema: Recado concluído por ' . ($user->name ?? 'Utilizador') . '.';

    $recado->observacoes = $recado->observacoes
        ? $recado->observacoes . "\n" . $comentarioSistema
        : $comentarioSistema;

    $recado->save();

    return redirect()->back()->with('success', 'Recado concluído com sucesso.');
}

public function escolherLocal(Request $request)
{
    $request->validate(['local' => 'required|in:Central,Call Center']);
    $request->session()->put('local_trabalho', $request->local);
    return redirect()->route('recados.index');
}

public function enviarAviso(Recado $recado, Aviso $aviso)
    {
        // Pega destinatários do recado (users + emails livres)
        $emails = $recado->destinatarios->pluck('email')->toArray();

        if($recado->guestTokens->count()) {
            $emails = array_merge($emails, $recado->guestTokens->pluck('email')->toArray());
        }

        foreach ($emails as $destinatarioEmail) {
            Mail::to($destinatarioEmail)->send(new RecadoAvisoMail($recado, $aviso));
        }

        return back()->with('success', 'Aviso enviado com sucesso!');
    }


}
