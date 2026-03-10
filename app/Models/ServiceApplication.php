<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Job\ApplicationStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceApplication extends Model
{
    use HasFactory, SoftDeletes;

    public const TABLE = 'service_applications';
    public const ID = 'id';
    public const SERVICE_ID = 'service_id';
    public const USER_ID = 'user_id';
    public const MESSAGE = 'message';
    public const BUDGET_OFFER = 'budget_offer';
    public const STATUS = 'status';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const DELETED_AT = 'deleted_at';

    private const DECIMAL = 'decimal:2';

    protected $table = self::TABLE;

    protected $fillable = [
        self::SERVICE_ID,
        self::USER_ID,
        self::MESSAGE,
        self::BUDGET_OFFER,
        self::STATUS,
    ];

    protected $casts = [
        self::BUDGET_OFFER => self::DECIMAL,
        self::STATUS => ApplicationStatusEnum::class,
    ];

    public function getId(): int
    {
        return $this->getAttribute(self::ID);
    }

    public function getServiceId(): int
    {
        return $this->getAttribute(self::SERVICE_ID);
    }

    public function getUserId(): int
    {
        return $this->getAttribute(self::USER_ID);
    }

    public function getMessage(): string
    {
        return $this->getAttribute(self::MESSAGE);
    }

    public function getBudgetOffer(): ?float
    {
        $value = $this->getAttribute(self::BUDGET_OFFER);
        return $value !== null ? (float) $value : null;
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

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, self::SERVICE_ID, Service::ID);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, self::USER_ID, User::ID);
    }
}
