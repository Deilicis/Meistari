<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use App\Enums\Job\ApplicationStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    use SoftDeletes;

    public const TABLE = 'applications';
    public const ID = 'id';
    public const JOB_REQUEST_ID = 'job_request_id';
    public const USER_ID = 'user_id';
    public const COVER_LETTER = 'cover_letter';
    public const PRICE_OFFER = 'price_offer';
    public const STATUS = 'status';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const DELETED_AT = 'deleted_at';
    private const DECIMAL = 'decimal:2';

    protected $table = self::TABLE;

    protected $fillable = [
        self::JOB_REQUEST_ID,
        self::USER_ID,
        self::COVER_LETTER,
        self::PRICE_OFFER,
        self::STATUS,
    ];

    protected $casts = [
        self::PRICE_OFFER => self::DECIMAL,
        self::STATUS => ApplicationStatusEnum::class,
    ];

    public function getId(): int
    {
        return $this->getAttribute(self::ID);
    }

    public function getJobRequestId(): int
    {
        return $this->getAttribute(self::JOB_REQUEST_ID);
    }

    public function getUserId(): int
    {
        return $this->getAttribute(self::USER_ID);
    }

    public function getCoverLetter(): string
    {
        return $this->getAttribute(self::COVER_LETTER);
    }

    public function getPriceOffer(): float
    {
        return (float) $this->getAttribute(self::PRICE_OFFER);
    }

    public function getStatus(): ApplicationStatusEnum
    {
        return $this->getAttribute(self::STATUS);
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, self::USER_ID, User::ID);
    }
}