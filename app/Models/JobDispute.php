<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobDispute extends Model
{
    use SoftDeletes;

    public const TABLE = 'job_disputes';
    public const ID = 'id';
    public const JOB_REQUEST_ID = 'job_request_id';
    public const RAISED_BY_USER_ID = 'raised_by_user_id';
    public const REASON = 'reason';
    public const RESOLVED_AT = 'resolved_at';
    public const RESOLUTION_NOTE = 'resolution_note';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const DELETED_AT = 'deleted_at';

    protected $table = self::TABLE;

    protected $fillable = [
        self::JOB_REQUEST_ID,
        self::RAISED_BY_USER_ID,
        self::REASON,
        self::RESOLVED_AT,
        self::RESOLUTION_NOTE,
    ];

    protected $casts = [
        self::RESOLVED_AT => 'datetime',
    ];

    public function getId(): int { return $this->getAttribute(self::ID); }
    public function getJobRequestId(): int { return $this->getAttribute(self::JOB_REQUEST_ID); }
    public function getRaisedByUserId(): int { return $this->getAttribute(self::RAISED_BY_USER_ID); }
    public function getReason(): string { return $this->getAttribute(self::REASON); }
    public function getResolvedAt(): ?Carbon { return $this->getAttribute(self::RESOLVED_AT); }
    public function getResolutionNote(): ?string { return $this->getAttribute(self::RESOLUTION_NOTE); }

    public function jobRequest(): BelongsTo
    {
        return $this->belongsTo(JobRequest::class, self::JOB_REQUEST_ID, JobRequest::ID);
    }

    public function raisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, self::RAISED_BY_USER_ID, User::ID);
    }
}
