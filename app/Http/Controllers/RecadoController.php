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
use Illuminate\Support\Collection;

class RecadoController extends Controller
{
    /**
     * ✅ Users do departamento (via pivot department_user)
     * Nota: requer User::departamentos() e Departamento::users()
     */
    private function departmentUserIds(?int $departamentoId)
    {
        $departamentoId = (int) ($departamentoId ?? 0);
        if ($departamentoId <= 0) return collect();

        return User::whereHas('departamentos', function ($q) use ($departamentoId) {
                $q->where('departamentos.id', $departamentoId);
            })
            ->pluck('users.id')
            ->map(fn ($id) => (int) $id);
    }

    /**
     * ✅ Emails do departamento (com validação real)
     */
    private function departmentEmails(?int $departamentoId)
    {
        $ids = $this->departmentUserIds($departamentoId);
        if ($ids->isEmpty()) return collect();

        return User::whereIn('id', $ids->all())
            ->pluck('email')
            ->map(fn($e) => strtolower(trim((string)$e)))
            ->filter(fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();
    }

    /**
     * ✅ Emails finais para "Responder a todos"
     * Inclui: destinatários diretos + grupos + dept + guests + criador
     */
    private function emailsResponderATodos(Recado $recado): Collection
    {
        $recado->loadMissing(['destinatarios', 'grupos.users', 'guestTokens', 'departamento']);

        $blacklist = collect([
            'callcenter.recados@soccsantos.pt',
        ])->map(fn($e) => strtolower(trim($e)));

        $emailsDestinatarios = $recado->destinatarios->pluck('email');

        $emailsGrupos = $recado->grupos
            ->pluck('users')
            ->flatten()
            ->pluck('email');

        $emailsDept = $this->departmentEmails($recado->departamento_id);

        $emailsGuests = $recado->guestTokens->pluck('email');

        $emailsCriador = User::where('id', $recado->user_id)->pluck('email');

        return collect()
            ->merge($emailsDestinatarios)
            ->merge($emailsGrupos)
            ->merge($emailsDept)
            ->merge($emailsGuests)
            ->merge($emailsCriador)
            ->map(fn($e) => strtolower(trim((string)$e)))
            ->filter(fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
            ->reject(fn($e) => $blacklist->contains($e))
            ->unique()
            ->values();
    }

    /**
     * ✅ Sanitiza e devolve array simples de emails
     */
    private function cleanEmails(Collection $emails): array
    {
        return $emails
            ->map(fn($e) => strtolower(trim((string)$e)))
            ->filter(fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->toArray();
    }

    /* ================= LISTAGEM ================= */

    public function index(Request $request)
    {
        $user = auth()->user();

        $estados = Estado::orderBy('name')->get();
        $tiposFormulario = TipoFormulario::orderBy('name')->get();

        $vistas = collect(\App\Services\VistaService::visiveisPara($user));

        $recados = Recado::with([
            'setor','origem','departamento','destinatarios','estado','sla',
            'tipo','aviso','tipoFormulario','grupos','guestTokens','campanha'
        ]);

        $manualFields = ['id','contact_client','plate','estado_id','tipo_formulario_id'];

        $temFiltrosManuais =
            $request->filled('filtros') ||
            collect($manualFields)->contains(fn ($f) => $request->filled($f));

        if ($request->has('vista_id')) {
            $vistaId = $request->input('vista_id');
            if ($vistaId) $request->session()->put('recados_vista_id', $vistaId);
            else $request->session()->forget('recados_vista_id');
        }

        $vistaId = $request->filled('vista_id')
            ? $request->input('vista_id')
            : $request->session()->get('recados_vista_id');

        $vistaFiltros = [];

        if (!$temFiltrosManuais && !empty($vistaId)) {

            $vista = \App\Services\VistaRepo::findOrFail($vistaId);

            if (!$vistas->pluck('id')->contains($vista['id'])) abort(403);

            $vistaFiltros = $vista['filtros'] ?? [];

            if (is_array($vistaFiltros) && array_key_exists('conditions', $vistaFiltros)) {
                $vistaFiltros = $vistaFiltros['conditions'] ?? [];
            }

            $recados = \App\Queries\RecadoQuery::applyFilters(
                $recados,
                $vistaFiltros,
                $vista['logica'] ?? 'AND'
            );
        }

        if ($request->filled('filtros')) {
            $recados = \App\Queries\RecadoQuery::applyFilters(
                $recados,
                $request->input('filtros', []),
                $request->input('logica', 'AND')
            );
        }

        foreach ($manualFields as $field) {
            if ($request->filled($field)) {
                $operator = in_array($field, ['contact_client','plate']) ? 'LIKE' : '=';
                $value = in_array($field, ['contact_client','plate'])
                    ? '%'.$request->input($field).'%'
                    : $request->input($field);

                $recados->where($field, $operator, $value);
            }
        }

        $deptIdsDaVista = collect();
        $permitirVerDepartamentoDaVista = false;

        if (!$temFiltrosManuais && !empty($vistaId)) {
            $deptIdsDaVista = collect($vistaFiltros)
                ->filter(fn ($f) => is_array($f))
                ->filter(fn ($f) => ($f['field'] ?? null) === 'departamento_id')
                ->filter(fn ($f) => ($f['operator'] ?? '=') === '=')
                ->pluck('value')
                ->filter(fn ($v) => $v !== null && $v !== '')
                ->map(fn ($v) => (int) $v)
                ->unique()
                ->values();

            if ($deptIdsDaVista->isNotEmpty()) {
                $meusDeptIds = $user->departamentos()
                    ->pluck('departamentos.id')
                    ->map(fn ($v) => (int) $v);

                $permitidos = $deptIdsDaVista->intersect($meusDeptIds);

                if ($permitidos->isNotEmpty()) {
                    $permitirVerDepartamentoDaVista = true;
                    $deptIdsDaVista = $permitidos;
                } else {
                    $deptIdsDaVista = collect();
                }
            }
        }

        if ($user->cargo?->name !== 'admin') {
            $uid = (int) $user->id;

            $recados->where(function ($q) use ($uid, $permitirVerDepartamentoDaVista, $deptIdsDaVista) {
                $q->where('user_id', $uid)
                  ->orWhereHas('destinatarios', fn ($d) => $d->where('users.id', $uid));

                if ($permitirVerDepartamentoDaVista && $deptIdsDaVista->isNotEmpty()) {
                    $q->orWhereIn('departamento_id', $deptIdsDaVista->all());
                }
            });
        }

        $sortBy  = $request->input('sort_by', 'id');
        $sortDir = $request->input('sort_dir', 'desc');

        $recados = $recados
            ->orderBy($sortBy, $sortDir)
            ->paginate(10)
            ->withQueryString();

        $showPopup = !$request->session()->has('local_trabalho');

        return view('recados.index', compact(
            'recados',
            'estados',
            'tiposFormulario',
            'vistas',
            'showPopup'
        ));
    }

    public function create(Request $request)
    {
        $user = auth()->user();

        if ($user->grupos->contains('name', 'Telefonistas') && !$request->session()->has('local_trabalho')) {
            return redirect()->route('recados.index');
        }

        $localTrabalho = $request->session()->get('local_trabalho');

        $estados = Estado::orderBy('name')->get();
        $tiposFormulario = TipoFormulario::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $grupos = Grupo::orderBy('name')->get();
        $setores = Setor::orderBy('name')->get();
        $origens = Origem::orderBy('name')->get();
        $departamentos = Departamento::orderBy('name')->get();
        $slas = SLA::orderBy('name')->get();
        $tipos = Tipo::orderBy('name')->get();
        $avisos = Aviso::orderBy('name')->get();
        $campanhas = Campanha::orderBy('name')->get();

        $nomeTipo = strtolower(trim((string)$localTrabalho));
        $tipoFormularioId = $tiposFormulario
            ->first(fn ($t) => strtolower(trim($t->name)) === $nomeTipo)
            ?->id;

        if (!$tipoFormularioId) {
            $tipoFormularioId = $tiposFormulario
                ->first(fn ($t) => strtolower(trim($t->name)) === 'central')
                ?->id;
        }

        $view = match ($nomeTipo) {
            'call center' => 'recados.create_callcenter',
            'central' => 'recados.create_central',
            default => 'recados.create_central',
        };

        return view($view, compact(
            'estados',
            'tiposFormulario',
            'tipoFormularioId',
            'users',
            'grupos',
            'setores',
            'origens',
            'departamentos',
            'slas',
            'tipos',
            'avisos',
            'campanhas',
            'localTrabalho'
        ));
    }

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

        $guestEmails = $recado->guestTokens->pluck('email')->toArray();

        return view('recados.edit', compact(
            'recado','estados','tiposFormulario','users','grupos','guestEmails',
            'setores','origens','departamentos','slas','tipos','avisos','campanhas'
        ));
    }

    public function update(Request $request, Recado $recado)
    {
        $recado->name = $request->input('name');
        $recado->contact_client = $request->input('contact_client');
        $recado->plate = $request->input('plate');
        $recado->mensagem = $request->input('mensagem');
        $recado->observacoes = $request->input('observacoes');

        $recado->estado_id = $request->input('estado_id') ?: null;
        $recado->tipo_formulario_id = $request->input('tipo_formulario_id') ?: null;
        $recado->sla_id = $request->input('sla_id') ?: null;
        $recado->tipo_id = $request->input('tipo_id') ?: null;
        $recado->origem_id = $request->input('origem_id') ?: null;
        $recado->setor_id = $request->input('setor_id') ?: null;
        $recado->departamento_id = $request->input('departamento_id') ?: null;
        $recado->aviso_id = $request->input('aviso_id') ?: null;
        $recado->campanha_id = $request->input('campanha_id') ?: null;

        $recado->abertura = $request->input('abertura') ?: null;
        $recado->termino = $request->input('termino') ?: null;

        if ($request->hasFile('ficheiro')) {
            $file = $request->file('ficheiro');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->storeAs('public/recados', $filename);
            $recado->ficheiro = $filename;
        }

        $recado->save();

        $recado->destinatariosUsers()->sync($request->input('destinatarios_users', []));
        $recado->grupos()->sync($request->input('destinatarios_grupos', []));

        if ($request->has('destinatarios_livres')) {
            foreach ($request->destinatarios_livres as $email) {
                $email = strtolower(trim((string)$email));
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) continue;

                if (!$recado->guestEmails->contains('email', $email)) {
                    $recado->guestEmails()->create(['email' => $email]);
                }
            }
        }

        return redirect()->route('recados.index')->with('success', 'Recado atualizado com sucesso!');
    }

    /* ================= CREATE (ENVIO EMAIL) ================= */

    public function store(Request $request)
    {
        $tipoFormulario = TipoFormulario::find($request->tipo_formulario_id);

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
            'estado_id' => 'nullable|exists:estados,id',
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

        if ($tipoFormulario && strtolower($tipoFormulario->name) === 'call center') {
            $rules['assunto'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);
        $validated['user_id'] = auth()->id();

        if ($request->hasFile('ficheiro')) {
            $validated['ficheiro'] = basename($request->file('ficheiro')->store('recados', 'public'));
        }

        $estadoNovo = Estado::where('name', 'Novo')->first();
        if ($estadoNovo) $validated['estado_id'] = $estadoNovo->id;

        $recado = Recado::create($validated);

        // 1) Telefonistas sempre incluído nos grupos
        $gruposSelecionados = $request->input('destinatarios_grupos', []);
        $telefonistasId = Grupo::where('name', 'Telefonistas')->first()?->id;
        if ($telefonistasId && !in_array($telefonistasId, $gruposSelecionados)) {
            $gruposSelecionados[] = $telefonistasId;
        }
        $recado->grupos()->sync($gruposSelecionados);

        // 2) Destinatários manuais
        $userIdsSelecionados = collect($request->input('destinatarios_users', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $recado->destinatariosUsers()->sync($userIdsSelecionados->all());

        // 3) Emails do departamento
        $depId = (int) ($validated['departamento_id'] ?? 0);
        $emailsDept = $this->departmentEmails($depId);

        // 4) Emails dos grupos (inclui telefonistas)
        $emailsGrupos = User::whereHas('grupos', fn ($q) => $q->whereIn('grupos.id', $gruposSelecionados))
            ->pluck('email')
            ->map(fn($e) => strtolower(trim((string)$e)))
            ->filter(fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();

        // 5) Emails dos destinatários manuais
        $emailsSelecionados = User::whereIn('id', $userIdsSelecionados->all())
            ->pluck('email')
            ->map(fn($e) => strtolower(trim((string)$e)))
            ->filter(fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();

        // 6) Email do criador (entra no Reply All)
        $emailsCriador = User::where('id', $recado->user_id)
            ->pluck('email')
            ->map(fn($e) => strtolower(trim((string)$e)))
            ->filter(fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
            ->values();

        // 7) Guests do request (para aparecerem no header do email geral)
        $emailsGuestsRequest = collect($request->input('destinatarios_livres', []))
            ->map(fn($e) => strtolower(trim((string)$e)))
            ->filter(fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();

        // ✅ Email geral: TUDO no header => Reply All vai para TODOS
        $emailsTodosNoHeader = collect()
            ->merge($emailsSelecionados)
            ->merge($emailsDept)
            ->merge($emailsGrupos)
            ->merge($emailsGuestsRequest)
            ->merge($emailsCriador)
            ->reject(fn($e) => $e === 'callcenter.recados@soccsantos.pt')
            ->unique()
            ->values();

        $toEmails = $this->cleanEmails($emailsTodosNoHeader);

        if (!empty($toEmails)) {
            Mail::to($toEmails)->send(new RecadoCriadoMail($recado));
        }

        // ✅ Token individual só para guests (email separado)
        foreach ($emailsGuestsRequest as $email) {
            $token = Str::random(60);

            RecadoGuestToken::create([
                'recado_id' => $recado->id,
                'email' => $email,
                'token' => $token,
                'expires_at' => now()->addMonth(),
                'is_active' => true
            ]);

            Mail::to($email)->send(
                new RecadoCriadoMail($recado, route('recados.guest', $token))
            );
        }

        return redirect()->route('recados.index')->with('success', 'Recado criado e emails enviados.');
    }

    /* ================= SHOW ================= */

    public function show($id)
    {
        $recado = Recado::with([
            'sla','tipo','origem','setor','departamento',
            'destinatarios','aviso','estado','tipoFormulario',
            'guestTokens','grupos.users','campanha'
        ])->findOrFail($id);

        $user = auth()->user();

        if ($user->cargo?->name !== 'admin' && $recado->user_id !== $user->id) {
            $uid = (int) $user->id;

            $isDestinatario = $recado->destinatarios->contains($uid);
            $isGrupo = $recado->grupos->pluck('users')->flatten()->pluck('id')->contains($uid);

            $isDepartamento = false;
            if ($recado->departamento_id) {
                $isDepartamento = $recado->departamento
                    ? $recado->departamento->users()->where('users.id', $uid)->exists()
                    : false;
            }

            if (!$isDestinatario && !$isGrupo && !$isDepartamento) {
                abort(403, 'Acesso negado. Este recado não é seu.');
            }
        }

        $avisos = Aviso::all();
        $estados = Estado::all();

        return view('recados.show', compact('recado','estados','avisos'));
    }

    public function destroy($id)
    {
        $user = auth()->user();
        if ($user->cargo?->name !== 'admin') abort(403, 'Acesso não autorizado');

        $recado = Recado::findOrFail($id);
        $recado->delete();

        return redirect()->route('recados.index')->with('success', 'Recado apagado com sucesso!');
    }

    /* ================= COMENTÁRIO (EMAIL PARA TODOS) ================= */

    public function adicionarComentario(Request $request, Recado $recado)
    {
        $request->validate(['comentario' => 'required|string']);

        $user = auth()->user();
        $novaLinha = now()->format('d/m/Y H:i').' - '.$user->name.': '.$request->comentario;

        $recado->observacoes = $recado->observacoes
            ? $recado->observacoes."\n".$novaLinha
            : $novaLinha;

        $estadoPendente = Estado::where('name','Pendente')->first();
        if ($estadoPendente && $recado->estado && strtolower($recado->estado->name) === 'novo') {
            $recado->estado_id = $estadoPendente->id;
        }

        $recado->save();

        // ✅ lista completa para Reply All
        $emails = $this->emailsResponderATodos($recado);

        // opcional: quem comentou não recebe
        if ($user->email) {
            $emails = $emails->reject(fn($e) => strtolower($e) === strtolower($user->email))->values();
        }

        $avisoComentario = Aviso::where('name', 'Novo Comentário')->first() ?? Aviso::orderBy('id')->first();

        $enviados = 0;

        if ($avisoComentario) {
            $toEmails = $this->cleanEmails($emails);

            if (!empty($toEmails)) {
                Mail::to($toEmails)->send(new RecadoAvisoMail($recado, $avisoComentario));
                $enviados = count($toEmails);
            }
        }

        RecadoGuestToken::where('recado_id', $recado->id)->where('is_active', true)->update(['is_active' => false]);

        $msg = $avisoComentario
            ? "Comentário adicionado e enviado a todos ($enviados)."
            : "Comentário adicionado. (Sem email: não existe nenhum Aviso para enviar)";

        return redirect()->back()->with('success', $msg);
    }

    public function updateEstado(Request $request, Recado $recado)
    {
        $request->validate(['estado_id' => 'required|exists:estados,id']);

        $estadoAntigo = $recado->estado;
        $novoEstado = Estado::find($request->estado_id);
        $user = auth()->user();

        if (!$novoEstado) return redirect()->back()->with('error', 'Estado inválido.');

        $recado->estado_id = $novoEstado->id;

        $comentarioSistema = null;
        if ($estadoAntigo && strtolower($estadoAntigo->name) == 'tratado' && strtolower($novoEstado->name) == 'pendente') {
            $comentarioSistema = now()->format('d/m/Y H:i').' - Sistema: Recado reaberto por '.$user->name.'.';
        }
        if (strtolower($novoEstado->name) == 'tratado') {
            $comentarioSistema = now()->format('d/m/Y H:i').' - Sistema: Recado concluído por '.$user->name.'.';
            $recado->termino = now();
        }

        if ($comentarioSistema) {
            $recado->observacoes = $recado->observacoes
                ? $recado->observacoes."\n".$comentarioSistema
                : $comentarioSistema;
        }

        $recado->save();
        RecadoGuestToken::where('recado_id', $recado->id)->where('is_active', true)->update(['is_active' => false]);

        return redirect()->back()->with('success', 'Estado atualizado com sucesso.');
    }

    public function escolherLocal(Request $request)
    {
        $request->validate(['local' => 'required|in:Central,Call Center']);
        $request->session()->put('local_trabalho', $request->local);
        return redirect()->route('recados.index');
    }

    public function exportFiltered(Request $request)
    {
        $user = auth()->user();

        $query = Recado::with([
            'setor','origem','departamento','destinatarios','estado','sla',
            'tipo','aviso','tipoFormulario','grupos','guestTokens','campanha'
        ]);

        $vistaId = $request->filled('vista_id')
            ? $request->input('vista_id')
            : $request->session()->get('recados_vista_id');

        $manualFields = ['id','contact_client','plate','estado_id','tipo_formulario_id'];

        $temFiltrosManuais =
            $request->filled('filtros') ||
            collect($manualFields)->contains(fn ($f) => $request->filled($f));

        if (!$temFiltrosManuais && !empty($vistaId)) {
            $vistas = collect(\App\Services\VistaService::visiveisPara($user));
            $vista = \App\Services\VistaRepo::findOrFail($vistaId);

            if (!$vistas->pluck('id')->contains($vista['id'])) abort(403);

            $vistaFiltros = $vista['filtros'] ?? [];
            if (is_array($vistaFiltros) && array_key_exists('conditions', $vistaFiltros)) {
                $vistaFiltros = $vistaFiltros['conditions'] ?? [];
            }

            $query = \App\Queries\RecadoQuery::applyFilters(
                $query,
                $vistaFiltros,
                $vista['logica'] ?? 'AND'
            );
        }

        if ($request->filled('filtros')) {
            $query = \App\Queries\RecadoQuery::applyFilters(
                $query,
                $request->input('filtros', []),
                $request->input('logica', 'AND')
            );
        }

        foreach ($manualFields as $field) {
            if ($request->filled($field)) {
                $operator = in_array($field, ['contact_client','plate']) ? 'LIKE' : '=';
                $value = in_array($field, ['contact_client','plate'])
                    ? '%'.$request->input($field).'%'
                    : $request->input($field);

                $query->where($field, $operator, $value);
            }
        }

        if ($user->cargo?->name !== 'admin') {
            $uid = (int) $user->id;

            $query->where(function ($q) use ($uid) {
                $q->where('user_id', $uid)
                  ->orWhereHas('destinatarios', fn ($d) => $d->where('users.id', $uid))
                  ->orWhereHas('grupos.users', fn ($g) => $g->where('users.id', $uid))
                  ->orWhereHas('departamento.users', fn ($u) => $u->where('users.id', $uid));
            });
        }

        $sortBy  = $request->input('sort_by', 'id');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $recados = $query->get();

        return Excel::download(new RecadosExport($recados), 'recados_filtrados.xlsx');
    }

    public function concluir(Recado $recado)
    {
        $estadoTratado = Estado::where('name', 'Tratado')->first();
        if (!$estadoTratado) return redirect()->back()->with('error', 'Estado "Tratado" não encontrado.');

        $recado->estado_id = $estadoTratado->id;
        $recado->termino = now();

        $comentarioSistema = now()->format('d/m/Y H:i').' - Sistema: Recado concluído por '.auth()->user()->name.'.';
        $recado->observacoes = $recado->observacoes
            ? $recado->observacoes."\n".$comentarioSistema
            : $comentarioSistema;

        $recado->save();
        RecadoGuestToken::where('recado_id', $recado->id)->where('is_active', true)->update(['is_active' => false]);

        return redirect()->back()->with('success', 'Recado concluído com sucesso.');
    }

    /**
     * ✅ Aviso: usa emailsResponderATodos() (inclui grupos + dept + guests + criador)
     */
    public function enviarAviso(Recado $recado, Aviso $aviso)
    {
        $emails = $this->emailsResponderATodos($recado);

        $toEmails = $this->cleanEmails($emails);

        if (!empty($toEmails)) {
            Mail::to($toEmails)->send(new RecadoAvisoMail($recado, $aviso));
        }

        return back()->with('success', 'Aviso enviado com sucesso!');
    }
}
