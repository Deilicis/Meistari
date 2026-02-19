<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use SoftDeletes;

    public const TABLE = 'conversations';
    public const ID = 'id';
    public const SENDER_ID = 'sender_id';
    public const RECEIVER_ID = 'receiver_id';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const DELETED_AT = 'deleted_at';

    protected $table = self::TABLE;

    protected $fillable = [
        self::SENDER_ID,
        self::RECEIVER_ID,
    ];

    public function getId(): int
    {
        return $this->getAttribute(self::ID);
    }

    public function getSenderId(): int
    {
        return $this->getAttribute(self::SENDER_ID);
    }

    public function getReceiverId(): int
    {
        return $this->getAttribute(self::RECEIVER_ID);
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

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, self::SENDER_ID, User::ID);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, self::RECEIVER_ID, User::ID);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, Message::CONVERSATION_ID, self::ID);
    }
}