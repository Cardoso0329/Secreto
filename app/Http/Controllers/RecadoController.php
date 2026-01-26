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
           ========================================================= */

        // ===== Departamento (vista)
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

        // ===== Chefia (vista)
        $chefiaIdsDaVista = collect();
        $permitirVerChefiaDaVista = false;

        if (!$temFiltrosManuais && !empty($vistaId)) {
            $chefiaIdsDaVista = collect($vistaFiltros)
                ->filter(fn ($f) => is_array($f))
                ->filter(fn ($f) => ($f['field'] ?? null) === 'chefia_id')
                ->filter(fn ($f) => ($f['operator'] ?? '=') === '=')
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
                    $chefiaIdsDaVista = collect();
                }
            }
        }

        /* ================= VISIBILIDADE ================= */
        if ($user->cargo?->name !== 'admin') {

            $uid = (int) $user->id;

            $haVistaAtiva = (!$temFiltrosManuais && !empty($vistaId));

            if (!$haVistaAtiva) {
                $recados->where(function ($q) use ($uid) {
                    $q->where('user_id', $uid)
                      ->orWhereHas('destinatarios', fn ($d) => $d->where('users.id', $uid))
                      ->orWhereHas('grupos.users', fn ($g) => $g->where('users.id', $uid))
                      ->orWhereHas('departamento.users', fn ($u) => $u->where('users.id', $uid))
                      ->orWhereHas('chefia.users', fn ($u) => $u->where('users.id', $uid));
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

        $chefias = Chefia::orderBy('name')->get();

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
        $rules = [
            'name' => 'required|string|max:255',
            'contact_client' => 'required|string|max:255',
            'plate' => 'nullable|string|max:255',
            'mensagem' => 'required|string',
            'observacoes' => 'nullable|string',

            'estado_id' => 'nullable|exists:estados,id',
            'tipo_formulario_id' => 'nullable|exists:tipo_formularios,id',
            'sla_id' => 'nullable|exists:slas,id',
            'tipo_id' => 'nullable|exists:tipos,id',
            'origem_id' => 'nullable|exists:origens,id',
            'setor_id' => 'nullable|exists:setores,id',
            'departamento_id' => 'nullable|exists:departamentos,id',
            'aviso_id' => 'nullable|exists:avisos,id',
            'campanha_id' => 'nullable|exists:campanhas,id',
            'chefia_id' => 'nullable|exists:chefias,id',

            'abertura' => 'nullable|date',
            'termino' => 'nullable|date',
            'ficheiro' => 'nullable|file',

            'destinatarios_users' => 'nullable|array',
            'destinatarios_users.*' => 'exists:users,id',

            'destinatarios_grupos' => 'nullable|array',
            'destinatarios_grupos.*' => 'exists:grupos,id',

            'destinatarios_livres' => 'nullable|array',
            'destinatarios_livres.*' => 'nullable|email',


        ];

        $validated = $request->validate($rules);

        $recado->fill($validated);

        if ($request->hasFile('ficheiro')) {
            $file = $request->file('ficheiro');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->storeAs('public/recados', $filename);
            $recado->ficheiro = $filename;
        }

        $recado->save();

        $recado->destinatariosUsers()->sync($request->input('destinatarios_users', []));
        $recado->grupos()->sync($request->input('destinatarios_grupos', []));

        // ✅ Atualizar emails livres (guest tokens)
        $novosLivres = collect((array) $request->input('destinatarios_livres', []))
            ->map(fn($e) => trim(mb_strtolower((string) $e)))
            ->filter(fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();

        $atuaisLivres = $recado->guestTokens()->pluck('email')
            ->map(fn($e) => trim(mb_strtolower((string) $e)))
            ->values();

        $paraRemover = $atuaisLivres->diff($novosLivres);
        if ($paraRemover->isNotEmpty()) {
            $recado->guestTokens()->whereIn('email', $paraRemover->all())->delete();
        }

        $paraAdicionar = $novosLivres->diff($atuaisLivres);
        foreach ($paraAdicionar as $email) {
            $recado->guestTokens()->create([
                'email'      => $email,
                'token'      => Str::random(60),
                'expires_at' => now()->addMonth(),
                'is_active'  => true,
            ]);
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
            'setor_id' => 'nullable|exists:setores,id',

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

            'tipo_formulario_id' => 'required|exists:tipo_formularios,id',
            'wip' => 'nullable|string|max:255',
            'chefia_id' => 'nullable|exists:chefias,id',
        ];

        if ($tipoFormulario && strtolower(trim($tipoFormulario->name)) === 'call center') {
            $rules['assunto'] = 'required|string|max:255';
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

        /* ================== DESTINATÁRIOS / EMAILS ================== */

        $gruposSelecionados = (array) $request->input('destinatarios_grupos', []);
        $telefonistasId = Grupo::where('name', 'Telefonistas')->first()?->id;

        if ($telefonistasId && !in_array($telefonistasId, $gruposSelecionados)) {
            $gruposSelecionados[] = $telefonistasId;
        }

        $gruposSelecionados = collect($gruposSelecionados)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $recado->grupos()->sync($gruposSelecionados);

        $userIdsSelecionados = collect((array) $request->input('destinatarios_users', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $recado->destinatariosUsers()->sync($userIdsSelecionados->all());

        $depId = (int) ($validated['departamento_id'] ?? 0);
        $emailsDept = $this->departmentEmails($depId);

        $chefiaId = (int) ($validated['chefia_id'] ?? 0);
        $emailsChefia = $this->chefiaEmails($chefiaId);

        $emailsGrupos = User::whereHas('grupos', fn ($q) => $q->whereIn('grupos.id', $gruposSelecionados))
            ->pluck('email')
            ->filter()
            ->unique()
            ->values();

        $emailsSelecionados = User::whereIn('id', $userIdsSelecionados->all())
            ->pluck('email')
            ->filter()
            ->unique()
            ->values();

        $emailsInternos = collect()
            ->merge($emailsSelecionados)
            ->merge($emailsDept)
            ->merge($emailsChefia)
            ->merge($emailsGrupos)
            ->map(fn ($e) => trim(mb_strtolower((string) $e)))
            ->filter(fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();

        foreach ($emailsInternos as $email) {
            try {
                Mail::to($email)->send(new RecadoCriadoMail($recado, null, $emailsInternos));
            } catch (\Throwable $e) {
                \Log::error('Falha a enviar email do recado', [
                    'recado_id' => $recado->id,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
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
                    // ✅ continua a usar a tua forma: /recados/guest/{token}
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

        // ✅ FIX: evita "cargo on null" quando abre sem login
        if (!$user) {
            abort(403, 'Faça login para ver este recado.');
        }

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

    // ✅ NOVO: abrir recado via link do email SEM LOGIN
    public function showGuest(string $token)
    {
        $guest = RecadoGuestToken::where('token', $token)->first();

        if (!$guest) {
            abort(403, 'Token inválido.');
        }

        // valida ativo + expiração
        if (!$guest->is_active) {
            abort(403, 'Token desativado.');
        }

        if ($guest->expires_at && $guest->expires_at->isPast()) {
            abort(403, 'Token expirado.');
        }

        $recado = Recado::with([
            'sla','tipo','origem','setor','departamento','chefia',
            'destinatarios','aviso','estado','tipoFormulario',
            'guestTokens','grupos.users','campanha'
        ])->findOrFail($guest->recado_id);

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
            $vista  = \App\Services\VistaRepo::findOrFail($vistaId);

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
                  ->orWhereHas('departamento.users', fn ($u) => $u->where('users.id', $uid))
                  ->orWhereHas('chefia.users', fn ($u) => $u->where('users.id', $uid));
            });
        }

        $allowedSort = ['id','contact_client','plate','estado_id','tipo_formulario_id','abertura','termino','created_at'];
        $sortBy  = $request->input('sort_by', 'id');
        $sortDir = strtolower($request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (!in_array($sortBy, $allowedSort)) $sortBy = 'id';

        $query->orderBy($sortBy, $sortDir);

        $recados = $query->get();

        if ($recados->isEmpty()) {
            return back()->with('error', 'Não existem recados para exportar com os filtros atuais.');
        }

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

   public function enviarAviso(Request $request, Recado $recado)
{
    // validar
    $request->validate([
        'aviso_id' => ['required', 'exists:avisos,id'],
    ]);

    $avisoId = (int) $request->input('aviso_id');

    // bloquear repetidos
    if ($recado->avisosEnviados()->where('aviso_id', $avisoId)->exists()) {
        return back()->with('error', 'Este aviso já foi enviado.');
    }

    // buscar aviso
    $aviso = Aviso::findOrFail($avisoId);

    // emails (destinatarios + guestTokens)
    $emails = $recado->destinatarios->pluck('email')->toArray();

    if ($recado->guestTokens->count()) {
        $emails = array_merge($emails, $recado->guestTokens->pluck('email')->toArray());
    }

    foreach (array_values(array_unique($emails)) as $email) {
        Mail::to($email)->send(new RecadoAvisoMail($recado, $aviso));
    }

    // guardar no pivot (marca como enviado)
    $recado->avisosEnviados()->syncWithoutDetaching([$avisoId]);

    return back()->with('success', 'Aviso enviado com sucesso!');
}
}
