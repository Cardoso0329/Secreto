<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditService
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'remember_token',
        'current_password',
        'token',
    ];

    public static function log(
        string $event,
        $auditable = null,
        array $meta = [],
        ?array $old = null,
        ?array $new = null
    ): void {
        $request = app()->runningInConsole() ? null : request();

        $routeName = $request ? optional($request->route())->getName() : null;

        /**
         * ✅ Em fluxos de autenticação (login/reset/verification), considera ator = guest
         * porque é muito comum não haver user autenticado “real”.
         */
        $forceGuestActor =
            $routeName && (
                str_starts_with($routeName, 'password.') ||
                str_starts_with($routeName, 'login') ||
                str_starts_with($routeName, 'verification.')
            );

        $actorId = (!$forceGuestActor && auth()->check()) ? auth()->id() : null;

        /**
         * ✅ Valida se o user existe na MESMA connection onde estás a gravar o audit log
         * (evita o caso: User noutra DB, FK noutra DB).
         */
        if ($actorId) {
            $auditConn = (new AuditLog())->getConnectionName(); // pode ser null (default)

            $exists = $auditConn
                ? User::on($auditConn)->whereKey($actorId)->exists()
                : User::whereKey($actorId)->exists();

            if (!$exists) {
                $actorId = null;
            }
        }

        $oldSafe  = self::sanitize($old);
        $newSafe  = self::sanitize($new);
        $metaSafe = self::sanitize($meta);

        AuditLog::create([
            'user_id'        => $actorId,
            'event'          => $event,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id'   => $auditable?->getKey(),

            'route'      => $routeName,
            'method'     => $request ? $request->method() : null,
            'url'        => $request ? $request->fullUrl() : null,
            'ip'         => $request ? $request->ip() : null,
            'user_agent' => $request ? substr((string) $request->userAgent(), 0, 65000) : null,

            'old_values' => $oldSafe,
            'new_values' => $newSafe,
            'meta'       => $metaSafe,
            'created_at' => now(),
        ]);
    }

    private static function sanitize(?array $data): ?array
    {
        if ($data === null) return null;

        $clean = collect($data)->except(self::SENSITIVE_KEYS)->all();

        foreach (['attributes', 'original', 'changes'] as $nested) {
            if (isset($clean[$nested]) && is_array($clean[$nested])) {
                $clean[$nested] = collect($clean[$nested])->except(self::SENSITIVE_KEYS)->all();
            }
        }

        return $clean;
    }
}
