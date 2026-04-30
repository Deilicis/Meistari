<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NotificationTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes;

    public const TABLE      = 'notifications';
    public const ID         = 'id';
    public const USER_ID    = 'user_id';
    public const TYPE       = 'type';
    public const TITLE      = 'title';
    public const BODY       = 'body';
    public const ACTION_URL = 'action_url';
    public const METADATA   = 'metadata';
    public const READ_AT    = 'read_at';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const DELETED_AT = 'deleted_at';

    protected $table = self::TABLE;

    protected $fillable = [
        self::USER_ID,
        self::TYPE,
        self::TITLE,
        self::BODY,
        self::ACTION_URL,
        self::METADATA,
        self::READ_AT,
    ];

    protected $casts = [
        self::TYPE     => NotificationTypeEnum::class,
        self::METADATA => 'array',
        self::READ_AT  => 'datetime',
    ];

    public function getId(): int
    {
        return $this->getAttribute(self::ID);
    }

    public function getUserId(): int
    {
        return $this->getAttribute(self::USER_ID);
    }

    public function getType(): NotificationTypeEnum
    {
        return $this->getAttribute(self::TYPE);
    }

    public function getTitle(): string
    {
        return $this->getAttribute(self::TITLE);
    }

    public function getBody(): ?string
    {
        return $this->getAttribute(self::BODY);
    }

    public function getActionUrl(): ?string
    {
        return $this->getAttribute(self::ACTION_URL);
    }

    public function getMetadata(): ?array
    {
        return $this->getAttribute(self::METADATA);
    }

    public function getReadAt(): ?Carbon
    {
        return $this->getAttribute(self::READ_AT);
    }

    public function getCreatedAt(): ?Carbon
    {
        return $this->getAttribute(self::CREATED_AT);
    }

    public function isRead(): bool
    {
        return $this->getReadAt() !== null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, self::USER_ID, User::ID);
    }
}
