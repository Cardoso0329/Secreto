<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use App\Services\AuditService;

class LogLogout
{
    public function handle(Logout $event): void
    {
        AuditService::log('logout', $event->user, [
            'user_id' => $event->user?->id,
            'email'   => $event->user?->email,
        ]);
    }
}
