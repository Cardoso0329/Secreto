<?php

namespace App\Listeners;

use App\Models\EmailLog;
use Illuminate\Mail\Events\MessageSent;

class LogEmailSent
{
    public function handle(MessageSent $event): void
    {
        $mailable = $event->data['mailable'] ?? null;

        if (!$mailable || !$mailable->email_log_id) return;

        $log = EmailLog::find($mailable->email_log_id);
        if (!$log) return;

        $log->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }
}
