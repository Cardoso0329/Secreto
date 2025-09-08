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
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class RecadoController extends Controller
{
    public function create(Request $request)
    {
        $tipoFormularioName = $request->query('tipo_formulario');

        $tipoFormulario = TipoFormulario::where('name', $tipoFormularioName)->first();

        if (!$tipoFormulario) {
            // Se não escolher tipo, podes redirecionar para index ou mostrar erro
            return redirect()->route('recados.index')->with('error', 'Tipo de formulário inválido ou não selecionado.');
        }

        // Dados necessários para o formulário
        $setores = Setor::all();
        $origens = Origem::all();
        $departamentos = Departamento::all();
        $slas = SLA::all();
        $tipos = Tipo::whereIn('name', [
    'Pedido de contacto',
    'Pedido de informação',
    'Pedido de marcação',
    'Pedido de orçamento'
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
    if (!in_array($sortBy, $allowedSorts)) {
        $sortBy = 'id';
    }
    if (!in_array($sortDir, ['asc', 'desc'])) {
        $sortDir = 'desc';
    }

    $recados = Recado::with([
        'setor', 'origem', 'departamento', 'destinatarios', 'estado', 'sla', 'tipo', 'aviso', 'tipoFormulario'
    ])
        ->when($user->cargo?->name === 'Funcionário', function ($query) use ($user) {
            // Funcionário só pode ver os recados que criou ou que lhe foram destinados
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('destinatarios', function ($q2) use ($user) {
                      $q2->where('users.id', $user->id);
                  });
            });
        })
        ->when(!$user->cargo || !in_array($user->cargo->name, ['admin','Funcionário']), function ($query) use ($user) {
            // Outros cargos → por agora restringes como quiseres, aqui coloco a mesma regra de funcionário
            $query->where('user_id', $user->id);
        })

        ->when($request->filled('estado_id'), fn($q) => $q->where('estado_id', $request->estado_id))
        ->when($request->filled('tipo_formulario_id'), fn($q) => $q->where('tipo_formulario_id', $request->tipo_formulario_id))

        // 🔍 filtros

        ->when($request->filled('id'), fn($q) =>
    $q->where('id', $request->id)
)
        ->when($request->filled('contact_client'), fn($q) =>
            $q->where('contact_client', 'like', '%'.$request->contact_client.'%')
        )
        ->when($request->filled('plate'), fn($q) =>
            $q->where('plate', 'like', '%'.$request->plate.'%')
        )

        ->orderBy($sortBy, $sortDir)
        ->paginate(10)
        ->withQueryString();

    return view('recados.index', compact('recados', 'estados', 'tiposFormulario'));
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

    if ($request->hasFile('ficheiro')) {
        $path = $request->file('ficheiro')->store('recados', 'public');
        $validated['ficheiro'] = basename($path);
    }

    $validated['plate'] = $request->input('plate');
    $validated['destinatario_livre'] = $request->input('destinatario_livre');

    // 🔹 Adiciona o user_id do utilizador autenticado
    $validated['user_id'] = auth()->id();

    $recado = Recado::create($validated);

    $emails = [];

    if ($request->has('destinatarios_users')) {
        $emails = array_merge(
            $emails,
            User::whereIn('id', $request->destinatarios_users)->pluck('email')->toArray()
        );
    }

    if ($request->filled('destinatarios_grupos')) {
    $grupos = Grupo::with('users')
        ->whereIn('id', $request->destinatarios_grupos)
        ->get();

    foreach ($grupos as $grupo) {
        $recado->destinatarios()->syncWithoutDetaching($grupo->users->pluck('id')->toArray());
    }
}


    if ($request->filled('destinatarios_users')) {
        $recado->destinatarios()->syncWithoutDetaching($request->destinatarios_users);
    }

    if ($request->filled('destinatarios_grupos')) {
        $grupos = Grupo::with('users')->whereIn('destinatarios_grupos')->get();
        foreach ($grupos as $grupo) {
            $recado->destinatarios()->syncWithoutDetaching($grupo->users->pluck('id')->toArray());
        }
    }

    $emails = array_unique($emails);

    foreach ($emails as $email) {
        Mail::to($email)->send(new RecadoCriadoMail($recado));
    }

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


    return redirect()->route('recados.index')->with('success', 'Recado criado e emails enviados.');
}


    public function show($id)
    {
        $recado = Recado::with([
            'sla', 'tipo', 'origem', 'setor', 'departamento', 'destinatarios', 'aviso', 'estado', 'tipoFormulario', 'guestTokens'
        ])->findOrFail($id);

        $user = auth()->user();

        if ($user->cargo?->name !== 'admin' && !$recado->destinatarios->contains($user->id)) {
            abort(403, 'Acesso negado. Este recado não é seu.');
        }

        $estados = Estado::all();
        return view('recados.show', compact('recado', 'estados'));
    }

    public function destroy($id)
    {
        $user = auth()->user();

        if ($user->cargo?->name !== 'admin') {
            abort(403, 'Acesso não autorizado');
        }

        $recado = Recado::findOrFail($id);
        $recado->delete();

        return redirect()->route('recados.index')->with('success', 'Recado apagado com sucesso!');
    }

    public function adicionarComentario(Request $request, Recado $recado)
    {
        $request->validate(['comentario' => 'required|string']);

        $novaLinha = now()->format('d/m/Y H:i') . ' - ' . auth()->user()->name . ': ' . $request->comentario;

        $recado->observacoes = $recado->observacoes
            ? $recado->observacoes . "\n" . $novaLinha
            : $novaLinha;

        if ($recado->estado->name === 'Aguardar') {
            $estadoPendente = Estado::where('name', 'Pendente')->first();
            if ($estadoPendente) {
                $recado->estado_id = $estadoPendente->id;
            }
        }

        $recado->save();

        // Invalida qualquer token de acesso de convidados ao adicionar comentário
        RecadoGuestToken::where('recado_id', $recado->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        return redirect()->back()->with('success', 'Comentário adicionado.');
    }

    public function updateEstado(Request $request, Recado $recado)
    {
        $request->validate([
            'estado_id' => 'required|exists:estados,id',
        ]);

        $recado->estado_id = $request->estado_id;
        $recado->save();

        // Invalida tokens guests sempre que o estado muda
        RecadoGuestToken::where('recado_id', $recado->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        return redirect()->back()->with('success', 'Estado atualizado com sucesso.');
    }

    public function concluir(Recado $recado)
    {
        $estadoTratado = Estado::where('name', 'Tratado')->first();

        if (!$estadoTratado) {
            return redirect()->back()->with('error', 'Estado "Tratado" não encontrado.');
        }

        $recado->estado_id = $estadoTratado->id;
        $recado->termino = now();

        $comentarioSistema = now()->format('d/m/Y H:i') . ' - Sistema: Recado concluído .';

        if ($recado->observacoes) {
            $recado->observacoes .= "\n" . $comentarioSistema;
        } else {
            $recado->observacoes = $comentarioSistema;
        }

        $recado->save();

        // Invalida tokens guests quando se conclui o recado
        RecadoGuestToken::where('recado_id', $recado->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        return redirect()->back()->with('success', 'Recado concluído com sucesso.');
    }


    public function guestView($token)
    {
        $guestToken = RecadoGuestToken::where('token', $token)->firstOrFail();
    
        if (! $guestToken->isValid()) {
            abort(403, 'Link expirado ou inválido.');
        }
    
        $recado = $guestToken->recado;
        $estados = Estado::all();
    
        // Passa as variáveis corretamente
        return view('emails.recados.guest', compact('recado', 'token', 'estados'));
    }
    

    public function guestUpdate(Request $request, $token)
{
    $guestToken = RecadoGuestToken::where('token', $token)->where('is_active', true)->firstOrFail();

    if (! $guestToken->isValid()) {
        abort(403, 'Link expirado ou inválido.');
    }

    $recado = $guestToken->recado;

    $validated = $request->validate([
        'mensagem' => 'nullable|string|max:5000',
        'estado_id' => 'nullable|exists:estados,id',
        'comentario' => 'nullable|string|max:2000',
    ]);

    // Atualiza mensagem
    if (!empty($validated['mensagem'])) {
        $recado->mensagem = $validated['mensagem'];
    }

    // Atualiza estado (bloquear concluir/concluído)
    if (!empty($validated['estado_id'])) {
        $novo = Estado::find($validated['estado_id']);
        if ($novo && strtolower($novo->name) !== 'concluído' && strtolower($novo->name) !== 'tratado') {
            $recado->estado_id = $novo->id;
        }
    }

    // Comentário
    if (!empty($validated['comentario'])) {
        $nomeOuEmail = $recado->destinatario_livre ?? 'Convidado';
        $novaLinha = now()->format('d/m/Y H:i') . ' - ' . $nomeOuEmail . ': ' . $validated['comentario'];
        $recado->observacoes = $recado->observacoes
            ? $recado->observacoes . "\n" . $novaLinha
            : $novaLinha;
    }

    $recado->save();

    // ⚠️ Agora NÃO invalidamos sempre o token.
    // Só expira se o estado já for "Tratado".
    if ($recado->estado && strtolower($recado->estado->name) === 'tratado') {
        $guestToken->update(['is_active' => false]);
    }

    return redirect()->route('recados.guest', ['token' => $token])
                     ->with('success', 'Recado atualizado com sucesso!');
}




public function guestComment(Request $request, $token)
{
    $request->validate([
        'comentario' => 'required|string|max:2000',
        // opcional: 'nome' => 'nullable|string|max:255'
    ]);

    $guestToken = RecadoGuestToken::where('token', $token)->where('is_active', true)->firstOrFail();

    if (! $guestToken->isValid()) {
        abort(403, 'Link expirado ou inválido.');
    }

    $recado = $guestToken->recado;

    // Impedir comentários se o recado já estiver concluído (opcional)
    if ($recado->estado && strtolower($recado->estado->name) === 'concluído') {
        return redirect()->route('recados.guest', ['token' => $token])
                         ->with('error', 'Este recado já está concluído. Não são permitidos comentários.');
    }

    $nome = $request->input('nome') ?: 'Convidado';
    $novaLinha = now()->format('d/m/Y H:i') . ' - ' . $nome . ': ' . $request->comentario;

    $recado->observacoes = $recado->observacoes
        ? $recado->observacoes . "\n" . $novaLinha
        : $novaLinha;

    // Se o estado for "Aguardar" alteramos para "Pendente" (mantém lógica do controller)
    if ($recado->estado && $recado->estado->name === 'Aguardar') {
        $estadoPendente = Estado::where('name', 'Pendente')->first();
        if ($estadoPendente) {
            $recado->estado_id = $estadoPendente->id;
        }
    }

    $recado->save();

    // **Decisão**: não invalidar o token após comentário (permite múltiplos comentários enquanto não expirar).
    // Se quiseres invalidar: $guestToken->update(['is_active' => false]);

    return redirect()->route('recados.guest', ['token' => $token])
                     ->with('success', 'Comentário enviado. Obrigado!');
}


public function export()
{
    $recados = Recado::with(['estado', 'tipoFormulario'])->get();

    return Excel::download(new RecadosExport($recados), 'recados_todos.xlsx');
}

public function exportFiltered(Request $request)
{
    $recados = Recado::with(['estado', 'tipoFormulario'])
        ->when($request->filled('estado_id'), fn($q) =>
            $q->where('estado_id', $request->estado_id)
        )
        ->when($request->filled('tipo_formulario_id'), fn($q) =>
            $q->where('tipo_formulario_id', $request->tipo_formulario_id)
        )
        ->when($request->filled('id'), fn($q) =>
            $q->where('id', $request->id)
        )
        ->when($request->filled('contact_client'), fn($q) =>
            $q->where('contact_client', 'like', '%'.$request->contact_client.'%')
        )
        ->when($request->filled('plate'), fn($q) =>
            $q->where('plate', 'like', '%'.$request->plate.'%')
        )
        ->get();

    return Excel::download(new RecadosExport($recados), 'recados_filtrados.xlsx');
}





}
