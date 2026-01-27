<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->cargo?->name !== 'admin') abort(403);

        $q = EmailLog::query();

        // filtros
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        if ($request->filled('mail_type')) {
            $q->where('mail_type', 'like', '%'.$request->mail_type.'%');
        }

        if ($request->filled('to')) {
            // procura no JSON "to"
            $to = strtolower(trim($request->to));
            $q->whereRaw("LOWER(JSON_EXTRACT(`to`, '$[*].email')) LIKE ?", ['%'.$to.'%']);
        }

        if ($request->filled('recado_id')) {
            $q->where('recado_id', (int)$request->recado_id);
        }

        if ($request->filled('date_from')) {
            $q->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $q->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $q->orderByDesc('id')->paginate(20)->withQueryString();

        $statuses = ['created','sending','sent','failed'];

        return view('email_logs.index', compact('logs','statuses'));
    }

    public function show(EmailLog $emailLog)
    {
        if (auth()->user()->cargo?->name !== 'admin') abort(403);

        return view('email_logs.show', compact('emailLog'));
    }
}
