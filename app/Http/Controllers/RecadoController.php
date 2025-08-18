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
        $tipos = Tipo::all();
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
        ->when(!$user->cargo || $user->cargo->name !== 'admin', function ($query) use ($user) {
            $query->whereHas('destinatarios', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        })
        ->when($request->filled('estado_id'), function ($query) use ($request) {
            $query->where('estado_id', $request->estado_id);
        })
        ->when($request->filled('tipo_formulario_id'), function ($query) use ($request) {
            $query->where('tipo_formulario_id', $request->tipo_formulario_id);
        })
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
            'destinatario_livre' => 'nullable|string',
            'destinatario_livre_email' => 'nullable|email',
        ]);

        if ($request->hasFile('ficheiro')) {
            $path = $request->file('ficheiro')->store('recados', 'public');
            $validated['ficheiro'] = basename($path);
        }

        $validated['plate'] = $request->input('plate');
        $validated['destinatario_livre'] = $request->input('destinatario_livre');

        $recado = Recado::create($validated);

        $emails = [];

        if ($request->has('destinatarios_users')) {
            $emails = array_merge(
                $emails,
                User::whereIn('id', $request->destinatarios_users)->pluck('email')->toArray()
            );
        }

        if ($request->has('destinatarios_grupos')) {
            $grupos = Grupo::with('users')->whereIn('id', $request->destinatarios_grupos)->get();
            foreach ($grupos as $grupo) {
                $emails = array_merge($emails, $grupo->users->pluck('email')->toArray());
            }
        }

        if ($request->filled('destinatarios_users')) {
            $recado->destinatarios()->syncWithoutDetaching($request->destinatarios_users);
        }

        if ($request->filled('destinatarios_grupos')) {
            $grupos = Grupo::with('users')->whereIn('id', $request->destinatarios_grupos)->get();
            foreach ($grupos as $grupo) {
                $recado->destinatarios()->syncWithoutDetaching($grupo->users->pluck('id')->toArray());
            }
        }

        $emails = array_unique($emails);

        // Enviar mail para utilizadores/grupos
        foreach ($emails as $email) {
            Mail::to($email)->send(new RecadoCriadoMail($recado));
        }

        // Se houver destinatário livre válido (email), criar token e enviar email com guestUrl
        if ($request->filled('destinatario_livre') && filter_var($request->destinatario_livre, FILTER_VALIDATE_EMAIL)) {
            // criar token
            $token = Str::random(60);

            RecadoGuestToken::create([
                'recado_id' => $recado->id,
                'token' => $token,
                'expires_at' => now()->addMonth(),
                'is_active' => true,
            ]);

            $guestUrl = route('recados.guest', ['token' => $token]);

            // enviar email com guestUrl
            Mail::to($request->destinatario_livre)->send(new RecadoCriadoMail($recado, $guestUrl));
        }

        return redirect()->route('recados.index')->with('success', 'Recado criado e emails enviados.');
    }

    public function show($id)
    {
        $recado = Recado::with([
            'sla', 'tipo', 'origem', 'setor', 'departamento', 'destinatarios', 'aviso', 'estado', 'tipoFormulario'
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

    // Validação: só permitimos campos seguros
    $validated = $request->validate([
        'mensagem' => 'nullable|string|max:5000',
        'estado_id' => 'nullable|exists:estados,id',
        'comentario' => 'nullable|string|max:2000',
    ]);

    // Atualiza mensagem se vier
    if (!empty($validated['mensagem'])) {
        $recado->mensagem = $validated['mensagem'];
    }

    // Atualiza estado com **regras**: aqui limitamos transições (ajusta conforme a tua lógica)
    if (!empty($validated['estado_id'])) {
        $novo = Estado::find($validated['estado_id']);
        // Exemplo de bloqueio: não permitir que convidado defina 'Concluído' diretamente
        if ($novo && strtolower($novo->name) !== 'concluído') {
            $recado->estado_id = $novo->id;
        }
    }

    // Comentário opcional
    if (!empty($validated['comentario'])) {
        $nomeOuEmail = $recado->destinatario_livre ?? 'Convidado';
        $novaLinha = now()->format('d/m/Y H:i') . ' - ' . $nomeOuEmail . ': ' . $validated['comentario'];
        $recado->observacoes = $recado->observacoes
            ? $recado->observacoes . "\n" . $novaLinha
            : $novaLinha;
    }
    

    $recado->save();

    // Segurança: invalidar o token após edição para evitar edições repetidas (recomendado)
    $guestToken->update(['is_active' => false]);

    return redirect()->route('recados.guest', ['token' => $token])
                     ->with('success', 'Recado atualizado e comentário adicionado com sucesso!');
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
    return Excel::download(new RecadosExport, 'recados.xlsx');
}




}
