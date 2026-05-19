<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PriceProposalStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceProposal extends Model
{
    use SoftDeletes;

    public const TABLE = 'price_proposals';
    public const ID = 'id';
    public const JOB_APPLICATION_ID = 'job_application_id';
    public const PROPOSED_BY_USER_ID = 'proposed_by_user_id';
    public const AMOUNT = 'amount';
    public const PRICE_TYPE = 'price_type';
    public const NOTE = 'note';
    public const STATUS = 'status';
    public const RESPONDED_BY_USER_ID = 'responded_by_user_id';
    public const RESPONDED_AT = 'responded_at';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const DELETED_AT = 'deleted_at';

    protected $table = self::TABLE;

    protected $fillable = [
        self::JOB_APPLICATION_ID,
        self::PROPOSED_BY_USER_ID,
        self::AMOUNT,
        self::PRICE_TYPE,
        self::NOTE,
        self::STATUS,
        self::RESPONDED_BY_USER_ID,
        self::RESPONDED_AT,
    ];

    protected $casts = [
        self::AMOUNT => 'decimal:2',
        self::STATUS => PriceProposalStatusEnum::class,
        self::RESPONDED_AT => 'datetime',
    ];

    public function getId(): int
    {
        return $this->getAttribute(self::ID);
    }

    public function getJobApplicationId(): int
    {
        return $this->getAttribute(self::JOB_APPLICATION_ID);
    }

    public function getProposedByUserId(): int
    {
        return $this->getAttribute(self::PROPOSED_BY_USER_ID);
    }

    public function getAmount(): float
    {
        return (float) $this->getAttribute(self::AMOUNT);
    }

    public function getPriceType(): string
    {
        return $this->getAttribute(self::PRICE_TYPE) ?? 'fixed';
    }

    public function getNote(): ?string
    {
        return $this->getAttribute(self::NOTE);
    }

    public function getStatus(): PriceProposalStatusEnum
    {
        return $this->getAttribute(self::STATUS);
    }

    public function getRespondedByUserId(): ?int
    {
        return $this->getAttribute(self::RESPONDED_BY_USER_ID);
    }

    public function getRespondedAt(): ?Carbon
    {
        return $this->getAttribute(self::RESPONDED_AT);
    }

    public function getCreatedAt(): ?Carbon
    {
        return $this->getAttribute(self::CREATED_AT);
    }

    public function isPending(): bool
    {
        return $this->getStatus() === PriceProposalStatusEnum::PENDING;
    }

    public function isAcceptable(): bool
    {
        return $this->isPending();
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, self::JOB_APPLICATION_ID, Application::ID);
    }

    public function proposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, self::PROPOSED_BY_USER_ID, User::ID);
    }

    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, self::RESPONDED_BY_USER_ID, User::ID);
    }
}
