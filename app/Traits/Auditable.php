<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            self::log('created', $model, null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            if (empty($changes)) return;

            $old = array_intersect_key($model->getOriginal(), $changes);

            self::log('updated', $model, $old, $changes);
        });

        static::deleted(function ($model) {
            self::log('deleted', $model, $model->getOriginal(), null);
        });
    }

    protected static function log(
        string $event,
        $model,
        ?array $old,
        ?array $new,
        array $meta = []
    ): void {
        $request = request();

        AuditLog::create([
            'user_id'        => auth()->id(),
            'event'          => $event,
            'auditable_type' => get_class($model),
            'auditable_id'   => $model->getKey(),

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
