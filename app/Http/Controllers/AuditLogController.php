<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        // ✅ só admin
        if (auth()->user()->cargo?->name !== 'admin') abort(403);

        $q = AuditLog::query()->with('user');

        // filtros
        if ($request->filled('event')) {
            $q->where('event', $request->event);
        }

        if ($request->filled('user_id')) {
            $q->where('user_id', $request->user_id);
        }

        if ($request->filled('auditable_type')) {
            $q->where('auditable_type', $request->auditable_type);
        }

        if ($request->filled('auditable_id')) {
            $q->where('auditable_id', $request->auditable_id);
        }

        if ($request->filled('ip')) {
            $q->where('ip', 'like', '%'.$request->ip.'%');
        }

        if ($request->filled('route')) {
            $q->where('route', 'like', '%'.$request->route.'%');
        }

        if ($request->filled('method')) {
            $q->where('method', strtoupper($request->method));
        }

        // intervalo de datas (created_at)
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        if ($request->filled('date_from')) {
            $q->where('created_at', '>=', $request->date_from.' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $q->where('created_at', '<=', $request->date_to.' 23:59:59');
        }

        // pesquisa livre em meta/old/new/url/user_agent
        if ($request->filled('search')) {
            $s = trim($request->search);

            // compatível com MySQL/MariaDB (CAST JSON -> CHAR) e também com colunas text
            $q->where(function ($qq) use ($s) {
                $qq->where('url', 'like', "%$s%")
                   ->orWhere('user_agent', 'like', "%$s%")
                   ->orWhereRaw('CAST(meta as CHAR) LIKE ?', ["%$s%"])
                   ->orWhereRaw('CAST(old_values as CHAR) LIKE ?', ["%$s%"])
                   ->orWhereRaw('CAST(new_values as CHAR) LIKE ?', ["%$s%"]);
            });
        }

        // dropdowns
        $events = AuditLog::query()
            ->select('event')
            ->whereNotNull('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

        $auditableTypes = AuditLog::query()
            ->select('auditable_type')
            ->whereNotNull('auditable_type')
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type');

        $users = User::orderBy('name')->get(['id','name','email']);

        $logs = $q->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('audit_logs.index', compact('logs','events','auditableTypes','users'));
    }

    public function show(AuditLog $auditLog)
    {
        if (auth()->user()->cargo?->name !== 'admin') abort(403);

        $auditLog->load('user');

        return view('audit_logs.show', compact('auditLog'));
    }
}
