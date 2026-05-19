<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    public const TABLE = 'audit_logs';
    public const ID = 'id';
    public const USER_ID = 'user_id';
    public const ACTION = 'action';
    public const AUDITABLE_TYPE = 'auditable_type';
    public const AUDITABLE_ID = 'auditable_id';
    public const OLD_VALUES = 'old_values';
    public const NEW_VALUES = 'new_values';
    public const IP_ADDRESS = 'ip_address';
    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = null;

    protected $table = self::TABLE;

    protected $fillable = [
        self::USER_ID,
        self::ACTION,
        self::AUDITABLE_TYPE,
        self::AUDITABLE_ID,
        self::OLD_VALUES,
        self::NEW_VALUES,
        self::IP_ADDRESS,
    ];

    protected $casts = [
        self::OLD_VALUES => 'array',
        self::NEW_VALUES => 'array',
    ];

    public function getId(): int
    {
        return $this->getAttribute(self::ID);
    }

    public function getUserId(): ?int
    {
        return $this->getAttribute(self::USER_ID);
    }

    public function getAction(): string
    {
        return $this->getAttribute(self::ACTION);
    }

    public function getAuditableType(): string
    {
        return $this->getAttribute(self::AUDITABLE_TYPE);
    }

    public function getAuditableId(): int
    {
        return $this->getAttribute(self::AUDITABLE_ID);
    }

    public function getOldValues(): ?array
    {
        return $this->getAttribute(self::OLD_VALUES);
    }

    public function getNewValues(): ?array
    {
        return $this->getAttribute(self::NEW_VALUES);
    }

    public function getIpAddress(): ?string
    {
        return $this->getAttribute(self::IP_ADDRESS);
    }

    public function getCreatedAt(): ?Carbon
    {
        return $this->getAttribute(self::CREATED_AT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, self::USER_ID, User::ID);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo(null, self::AUDITABLE_TYPE, self::AUDITABLE_ID);
    }
}
