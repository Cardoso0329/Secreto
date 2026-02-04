<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Services\AuditService;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        AuditService::log('login_success', $event->user, [
            'user_id' => $event->user->id,
            'email'   => $event->user->email,
        ]);
    }
}
