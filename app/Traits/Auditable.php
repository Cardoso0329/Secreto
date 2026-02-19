<?php

namespace App\Traits;

use App\Services\AuditService;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            AuditService::log(
                'created',
                $model,
                [],
                null,
                $model->getAttributes()
            );
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            if (empty($changes)) return;

            // old apenas das chaves alteradas
            $old = [];
            foreach ($changes as $key => $value) {
                $old[$key] = $model->getOriginal($key);
            }

            AuditService::log(
                'updated',
                $model,
                [],
                $old,
                $changes
            );
        });

        static::deleted(function ($model) {
            AuditService::log(
                'deleted',
                $model,
                [],
                $model->getOriginal(),
                null
            );
        });
    }
}
