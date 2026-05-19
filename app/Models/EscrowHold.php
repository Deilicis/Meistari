<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EscrowStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EscrowHold extends Model
{
    use SoftDeletes;

    public const TABLE = 'escrow_holds';
    public const ID = 'id';
    public const JOB_REQUEST_ID = 'job_request_id';
    public const CLIENT_ID = 'client_id';
    public const MASTER_ID = 'master_id';
    public const AMOUNT = 'amount';
    public const STATUS = 'status';
    public const PAYMENT_REFERENCE = 'payment_reference';
    public const HELD_AT = 'held_at';
    public const RELEASED_AT = 'released_at';
    public const REFUNDED_AT = 'refunded_at';
    public const AUTO_RELEASE_AT = 'auto_release_at';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const DELETED_AT = 'deleted_at';

    protected $table = self::TABLE;

    protected $fillable = [
        self::JOB_REQUEST_ID,
        self::CLIENT_ID,
        self::MASTER_ID,
        self::AMOUNT,
        self::STATUS,
        self::PAYMENT_REFERENCE,
        self::HELD_AT,
        self::RELEASED_AT,
        self::REFUNDED_AT,
        self::AUTO_RELEASE_AT,
    ];

    protected $casts = [
        self::STATUS => EscrowStatusEnum::class,
        self::AMOUNT => 'decimal:2',
        self::HELD_AT => 'datetime',
        self::RELEASED_AT => 'datetime',
        self::REFUNDED_AT => 'datetime',
        self::AUTO_RELEASE_AT => 'datetime',
    ];

    public function getId(): int { return $this->getAttribute(self::ID); }
    public function getJobRequestId(): int { return $this->getAttribute(self::JOB_REQUEST_ID); }
    public function getClientId(): int { return $this->getAttribute(self::CLIENT_ID); }
    public function getMasterId(): int { return $this->getAttribute(self::MASTER_ID); }
    public function getAmount(): string { return $this->getAttribute(self::AMOUNT); }
    public function getStatus(): EscrowStatusEnum { return $this->getAttribute(self::STATUS); }
    public function getPaymentReference(): ?string { return $this->getAttribute(self::PAYMENT_REFERENCE); }
    public function getHeldAt(): ?Carbon { return $this->getAttribute(self::HELD_AT); }
    public function getReleasedAt(): ?Carbon { return $this->getAttribute(self::RELEASED_AT); }
    public function getRefundedAt(): ?Carbon { return $this->getAttribute(self::REFUNDED_AT); }
    public function getAutoReleaseAt(): ?Carbon { return $this->getAttribute(self::AUTO_RELEASE_AT); }

    public function jobRequest(): BelongsTo
    {
        return $this->belongsTo(JobRequest::class, self::JOB_REQUEST_ID, JobRequest::ID);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, self::CLIENT_ID, User::ID);
    }

    public function master(): BelongsTo
    {
        return $this->belongsTo(User::class, self::MASTER_ID, User::ID);
    }
}
