<?php

namespace App\Services;

use App\Models\EmailLog;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailLogger
{
    public function sendLogged(Mailable $mailable, $to): EmailLog
    {
        $start = microtime(true);

        $html = null;
        try {
            $html = $mailable->render();
        } catch (\Throwable $e) {}

        $log = EmailLog::create([
            'app_env' => App::environment(),
            'mailer' => config('mail.default'),
            'mail_type' => get_class($mailable),
            'view' => $mailable->view_name ?? null,

            'recado_id' => $mailable->recado_id ?? null,
            'triggered_by_user_id' => $mailable->triggered_by_user_id ?? null,

            'to' => $this->normalize($to),
            'subject' => $mailable->subject ?? null,
            'body' => $html,
            'body_hash' => $html ? hash('sha256', $html) : null,
            'body_size' => $html ? strlen($html) : null,

            'status' => 'sending',
            'attempt' => 1,
            'ip' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'trace_id' => (string) Str::uuid(),
        ]);

        if (property_exists($mailable, 'email_log_id')) {
            $mailable->withEmailLog($log->id);
        }

        try {
            Mail::to($to)->send($mailable);

            $log->update([
                'status' => 'sent',
                'sent_at' => now(),
                'duration_ms' => (int)((microtime(true) - $start) * 1000),
            ]);

        } catch (\Throwable $e) {
            $log->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }

        return $log;
    }

    private function normalize($value): array
    {
        if (is_string($value)) {
            return [['email' => $value]];
        }

        if (is_array($value)) {
            return collect($value)->map(fn ($v) => ['email' => $v])->toArray();
        }

        return [];
    }
}
