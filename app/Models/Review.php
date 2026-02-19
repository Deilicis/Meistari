<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use SoftDeletes;

    public const TABLE = 'reviews';
    public const ID = 'id';
    public const JOB_REQUEST_ID = 'job_request_id';
    public const REVIEWER_ID = 'reviewer_id';
    public const REVIEWEE_ID = 'reviewee_id';
    public const RATING = 'rating';
    public const COMMENT = 'comment';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const DELETED_AT = 'deleted_at';
    private const INTEGER = 'integer';

    protected $table = self::TABLE;

    protected $fillable = [
        self::JOB_REQUEST_ID,
        self::REVIEWER_ID,
        self::REVIEWEE_ID,
        self::RATING,
        self::COMMENT,
    ];

    protected $casts = [
        self::RATING => self::INTEGER,
    ];

    public function getId(): int
    {
        return $this->getAttribute(self::ID);
    }

    public function getJobRequestId(): int
    {
        return $this->getAttribute(self::JOB_REQUEST_ID);
    }

    public function getReviewerId(): int
    {
        return $this->getAttribute(self::REVIEWER_ID);
    }

    public function getRevieweeId(): int
    {
        return $this->getAttribute(self::REVIEWEE_ID);
    }

    public function getRating(): int
    {
        return $this->getAttribute(self::RATING);
    }

    public function getComment(): ?string
    {
        return $this->getAttribute(self::COMMENT);
    }

    public function getCreatedAt(): ?Carbon
    {
        return $this->getAttribute(self::CREATED_AT);
    }

    public function getUpdatedAt(): ?Carbon
    {
        return $this->getAttribute(self::UPDATED_AT);
    }

    public function getDeletedAt(): ?Carbon
    {
        return $this->getAttribute(self::DELETED_AT);
    }

    public function jobRequest(): BelongsTo
    {
        return $this->belongsTo(JobRequest::class, self::JOB_REQUEST_ID, JobRequest::ID);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, self::REVIEWER_ID, User::ID);
    }

    public function reviewee(): BelongsTo
    {
        return $this->belongsTo(User::class, self::REVIEWEE_ID, User::ID);
    }
}