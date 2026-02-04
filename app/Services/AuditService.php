<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditService
{
    public static function log(
        string $event,
        $auditable = null,
        array $meta = [],
        ?array $old = null,
        ?array $new = null
    ): void {
        $request = request();

        AuditLog::create([
            'user_id'        => auth()->id(),
            'event'          => $event,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id'   => $auditable?->getKey(),

            'route'      => optional($request->route())->getName(),
            'method'     => $request->method(),
            'url'        => $request->fullUrl(),
            'ip'         => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 65000),

            'old_values' => $old,
            'new_values' => $new,
            'meta'       => $meta,
            'created_at' => now(),
        ]);
    }
}
