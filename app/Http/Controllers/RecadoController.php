<?php

namespace App\Http\Controllers;

use App\Mail\RecadoAvisoMail;
use App\Mail\RecadoCriadoMail;
use App\Models\{
    SLA, Recado, Setor, Origem, Departamento, Aviso, Estado, Tipo, User,
    Destinatario, TipoFormulario, Grupo, Campanha, RecadoGuestToken
};
use App\Exports\RecadosExport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class RecadoController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $estados = Estado::all();
        $tiposFormulario = TipoFormulario::all();

        $recados = Recado::with([
            'setor', 'origem', 'departamento', 'destinatarios', 'estado',
            'sla', 'tipo', 'aviso', 'tipoFormulario', 'grupos.users',
            'guestTokens', 'campanha'
        ]);

        if ($user->cargo?->name !== 'admin') {
            $recados->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('destinatarios', fn($q2) => $q2->where('users.id', $user->id))
                  ->orWhereHas('grupos.users', fn($q3) => $q3->where('users.id', $user->id));
            });
        }

        $filtros = $request->query() ?: session('recados_filtros', []);
        if ($request->query()) session(['recados_filtros' => $filtros]);

        // Aplicar filtros
        foreach (['id','contact_client','plate','estado_id','tipo_formulario_id'] as $field) {
            if (!empty($filtros[$field])) {
                $recados->where($field, $field === 'contact_client' || $field === 'plate' 
                    ? 'like' 
                    : '=', $field === 'contact_client' || $field === 'plate' 
                    ? '%' . $filtros[$field] . '%' 
                    : $filtros[$field]);
            }
        }

        // Ordenação
        $sortBy = data_get($filtros, 'sort_by', 'id');
$sortDir = data_get($filtros, 'sort_dir', 'desc');


        $recados = $recados->orderBy($sortBy, $sortDir)->paginate(10)->withQueryString();

        $showPopup = !$request->session()->has('local_trabalho');

        $vis = auth()->user()->visibilidade_recados;

if ($vis === 'nenhum') {
    $recados = collect(); // lista vazia
}

if ($vis === 'campanhas') {
    return redirect()->route('recados_campanhas.index');
}


        return view('recados.index', compact('recados','estados','tiposFormulario','filtros','showPopup'));
    }

    public function create(Request $request)
{
    // Determina o tipo de formulário: query string ou sessão
    $tipoFormularioName = $request->query('tipo_formulario') ?? $request->session()->get('local_trabalho');
    if (!$tipoFormularioName) {
        return redirect()->route('recados.index')->with('error','Tipo de formulário não selecionado.');
    }

    $tipoFormulario = TipoFormulario::where('name', $tipoFormularioName)->first();
    if (!$tipoFormulario) {
        return redirect()->route('recados.index')->with('error','Tipo de formulário inválido.');
    }

    // Dados para o formulário
    $campanhas = Campanha::all(); // agora só será uma campanha
    $setores = Setor::all();
    $origens = Origem::all();
    $departamentos = Departamento::all();
    $slas = SLA::all();
    $tipos = Tipo::whereIn('name', [
        'Pedido de Contacto','Pedido de Informação','Pedido de Marcação',
        'Pedido de Orçamento','Tomada de Conhecimento','Reclamação/Insatisfação'
    ])->get();
    $estados = Estado::all();
    $avisos = Aviso::all();
    $destinatarios = Destinatario::with('user')->get();

    $tipoFormularioId = $tipoFormulario->id;

    // Escolher a view certa conforme o tipo de formulário
    $view = match($tipoFormularioName) {
        'Central' => 'recados.create_central',
        'Call Center' => 'recados.create_callcenter',
        default => null
    };

    if (!$view) return redirect()->route('recados.index')->with('error','Tipo de formulário inválido.');

    // Passa os dados para a view
    return view($view, compact(
        'setores','origens','departamentos','slas','tipos',
        'estados','avisos','destinatarios','tipoFormularioId','campanhas'
    ));
}


    public function store(Request $request)
{
    $user = auth()->user();
    if (!$user->grupos()->where('name','Telefonistas')->exists()) {
        abort(403,'Apenas utilizadores do grupo Telefonistas podem criar recados.');
    }

    $callCenterId = TipoFormulario::where('name','Call Center')->first()->id;

    $rules = [
        'name'=>'required|string|max:255',
        'contact_client'=>'required|string|max:255',
        'plate'=>'nullable|string|max:255',
        'operator_email'=>'nullable|email',
        'sla_id'=>'required|exists:slas,id',
        'tipo_id'=>'required|exists:tipos,id',
        'origem_id'=>'required|exists:origens,id',
        'setor_id'=>'required|exists:setores,id',
        'departamento_id'=>'required|exists:departamentos,id',
        'mensagem'=>'required|string',
        'ficheiro'=>'nullable|file',
        'aviso_id'=>'nullable|exists:avisos,id',
        'estado_id'=>'required|exists:estados,id',
        'observacoes'=>'nullable|string',
        'abertura'=>'nullable|date',
        'termino'=>'nullable|date',
        'destinatarios_users'=>'array',
        'destinatarios_users.*'=>'exists:users,id',
        'destinatarios_grupos'=>'array',
        'destinatarios_grupos.*'=>'exists:grupos,id',
        'destinatarios_livres'=>'array',
        'destinatarios_livres.*'=>'email',
        'tipo_formulario_id'=>'required|exists:tipo_formularios,id',
        'wip'=>'nullable|string|max:255',
        'campanha_id'=>'nullable|exists:campanhas,id'
    ];

    if ($request->tipo_formulario_id == $callCenterId) $rules['assunto']='required|string|max:255';
    else $rules['assunto']='nullable|string|max:255';

    $validated = $request->validate($rules);
    $validated['user_id'] = $user->id;

    if ($request->hasFile('ficheiro')) {
        $validated['ficheiro'] = basename($request->file('ficheiro')->store('recados','public'));
    }

    $recado = Recado::create($validated);

    // Destinatários
    if ($request->filled('destinatarios_users')) {
        $recado->destinatariosUsers()->sync($request->destinatarios_users);
        foreach (User::whereIn('id',$request->destinatarios_users)->pluck('email') as $email) {
            Mail::to($email)->send(new RecadoCriadoMail($recado));
        }
    }

    if ($request->filled('destinatarios_livres')) {
        foreach ($request->destinatarios_livres as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $token = Str::random(60);
                RecadoGuestToken::create([
                    'recado_id'=>$recado->id,
                    'email'=>$email,
                    'token'=>$token,
                    'expires_at'=>now()->addMonth(),
                    'is_active'=>true
                ]);
                Mail::to($email)->send(new RecadoCriadoMail($recado, route('recados.guest', $token)));
            }
        }
    }

    if ($request->filled('destinatarios_grupos')) {
        $recado->grupos()->sync($request->destinatarios_grupos);
        $emails = User::whereHas('grupos', fn($q) => $q->whereIn('grupos.id',$request->destinatarios_grupos))
            ->pluck('email')->toArray();
        foreach(array_unique($emails) as $email){
            Mail::to($email)->send(new RecadoCriadoMail($recado));
        }
    }

    // Garantir estado Pendente
    $estadoPendente = Estado::where('name','Pendente')->first();
    if ($estadoPendente) {
        $recado->estado_id = $estadoPendente->id;
        $recado->save();
    }

    return redirect()->route('recados.index')->with('success','Recado criado e emails enviados.');
}

    public function show($id)
    {
        $recado = Recado::with([
            'sla','tipo','origem','setor','departamento',
            'destinatarios','aviso','estado','tipoFormulario',
            'guestTokens','grupos.users','campanha'
        ])->findOrFail($id);

        $user = auth()->user();
        if ($user->cargo?->name !== 'admin' && $recado->user_id !== $user->id) {
            $isDestinatario = $recado->destinatarios->contains($user->id);
            $isGrupo = $recado->grupos->pluck('users')->flatten()->pluck('id')->contains($user->id);
            if (!$isDestinatario && !$isGrupo) abort(403,'Acesso negado. Este recado não é seu.');
        }

        $avisos = Aviso::all();
        $estados = Estado::all();

        return view('recados.show', compact('recado','estados','avisos'));
    }

    public function destroy($id)
    {
        $user = auth()->user();
        if ($user->cargo?->name !== 'admin') abort(403,'Acesso não autorizado');

        $recado = Recado::findOrFail($id);
        $recado->delete();

        return redirect()->route('recados.index')->with('success','Recado apagado com sucesso!');
    }

    public function adicionarComentario(Request $request, Recado $recado)
    {
        $request->validate(['comentario'=>'required|string']);
        $novaLinha = now()->format('d/m/Y H:i').' - '.auth()->user()->name.': '.$request->comentario;

        $recado->observacoes = $recado->observacoes
            ? $recado->observacoes."\n".$novaLinha
            : $novaLinha;

        $estadoPendente = Estado::where('name','Pendente')->first();
        if ($estadoPendente) $recado->estado_id = $estadoPendente->id;

        $recado->save();
        RecadoGuestToken::where('recado_id',$recado->id)->where('is_active',true)->update(['is_active'=>false]);

        return redirect()->back()->with('success','Comentário adicionado.');
    }

    public function updateEstado(Request $request, Recado $recado)
    {
        $request->validate(['estado_id'=>'required|exists:estados,id']);

        $estadoAntigo = $recado->estado;
        $novoEstado = Estado::find($request->estado_id);
        $user = auth()->user();

        if (!$novoEstado) return redirect()->back()->with('error','Estado inválido.');

        $recado->estado_id = $novoEstado->id;

        $comentarioSistema = null;
        if ($estadoAntigo && strtolower($estadoAntigo->name)=='tratado' && strtolower($novoEstado->name)=='pendente') {
            $comentarioSistema = now()->format('d/m/Y H:i').' - Sistema: Recado reaberto por '.$user->name.'.';
        }
        if (strtolower($novoEstado->name)=='tratado') {
            $comentarioSistema = now()->format('d/m/Y H:i').' - Sistema: Recado concluído por '.$user->name.'.';
            $recado->termino = now();
        }

        if ($comentarioSistema) {
            $recado->observacoes = $recado->observacoes
                ? $recado->observacoes."\n".$comentarioSistema
                : $comentarioSistema;
        }

        $recado->save();
        RecadoGuestToken::where('recado_id',$recado->id)->where('is_active',true)->update(['is_active'=>false]);

        return redirect()->back()->with('success','Estado atualizado com sucesso.');
    }

    public function escolherLocal(Request $request)
    {
        $request->validate(['local'=>'required|in:Central,Call Center']);
        $request->session()->put('local_trabalho',$request->local);
        return redirect()->route('recados.index');
    }

    public function exportFiltered(Request $request)
    {
        $filters = $request->only(['id','contact_client','plate','estado_id','tipo_formulario_id']);
        $query = Recado::with(['estado','tipoFormulario','destinatarios','grupos.users']);

        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                $query->where($key, $key=='contact_client'||$key=='plate'?'like':'=',$key=='contact_client'||$key=='plate'?'%'.$value.'%':$value);
            }
        }

        $recados = $query->get();
        return Excel::download(new RecadosExport($recados),'recados_filtrados.xlsx');
    }

    public function concluir(Recado $recado)
    {
        $estadoTratado = Estado::where('name','Tratado')->first();
        if (!$estadoTratado) return redirect()->back()->with('error','Estado "Tratado" não encontrado.');

        $recado->estado_id = $estadoTratado->id;
        $recado->termino = now();

        $comentarioSistema = now()->format('d/m/Y H:i').' - Sistema: Recado concluído por '.auth()->user()->name.'.';
        $recado->observacoes = $recado->observacoes
            ? $recado->observacoes."\n".$comentarioSistema
            : $comentarioSistema;

        $recado->save();
        RecadoGuestToken::where('recado_id',$recado->id)->where('is_active',true)->update(['is_active'=>false]);

        return redirect()->back()->with('success','Recado concluído com sucesso.');
    }

    public function enviarAviso(Recado $recado, Aviso $aviso)
    {
        $emails = $recado->destinatarios->pluck('email')->toArray();
        if($recado->guestTokens->count()) {
            $emails = array_merge($emails,$recado->guestTokens->pluck('email')->toArray());
        }

        foreach ($emails as $email) {
            Mail::to($email)->send(new RecadoAvisoMail($recado,$aviso));
        }

        return back()->with('success','Aviso enviado com sucesso!');
    }
}
