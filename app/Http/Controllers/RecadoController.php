<?php

namespace App\Http\Controllers;

use App\Mail\RecadoAvisoMail;
use App\Mail\RecadoCriadoMail;
use App\Models\{
    SLA, Recado, Setor, Origem, Departamento, Aviso, Estado, Tipo, User,
    Destinatario, TipoFormulario, Grupo, Campanha, RecadoGuestToken, Vista, Chefia
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

    private function departmentEmails(?int $departamentoId)
    {
        $ids = $this->departmentUserIds($departamentoId);
        if ($ids->isEmpty()) return collect();

        return User::whereIn('id', $ids->all())
            ->pluck('email')
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * ✅ Users da chefia (via pivot chefia_user)
     * Nota: requer User::chefias() e Chefia::users()
     */
    private function chefiaUserIds(?int $chefiaId)
    {
        $chefiaId = (int) ($chefiaId ?? 0);
        if ($chefiaId <= 0) return collect();

        return User::whereHas('chefias', function ($q) use ($chefiaId) {
                $q->where('chefias.id', $chefiaId);
            })
            ->pluck('users.id')
            ->map(fn ($id) => (int) $id);
    }

    private function chefiaEmails(?int $chefiaId)
    {
        $ids = $this->chefiaUserIds($chefiaId);
        if ($ids->isEmpty()) return collect();

        return User::whereIn('id', $ids->all())
            ->pluck('email')
            ->filter()
            ->unique()
            ->values();
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        /* ================= DADOS BASE ================= */
        $estados = Estado::orderBy('name')->get();
        $tiposFormulario = TipoFormulario::orderBy('name')->get();

        // vistas visíveis para o user (arrays)
        $vistas = collect(\App\Services\VistaService::visiveisPara($user));

        /* ================= QUERY BASE ================= */
        $recados = Recado::with([
            'setor','origem','departamento','chefia','destinatarios','estado','sla',
            'tipo','aviso','tipoFormulario','grupos','guestTokens','campanha'
        ]);

        /* ================= DETETAR FILTROS MANUAIS ================= */
        $manualFields = ['id','contact_client','plate','estado_id','tipo_formulario_id'];

        $temFiltrosManuais =
            $request->filled('filtros') ||
            collect($manualFields)->contains(fn ($f) => $request->filled($f));

        /* ================= VISTA ATIVA (GET + SESSÃO) ================= */
        if ($request->has('vista_id')) {
            $vistaId = $request->input('vista_id');

            if ($vistaId) $request->session()->put('recados_vista_id', $vistaId);
            else $request->session()->forget('recados_vista_id');
        }

        $vistaId = $request->filled('vista_id')
            ? $request->input('vista_id')
            : $request->session()->get('recados_vista_id');

        $vistaFiltros = [];

        /* ================= APLICAR VISTA (SÓ SE NÃO HÁ FILTROS MANUAIS) ================= */
        if (!$temFiltrosManuais && !empty($vistaId)) {

            $vista = \App\Services\VistaRepo::findOrFail($vistaId);

            if (!$vistas->pluck('id')->contains($vista['id'])) abort(403);

            $vistaFiltros = $vista['filtros'] ?? [];

            // aceita formato antigo {conditions: []}
            if (is_array($vistaFiltros) && array_key_exists('conditions', $vistaFiltros)) {
                $vistaFiltros = $vistaFiltros['conditions'] ?? [];
            }

            $recados = \App\Queries\RecadoQuery::applyFilters(
                $recados,
                $vistaFiltros,
                $vista['logica'] ?? 'AND'
            );
        }

        /* ================= FILTROS TEMPORÁRIOS ================= */
        if ($request->filled('filtros')) {
            $recados = \App\Queries\RecadoQuery::applyFilters(
                $recados,
                $request->input('filtros', []),
                $request->input('logica', 'AND')
            );
        }

        /* ================= FILTROS MANUAIS ================= */
        foreach ($manualFields as $field) {
            if ($request->filled($field)) {
                $operator = in_array($field, ['contact_client','plate']) ? 'LIKE' : '=';
                $value = in_array($field, ['contact_client','plate'])
                    ? '%'.$request->input($field).'%'
                    : $request->input($field);

                $recados->where($field, $operator, $value);
            }
        }

        /* =========================================================
           ✅ EXPANDIR VISIBILIDADE APENAS POR VISTA DE DEPARTAMENTO/CHEFIA
           - Só faz sentido se:
             (1) há vista ativa
             (2) NÃO há filtros manuais
           ========================================================= */

        // ===== Departamento (vista)
        $deptIdsDaVista = collect();
        $permitirVerDepartamentoDaVista = false;

        if (!$temFiltrosManuais && !empty($vistaId)) {
            $deptIdsDaVista = collect($vistaFiltros)
                ->filter(fn ($f) => is_array($f))
                ->filter(fn ($f) => ($f['field'] ?? null) === 'departamento_id')
                ->filter(fn ($f) => ($f['operator'] ?? '=') === '=') // só "="
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
                    $deptIdsDaVista = collect(); // segurança
                }
            }
        }

        // ===== Chefia (vista)
        $chefiaIdsDaVista = collect();
        $permitirVerChefiaDaVista = false;

        if (!$temFiltrosManuais && !empty($vistaId)) {
            $chefiaIdsDaVista = collect($vistaFiltros)
                ->filter(fn ($f) => is_array($f))
                ->filter(fn ($f) => ($f['field'] ?? null) === 'chefia_id')
                ->filter(fn ($f) => ($f['operator'] ?? '=') === '=') // só "="
                ->pluck('value')
                ->filter(fn ($v) => $v !== null && $v !== '')
                ->map(fn ($v) => (int) $v)
                ->unique()
                ->values();

            if ($chefiaIdsDaVista->isNotEmpty()) {
                $minhasChefiasIds = $user->chefias()
                    ->pluck('chefias.id')
                    ->map(fn ($v) => (int) $v);

                $permitidos = $chefiaIdsDaVista->intersect($minhasChefiasIds);

                if ($permitidos->isNotEmpty()) {
                    $permitirVerChefiaDaVista = true;
                    $chefiaIdsDaVista = $permitidos;
                } else {
                    $chefiaIdsDaVista = collect(); // segurança
                }
            }
        }

        /* ================= VISIBILIDADE ================= */
        if ($user->cargo?->name !== 'admin') {

            $uid = (int) $user->id;

            // ✅ Se há VISTA ativa (e não há filtros manuais), não aplicamos "só meus"
            // para não estragar as vistas.
            $haVistaAtiva = (!$temFiltrosManuais && !empty($vistaId));

            if (!$haVistaAtiva) {
                $recados->where(function ($q) use ($uid) {
                    $q->where('user_id', $uid)
                      ->orWhereHas('destinatarios', fn ($d) => $d->where('users.id', $uid));
                });
            } else {
                // ✅ Quando há vista, podes expandir por departamento/chefia apenas se pertencer ao user
                $recados->where(function ($q) use ($uid, $permitirVerDepartamentoDaVista, $deptIdsDaVista, $permitirVerChefiaDaVista, $chefiaIdsDaVista) {

                    // mantém acesso normal também (opcional, mas bom)
                    $q->where('user_id', $uid)
                      ->orWhereHas('destinatarios', fn ($d) => $d->where('users.id', $uid));

                    if ($permitirVerDepartamentoDaVista && $deptIdsDaVista->isNotEmpty()) {
                        $q->orWhereIn('departamento_id', $deptIdsDaVista->all());
                    }

                    if ($permitirVerChefiaDaVista && $chefiaIdsDaVista->isNotEmpty()) {
                        $q->orWhereIn('chefia_id', $chefiaIdsDaVista->all());
                    }
                });
            }
        }

        /* ================= ORDENAÇÃO ================= */
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

        $localTrabalho = $request->session()->get('local_trabalho'); // "Central" ou "Call Center"

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

        // ✅ Chefias
        $chefias = Chefia::orderBy('name')->get();

        $nomeTipo = strtolower(trim((string)$localTrabalho)); // "central" ou "call center"
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
            'localTrabalho',
            'chefias'
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

        $chefias = Chefia::orderBy('name')->get();

        $guestEmails = $recado->guestTokens->pluck('email')->toArray();

        return view('recados.edit', compact(
            'recado','estados','tiposFormulario','users','grupos','guestEmails',
            'setores','origens','departamentos','slas','tipos','avisos','campanhas',
            'chefias'
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

        // ✅ Chefia
        $recado->chefia_id = $request->input('chefia_id') ?: null;

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
                $email = trim($email);
                if (!empty($email) && !$recado->guestEmails->contains('email', $email)) {
                    $recado->guestEmails()->create(['email' => $email]);
                }
            }
        }

        return redirect()->route('recados.index')->with('success', 'Recado atualizado com sucesso!');
    }

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
            'departamento_id' => 'nullable|exists:departamentos,id',
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
            'tipo_formulario_id' => 'exists:tipo_formularios,id',
            'wip' => 'nullable|string|max:255',

            // ✅ Chefia
            'chefia_id' => 'nullable|exists:chefias,id',
        ];

        if ($tipoFormulario && strtolower(trim($tipoFormulario->name)) === 'call center') {
            $rules['assunto'] = 'required|string|max:255';
            $rules['chefia_id'] = 'exists:chefias,id';
        }

        $validated = $request->validate($rules);
        $validated['user_id'] = auth()->id();

        if ($request->hasFile('ficheiro')) {
            $validated['ficheiro'] = basename($request->file('ficheiro')->store('recados', 'public'));
        }

        $estadoNovo = Estado::where('name', 'Novo')->first();
        if ($estadoNovo) {
            $validated['estado_id'] = $estadoNovo->id;
        }

        $recado = Recado::create($validated);

        /* ==========================================================
           ✅ EMAILS:
           - Users selecionados manualmente
           - Users do departamento (pivot)
           - Users da chefia (pivot) ✅ NOVO
           - Users do(s) grupo(s)
           ========================================================== */

        // 1) Telefonistas sempre incluído nos grupos
        $gruposSelecionados = $request->input('destinatarios_grupos', []);
        $telefonistasId = Grupo::where('name', 'Telefonistas')->first()?->id;

        if ($telefonistasId && !in_array($telefonistasId, $gruposSelecionados)) {
            $gruposSelecionados[] = $telefonistasId;
        }

        $gruposSelecionados = collect($gruposSelecionados)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $recado->grupos()->sync($gruposSelecionados);

        // 2) Destinatários MANUAIS (ficam associados ao recado)
        $userIdsSelecionados = collect($request->input('destinatarios_users', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $recado->destinatariosUsers()->sync($userIdsSelecionados->all());

        // 3) Users do departamento (email)
        $depId = (int) ($validated['departamento_id'] ?? 0);
        $emailsDept = $this->departmentEmails($depId);

        // 3.1) Users da chefia (email) ✅ NOVO
        $chefiaId = (int) ($validated['chefia_id'] ?? 0);
        $emailsChefia = $this->chefiaEmails($chefiaId);

        // 4) Users dos grupos (email)
        $emailsGrupos = User::whereHas('grupos', fn ($q) => $q->whereIn('grupos.id', $gruposSelecionados))
            ->pluck('email')
            ->filter()
            ->unique()
            ->values();

        // 5) Emails dos destinatários manuais
        $emailsSelecionados = User::whereIn('id', $userIdsSelecionados->all())
            ->pluck('email')
            ->filter()
            ->unique()
            ->values();

        // ✅ União final
        $emailsInternos = collect()
            ->merge($emailsSelecionados)
            ->merge($emailsDept)
            ->merge($emailsChefia) // ✅
            ->merge($emailsGrupos);

        $emailsInternos = $emailsInternos
            ->map(fn ($e) => trim(mb_strtolower((string) $e)))
            ->filter(fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();

        \Log::info('Recado email list (internos)', [
            'recado_id' => $recado->id,
            'departamento_id' => $depId,
            'chefia_id' => $chefiaId,
            'emails' => $emailsInternos->all(),
        ]);

        foreach ($emailsInternos as $email) {
            try {
                Mail::to($email)->send(new RecadoCriadoMail($recado, null, $emailsInternos));
            } catch (\Throwable $e) {
                \Log::error('Falha a enviar email do recado', [
                    'recado_id' => $recado->id,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        // 6) Destinatários livres
        if ($request->filled('destinatarios_livres')) {
            foreach ((array) $request->destinatarios_livres as $emailLivre) {

                $emailLivre = trim(mb_strtolower((string) $emailLivre));

                if (!filter_var($emailLivre, FILTER_VALIDATE_EMAIL)) continue;

                $token = Str::random(60);

                RecadoGuestToken::create([
                    'recado_id' => $recado->id,
                    'email' => $emailLivre,
                    'token' => $token,
                    'expires_at' => now()->addMonth(),
                    'is_active' => true
                ]);

                try {
                    Mail::to($emailLivre)->send(
                        new RecadoCriadoMail($recado, route('recados.guest', $token))
                    );
                } catch (\Throwable $e) {
                    \Log::error('Falha a enviar email para destinatario livre', [
                        'recado_id' => $recado->id,
                        'email' => $emailLivre,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return redirect()->route('recados.index')->with('success', 'Recado criado e emails enviados.');
    }

    public function show($id)
    {
        $recado = Recado::with([
            'sla','tipo','origem','setor','departamento','chefia',
            'destinatarios','aviso','estado','tipoFormulario',
            'guestTokens','grupos.users','campanha'
        ])->findOrFail($id);

        $user = auth()->user();

        if ($user->cargo?->name !== 'admin' && $recado->user_id !== $user->id) {
            $uid = (int) $user->id;

            $isDestinatario = $recado->destinatarios->contains($uid);
            $isGrupo = $recado->grupos->pluck('users')->flatten()->pluck('id')->contains($uid);

            // ✅ Departamento (pivot)
            $isDepartamento = false;
            if ($recado->departamento_id) {
                $isDepartamento = $recado->departamento
                    ? $recado->departamento->users()->where('users.id', $uid)->exists()
                    : false;
            }

            // ✅ Chefia (pivot) ✅ NOVO
            $isChefia = false;
            if ($recado->chefia_id) {
                $isChefia = $recado->chefia
                    ? $recado->chefia->users()->where('users.id', $uid)->exists()
                    : false;
            }

            if (!$isDestinatario && !$isGrupo && !$isDepartamento && !$isChefia) {
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

    public function adicionarComentario(Request $request, Recado $recado)
    {
        $request->validate(['comentario' => 'required|string']);
        $novaLinha = now()->format('d/m/Y H:i').' - '.auth()->user()->name.': '.$request->comentario;

        $recado->observacoes = $recado->observacoes
            ? $recado->observacoes."\n".$novaLinha
            : $novaLinha;

        $estadoPendente = Estado::where('name','Pendente')->first();
        if ($estadoPendente && strtolower($recado->estado->name) == 'novo') {
            $recado->estado_id = $estadoPendente->id;
        }

        $recado->save();
        RecadoGuestToken::where('recado_id', $recado->id)->where('is_active', true)->update(['is_active' => false]);

        return redirect()->back()->with('success', 'Comentário adicionado.');
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
            'setor','origem','departamento','chefia','destinatarios','estado','sla',
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

        // ✅ Visibilidade só quando NÃO há vista ativa (para não estragar vistas)
        $haVistaAtiva = (!$temFiltrosManuais && !empty($vistaId));

        if ($user->cargo?->name !== 'admin' && !$haVistaAtiva) {
            $uid = (int) $user->id;

            $query->where(function ($q) use ($uid) {
                $q->where('user_id', $uid)
                  ->orWhereHas('destinatarios', fn ($d) => $d->where('users.id', $uid))
                  ->orWhereHas('grupos.users', fn ($g) => $g->where('users.id', $uid))
                  ->orWhereHas('departamento.users', fn ($u) => $u->where('users.id', $uid))
                  ->orWhereHas('chefia.users', fn ($u) => $u->where('users.id', $uid)); // ✅ chefia
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

    public function enviarAviso(Recado $recado, Aviso $aviso)
    {
        $emails = $recado->destinatarios->pluck('email')->toArray();
        if ($recado->guestTokens->count()) {
            $emails = array_merge($emails, $recado->guestTokens->pluck('email')->toArray());
        }

        foreach (array_values(array_unique($emails)) as $email) {
            Mail::to($email)->send(new RecadoAvisoMail($recado, $aviso));
        }

        return back()->with('success', 'Aviso enviado com sucesso!');
    }
}
