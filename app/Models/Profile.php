<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use App\Enums\Profile\ProfileTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;

    public const TABLE = 'profiles';
    public const ID = 'id';
    public const USER_ID = 'user_id';
    public const TYPE = 'type';
    public const FIRST_NAME = 'first_name';
    public const LAST_NAME = 'last_name';
    public const COMPANY_NAME = 'company_name';
    public const REG_NUMBER = 'reg_number';
    public const VAT_NUMBER = 'vat_number';
    public const CITY = 'city';
    public const PHONE = 'phone';
    public const BIO = 'bio';
    public const IS_VERIFIED = 'is_verified';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    private const BOOLEAN = 'boolean';

    protected $table = self::TABLE;

    protected $fillable = [
        self::USER_ID,
        self::TYPE,
        self::FIRST_NAME,
        self::LAST_NAME,
        self::COMPANY_NAME,
        self::REG_NUMBER,
        self::VAT_NUMBER,
        self::CITY,
        self::PHONE,
        self::BIO,
        self::IS_VERIFIED,
    ];

    protected $casts = [
        self::TYPE => ProfileTypeEnum::class,
        self::IS_VERIFIED => self::BOOLEAN,
    ];

    public function getId(): int
    {
        return $this->getAttribute(self::ID);
    }

    public function getUserId(): int
    {
        return $this->getAttribute(self::USER_ID);
    }

    public function getType(): ProfileTypeEnum
    {
        return $this->getAttribute(self::TYPE);
    }

    public function getFirstName(): ?string
    {
        return $this->getAttribute(self::FIRST_NAME);
    }

    public function getLastName(): ?string
    {
        return $this->getAttribute(self::LAST_NAME);
    }

    public function getCompanyName(): ?string
    {
        return $this->getAttribute(self::COMPANY_NAME);
    }

    public function getRegNumber(): ?string
    {
        return $this->getAttribute(self::REG_NUMBER);
    }

    public function getVatNumber(): ?string
    {
        return $this->getAttribute(self::VAT_NUMBER);
    }

    public function getCity(): string
    {
        return $this->getAttribute(self::CITY);
    }

    public function getPhone(): ?string
    {
        return $this->getAttribute(self::PHONE);
    }

    public function getBio(): ?string
    {
        return $this->getAttribute(self::BIO);
    }

    public function getIsVerified(): bool
    {
        return $this->getAttribute(self::IS_VERIFIED);
    }

    public function getCreatedAt(): ?Carbon
    {
        return $this->getAttribute(self::CREATED_AT);
    }

    public function getUpdatedAt(): ?Carbon
    {
        return $this->getAttribute(self::UPDATED_AT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, self::USER_ID, User::ID);
    }
}