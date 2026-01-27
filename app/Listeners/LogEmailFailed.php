<?php

namespace App\Listeners;

use App\Models\EmailLog;
use Illuminate\Mail\Events\MessageFailed;

class LogEmailFailed
{
    public function handle(MessageFailed $event): void
    {
        $mailable = $event->data['mailable'] ?? null;

        if (!$mailable || !$mailable->email_log_id) return;

        $log = EmailLog::find($mailable->email_log_id);
        if (!$log) return;

        $log->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $event->exception?->getMessage(),
            'error_trace' => $event->exception?->getTraceAsString(),
        ]);
    }
}
