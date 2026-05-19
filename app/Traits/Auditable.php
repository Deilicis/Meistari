<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (self $model): void {
            if (! auth()->check()) {
                return;
            }

            AuditLog::create([
                AuditLog::USER_ID => auth()->id(),
                AuditLog::ACTION => 'created',
                AuditLog::AUDITABLE_TYPE => static::class,
                AuditLog::AUDITABLE_ID => $model->getKey(),
                AuditLog::OLD_VALUES => null,
                AuditLog::NEW_VALUES => $model->getAttributes(),
                AuditLog::IP_ADDRESS => request()?->ip(),
            ]);
        });

        static::updated(function (self $model): void {
            if (! auth()->check()) {
                return;
            }

            AuditLog::create([
                AuditLog::USER_ID => auth()->id(),
                AuditLog::ACTION => 'updated',
                AuditLog::AUDITABLE_TYPE => static::class,
                AuditLog::AUDITABLE_ID => $model->getKey(),
                AuditLog::OLD_VALUES => $model->getOriginal(),
                AuditLog::NEW_VALUES => $model->getDirty(),
                AuditLog::IP_ADDRESS => request()?->ip(),
            ]);
        });

        static::deleted(function (self $model): void {
            if (! auth()->check()) {
                return;
            }

            AuditLog::create([
                AuditLog::USER_ID => auth()->id(),
                AuditLog::ACTION => 'deleted',
                AuditLog::AUDITABLE_TYPE => static::class,
                AuditLog::AUDITABLE_ID => $model->getKey(),
                AuditLog::OLD_VALUES => $model->getAttributes(),
                AuditLog::NEW_VALUES => null,
                AuditLog::IP_ADDRESS => request()?->ip(),
            ]);
        });
    }
}
