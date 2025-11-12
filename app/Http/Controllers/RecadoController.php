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
use App\Mail\RecadoAvisoMail; // (vamos criar já a seguir)


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

        if ($tipoFormularioName === 'Central') {
            return view('recados.create_central', compact(
                'setores', 'origens', 'departamentos', 'slas', 'tipos',
                'estados', 'avisos', 'destinatarios', 'tipoFormularioId'
            ));
        } elseif ($tipoFormularioName === 'Call Center') {
            return view('recados.create_callcenter', compact(
                'setores', 'origens', 'departamentos', 'slas', 'tipos',
                'estados', 'avisos', 'destinatarios', 'tipoFormularioId'
            ));
        } else {
            return redirect()->route('recados.index')->with('error', 'Tipo de formulário inválido.');
        }
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
            'setor', 'origem', 'departamento', 'destinatarios', 'estado', 'sla', 'tipo', 'aviso', 'tipoFormulario', 'grupos', 'guestTokens'
        ])
       ->when($user->cargo?->name !== 'admin', function ($query) use ($user) {
    $query->where(function ($q) use ($user) {
        $q->where('user_id', $user->id)
          ->orWhereHas('destinatarios', function ($q2) use ($user) {
              $q2->where('users.id', $user->id); // <- aqui sim, porque é belongsToMany
          });
    });
})




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

    // ❌ Verificação obrigatória de pelo menos um destinatário
    if (
        empty($request->destinatarios_users) &&
        empty($request->destinatarios_grupos) &&
        empty($request->destinatarios_livres)
    ) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'Deve selecionar pelo menos um destinatário.');
    }

    if ($request->hasFile('ficheiro')) {
        $path = $request->file('ficheiro')->store('recados', 'public');
        $validated['ficheiro'] = basename($path);
    }

    $validated['plate'] = $request->input('plate');
    $validated['destinatario_livre'] = $request->input('destinatario_livre');
    $validated['user_id'] = auth()->id();
   
    $request->merge(['user_id' => auth()->id()]);

    $recado = Recado::create($validated);

        $emails = [];

        // Destinatários individuais
        if ($request->filled('destinatarios_users')) {
            $recado->destinatarios()->sync($request->destinatarios_users);
            $emails = array_merge($emails, User::whereIn('id', $request->destinatarios_users)->pluck('email')->toArray());
        }

        // Destinatários por grupo (apenas vincula grupos e envia email aos membros)
        if ($request->filled('destinatarios_grupos')) {
            $recado->grupos()->sync($request->destinatarios_grupos);
            $grupos = Grupo::with('users')->whereIn('id', $request->destinatarios_grupos)->get();
            foreach ($grupos as $grupo) {
                $emails = array_merge($emails, $grupo->users->pluck('email')->toArray());
            }
        }

        $emails = array_unique($emails);

        foreach ($emails as $email) {
            Mail::to($email)->send(new RecadoCriadoMail($recado));
        }

        // Destinatários livres
        if ($request->filled('destinatarios_livres')) {
            foreach ($request->destinatarios_livres as $livreEmail) {
                if (filter_var($livreEmail, FILTER_VALIDATE_EMAIL)) {
                    $token = Str::random(60);
                    RecadoGuestToken::create([
                        'recado_id' => $recado->id,
                        'email' => $livreEmail,
                        'token' => $token,
                        'expires_at' => now()->addMonth(),
                        'is_active' => true,
                    ]);
                    $guestUrl = route('recados.guest', ['token' => $token]);
                    Mail::to($livreEmail)->send(new RecadoCriadoMail($recado, $guestUrl));
                }
            }
        }

        $estadoPendente = Estado::where('name', 'Pendente')->first();
        $recado->estado_id = $estadoPendente->id;
        $recado->save();

        return redirect()->route('recados.index')->with('success', 'Recado criado e emails enviados.');

    }

    public function show($id)
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

    $query = Recado::query()->with([
        'estado',
        'tipoFormulario',
        'destinatarios',
        'grupos.users',
        'guestTokens'
    ]);

    if (!empty($filters['id'])) {
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

    $recados = $query->get();

    return Excel::download(new RecadosExport($recados), 'recados_filtrados.xlsx');
}

public function enviarAvisoEmail(Recado $recado)
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






}
