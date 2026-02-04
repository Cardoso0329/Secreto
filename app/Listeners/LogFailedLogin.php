<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use App\Services\AuditService;

class LogFailedLogin
{
    public function handle(Failed $event): void
    {
        AuditService::log('login_failed', null, [
            'email' => $event->credentials['email'] ?? null,
        ]);
    }
}
