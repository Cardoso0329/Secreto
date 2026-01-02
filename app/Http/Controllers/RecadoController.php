<?php

namespace App\Http\Controllers;

use App\Mail\RecadoAvisoMail;
use App\Mail\RecadoCriadoMail;
use App\Models\{
    SLA, Recado, Setor, Origem, Departamento, Aviso, Estado, Tipo, User,
    Destinatario, TipoFormulario, Grupo, Campanha, RecadoGuestToken, Vista
};
use App\Exports\RecadosExport;
use App\Queries\RecadoQuery;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\LengthAwarePaginator;


class RecadoController extends Controller
{
public function index(Request $request)
{
    $user = auth()->user();

    /* ================= DADOS BASE ================= */
    $estados = Estado::orderBy('name')->get();
    $tiposFormulario = TipoFormulario::orderBy('name')->get();
    $vistas = Vista::visiveisPara($user)->orderBy('nome')->get();

    /* ================= QUERY BASE ================= */
    $recados = Recado::with([
        'setor','origem','departamento','destinatarios','estado','sla',
        'tipo','aviso','tipoFormulario','grupos','guestTokens','campanha'
    ]);

    /* ================= APLICAR VISTA ================= */
    if ($request->filled('vista_id')) {
        $vista = Vista::visiveisPara($user)
            ->where('id', $request->vista_id)
            ->firstOrFail();

        $recados = $vista->apply($recados);
    }

    /* ================= FILTROS TEMPORÁRIOS (JSON/Array do formulário) ================= */
    if ($request->filled('filtros')) {
        // Espera-se que 'filtros' seja um array de arrays: [{field, operator, value}, ...]
        $filtros = $request->input('filtros', []);
        $logica = $request->input('logica', 'AND');

        $recados = RecadoQuery::applyFilters($recados, $filtros, $logica);
    }

    /* ================= FILTROS MANUAIS (SOBRESCREVEM A VISTA) ================= */
    foreach (['id','contact_client','plate','estado_id','tipo_formulario_id'] as $field) {
        if ($request->filled($field)) {
            $operator = in_array($field,['contact_client','plate']) ? 'like' : '=';
            $value = in_array($field,['contact_client','plate']) ? '%'.$request->input($field).'%' : $request->input($field);
            $recados->where($field, $operator, $value);
        }
    }

    /* ================= SEGURANÇA / VISIBILIDADE ================= */
    if ($user->cargo?->name !== 'admin') {
        $recados->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereHas('destinatarios', fn ($d) =>
                    $d->where('users.id', $user->id)
              )
              ->orWhereHas('grupos.users', fn ($g) =>
                    $g->where('users.id', $user->id)
              );
        });
    }

    /* ================= ORDENAÇÃO ================= */
    $sortBy  = $request->input('sort_by', 'id');
    $sortDir = $request->input('sort_dir', 'desc');

    $recados = $recados
        ->orderBy($sortBy, $sortDir)
        ->paginate(10)
        ->withQueryString();

    /* ================= GUARDAR FILTROS NA SESSÃO ================= */
    session([
        'recados_filtros' => [
            'estado_id' => $request->estado_id,
            'tipo_formulario_id' => $request->tipo_formulario_id,
            'filtros' => $request->input('filtros', []),
            'logica' => $request->input('logica', 'AND'),
        ]
    ]);

    /* ================= POPUP LOCAL ================= */
    $showPopup = !$request->session()->has('local_trabalho');

    return view('recados.index', compact(
        'recados',
        'estados',
        'tiposFormulario',
        'vistas',
        'showPopup'
    ));
}


     // Editar
   public function edit(Recado $recado)
{

    $estados = Estado::all();
    $tiposFormulario = TipoFormulario::all();
    $users = User::all();
    $grupos = Grupo::all();
    $setores = Setor::all();
    $origens = Origem::all();
    $departamentos = Departamento::all();
    $slas = SLA::all();
    $tipos = Tipo::all();
    $avisos = Aviso::all();
    $campanhas = Campanha::all();

    // Guest tokens já ativos
    $guestEmails = $recado->guestTokens->pluck('email')->toArray();

    return view('recados.edit', compact(
        'recado','estados','tiposFormulario','users','grupos','guestEmails',
        'setores','origens','departamentos','slas','tipos','avisos','campanhas'
    ));
}

public function update(Request $request, Recado $recado)
{
    // Atualiza campos simples
    $recado->name = $request->input('name');
    $recado->contact_client = $request->input('contact_client');
    $recado->plate = $request->input('plate');
    $recado->mensagem = $request->input('mensagem');
    $recado->observacoes = $request->input('observacoes');

    // Atualiza selects (pode ser null)
    $recado->estado_id = $request->input('estado_id') ?: null;
    $recado->tipo_formulario_id = $request->input('tipo_formulario_id') ?: null;
    $recado->sla_id = $request->input('sla_id') ?: null;
    $recado->tipo_id = $request->input('tipo_id') ?: null;
    $recado->origem_id = $request->input('origem_id') ?: null;
    $recado->setor_id = $request->input('setor_id') ?: null;
    $recado->departamento_id = $request->input('departamento_id') ?: null;
    $recado->aviso_id = $request->input('aviso_id') ?: null;
    $recado->campanha_id = $request->input('campanha_id') ?: null;

    // Datas (pode ser null)
    $recado->abertura = $request->input('abertura') ?: null;
    $recado->termino = $request->input('termino') ?: null;

    // Ficheiro
    if($request->hasFile('ficheiro')){
        $file = $request->file('ficheiro');
        $filename = time().'_'.$file->getClientOriginalName();
        $file->storeAs('public/recados', $filename);
        $recado->ficheiro = $filename;
    }

    $recado->save();

    // Atualiza destinatários
    $recado->destinatariosUsers()->sync($request->input('destinatarios_users', []));
    $recado->grupos()->sync($request->input('destinatarios_grupos', []));

   // Emails livres (guest emails) - apenas adiciona novos
if($request->has('destinatarios_livres')){
    foreach($request->destinatarios_livres as $email){
        $email = trim($email);
        if(!empty($email) && !$recado->guestEmails->contains('email', $email)){
            $recado->guestEmails()->create(['email' => $email]);
        }
    }
}



    return redirect()->route('recados.index')->with('success', 'Recado atualizado com sucesso!');
}


    public function store(Request $request)
{
    // Detecta o tipo de formulário
    $tipoFormulario = TipoFormulario::find($request->tipo_formulario_id);

    // Validação condicional
    $rules = [
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
        'estado_id' => 'nullable|exists:estados,id', // será sobrescrito
        'observacoes' => 'nullable|string',
        'abertura' => 'nullable|date',
        'termino' => 'nullable|date',
        'destinatarios_users' => 'array',
        'destinatarios_users.*' => 'exists:users,id',
        'destinatarios_grupos' => 'array',
        'destinatarios_grupos.*' => 'exists:grupos,id',
        'destinatarios_livres' => 'array',
        'destinatarios_livres.*' => 'email',
        'tipo_formulario_id' => 'required|exists:tipo_formularios,id',
        'wip' => 'nullable|string|max:255',
    ];

    // Só valida 'assunto' se for Call Center
    if ($tipoFormulario && strtolower($tipoFormulario->name) === 'call center') {
        $rules['assunto'] = 'required|string|max:255';
    }

    $validated = $request->validate($rules);

    $validated['user_id'] = auth()->id();

    if ($request->hasFile('ficheiro')) {
        $validated['ficheiro'] = basename($request->file('ficheiro')->store('recados','public'));
    }

    // --- Garantir estado inicial "Novo" antes do create ---
    $estadoNovo = Estado::where('name','Novo')->first();
    if ($estadoNovo) {
        $validated['estado_id'] = $estadoNovo->id;
    }

    $recado = Recado::create($validated);

    // DESTINATÁRIOS (users)
    if ($request->filled('destinatarios_users')) {
        $recado->destinatariosUsers()->sync($request->destinatarios_users);

        foreach (User::whereIn('id', $request->destinatarios_users)->pluck('email') as $email) {
            Mail::to($email)->send(new RecadoCriadoMail($recado));
        }
    }

    // DEST LIVRES
    if ($request->filled('destinatarios_livres')) {
        foreach ($request->destinatarios_livres as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $token = Str::random(60);

                RecadoGuestToken::create([
                    'recado_id' => $recado->id,
                    'email' => $email,
                    'token' => $token,
                    'expires_at' => now()->addMonth(),
                    'is_active' => true
                ]);

                Mail::to($email)->send(new RecadoCriadoMail($recado, route('recados.guest', $token)));
            }
        }
    }

    // GRUPOS
    $gruposSelecionados = $request->input('destinatarios_grupos', []);

    // Garantir que Telefonistas esteja sempre incluído
    $telefonistasId = Grupo::where('name','Telefonistas')->first()?->id;
    if ($telefonistasId && !in_array($telefonistasId, $gruposSelecionados)) {
        $gruposSelecionados[] = $telefonistasId;
    }

    $recado->grupos()->sync($gruposSelecionados);

    // Enviar emails para todos os usuários dos grupos
    $emails = User::whereHas('grupos', fn($q) => $q->whereIn('grupos.id', $gruposSelecionados))
        ->pluck('email')->unique();

    foreach ($emails as $email) {
        Mail::to($email)->send(new RecadoCriadoMail($recado));
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

        // --- Passa para Pendente se ainda estiver Novo ---
        $estadoPendente = Estado::where('name','Pendente')->first();
        if ($estadoPendente && strtolower($recado->estado->name)=='novo') {
            $recado->estado_id = $estadoPendente->id;
        }

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
    // Recuperar filtros obrigatórios da sessão
    $filtros = session('recados_filtros', []);

    $query = Recado::with(['estado','tipoFormulario','destinatarios','grupos.users','guestTokens']);

    // Aplicar filtros obrigatórios
    if (!empty($filtros['estado_id'])) {
        $query->where('estado_id', $filtros['estado_id']);
    }
    if (!empty($filtros['tipo_formulario_id'])) {
        $query->where('tipo_formulario_id', $filtros['tipo_formulario_id']);
    }

    // Aplicar filtros manuais vindos da query GET (se houver)
    foreach (['id','contact_client','plate'] as $field) {
        if ($request->filled($field)) {
            $query->where(
                $field,
                in_array($field,['contact_client','plate']) ? 'like' : '=',
                in_array($field,['contact_client','plate']) ? '%'.$request->input($field).'%' : $request->input($field)
            );
        }
    }

    $recados = $query->get();

    return Excel::download(new RecadosExport($recados), 'recados_filtrados.xlsx');
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
