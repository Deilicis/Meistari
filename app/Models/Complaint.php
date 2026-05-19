<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Complaint\ComplaintStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Complaint extends Model
{
    public const TABLE = 'complaints';
    public const ID = 'id';
    public const REPORTER_ID = 'reporter_id';
    public const REPORTED_USER_ID = 'reported_user_id';
    public const REPORTED_ENTITY_TYPE = 'reported_entity_type';
    public const REPORTED_ENTITY_ID = 'reported_entity_id';
    public const REASON = 'reason';
    public const STATUS = 'status';
    public const RESOLVED_BY = 'resolved_by';
    public const RESOLUTION_NOTE = 'resolution_note';
    public const RESOLVED_AT = 'resolved_at';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    protected $table = self::TABLE;

    protected $fillable = [
        self::REPORTER_ID,
        self::REPORTED_USER_ID,
        self::REPORTED_ENTITY_TYPE,
        self::REPORTED_ENTITY_ID,
        self::REASON,
        self::STATUS,
        self::RESOLVED_BY,
        self::RESOLUTION_NOTE,
        self::RESOLVED_AT,
    ];

    protected $casts = [
        self::STATUS => ComplaintStatusEnum::class,
        self::RESOLVED_AT => 'datetime',
    ];

    public function getId(): int
    {
        return $this->getAttribute(self::ID);
    }

    public function getReporterId(): int
    {
        return $this->getAttribute(self::REPORTER_ID);
    }

    public function getReportedUserId(): int
    {
        return $this->getAttribute(self::REPORTED_USER_ID);
    }

    public function getReason(): string
    {
        return $this->getAttribute(self::REASON);
    }

    public function getStatus(): ComplaintStatusEnum
    {
        return $this->getAttribute(self::STATUS);
    }

    public function getResolvedBy(): ?int
    {
        return $this->getAttribute(self::RESOLVED_BY);
    }

    public function getResolutionNote(): ?string
    {
        return $this->getAttribute(self::RESOLUTION_NOTE);
    }

    public function getResolvedAt(): ?Carbon
    {
        return $this->getAttribute(self::RESOLVED_AT);
    }

    public function getCreatedAt(): ?Carbon
    {
        return $this->getAttribute(self::CREATED_AT);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, self::REPORTER_ID, User::ID);
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, self::REPORTED_USER_ID, User::ID);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, self::RESOLVED_BY, User::ID);
    }

    public function reportedEntity(): MorphTo
    {
        return $this->morphTo(null, self::REPORTED_ENTITY_TYPE, self::REPORTED_ENTITY_ID);
    }
}
