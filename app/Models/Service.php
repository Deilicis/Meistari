<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use App\Enums\Service\ServicePriceTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use SoftDeletes, HasFactory;

    public const TABLE = 'services';
    public const ID = 'id';
    public const USER_ID = 'user_id';
    public const CATEGORY_ID = 'category_id';
    public const TITLE = 'title';
    public const SLUG = 'slug';
    public const DESCRIPTION = 'description';
    public const PRICE = 'price';
    public const PRICE_TYPE = 'price_type';
    public const LOCATION = 'location';
    public const IS_ACTIVE = 'is_active';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const DELETED_AT = 'deleted_at';
    private const DECIMAL = 'decimal:2';
    private const BOOLEAN = 'boolean';
    private const ARRAY_CAST = 'array';

    protected $table = self::TABLE;

    protected $fillable = [
        self::USER_ID,
        self::CATEGORY_ID,
        self::TITLE,
        self::SLUG,
        self::DESCRIPTION,
        self::PRICE,
        self::PRICE_TYPE,
        self::LOCATION,
        self::IS_ACTIVE,
    ];

    protected $casts = [
        self::PRICE => self::DECIMAL,
        self::PRICE_TYPE => ServicePriceTypeEnum::class,
        self::IS_ACTIVE => self::BOOLEAN,
        self::LOCATION => self::ARRAY_CAST,
    ];

    public function getId(): int
    {
        return $this->getAttribute(self::ID);
    }

    public function getUserId(): int
    {
        return $this->getAttribute(self::USER_ID);
    }

    public function getCategoryId(): int
    {
        return $this->getAttribute(self::CATEGORY_ID);
    }

    public function getTitle(): string
    {
        return $this->getAttribute(self::TITLE);
    }

    public function getSlug(): string
    {
        return $this->getAttribute(self::SLUG);
    }

    public function getDescription(): string
    {
        return $this->getAttribute(self::DESCRIPTION);
    }

    public function getPrice(): ?float
    {
        return $this->getAttribute(self::PRICE) !== null 
            ? (float) $this->getAttribute(self::PRICE) 
            : null;
    }

    public function getPriceType(): ServicePriceTypeEnum
    {
        return $this->getAttribute(self::PRICE_TYPE);
    }

    public function getLocation(): array
    {
        return $this->getAttribute(self::LOCATION) ?? [];
    }

    public function getIsActive(): bool
    {
        return $this->getAttribute(self::IS_ACTIVE);
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, self::USER_ID, User::ID);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, self::CATEGORY_ID, Category::ID);
    }
}