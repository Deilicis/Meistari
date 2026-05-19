<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MessageTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use SoftDeletes, HasFactory;

    public const TABLE = 'messages';
    public const ID = 'id';
    public const CONVERSATION_ID = 'conversation_id';
    public const SENDER_ID = 'sender_id';
    public const BODY = 'body';
    public const TYPE = 'type';
    public const PROPOSAL_ID = 'proposal_id';
    public const READ_AT = 'read_at';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const DELETED_AT = 'deleted_at';
    private const DATETIME = 'datetime';

    protected $table = self::TABLE;

    protected $fillable = [
        self::CONVERSATION_ID,
        self::SENDER_ID,
        self::BODY,
        self::TYPE,
        self::PROPOSAL_ID,
        self::READ_AT,
    ];

    protected $casts = [
        self::READ_AT => self::DATETIME,
        self::TYPE => MessageTypeEnum::class,
    ];

    public function getId(): int
    {
        return $this->getAttribute(self::ID);
    }

    public function getConversationId(): int
    {
        return $this->getAttribute(self::CONVERSATION_ID);
    }

    public function getSenderId(): int
    {
        return $this->getAttribute(self::SENDER_ID);
    }

    public function getBody(): string
    {
        return $this->getAttribute(self::BODY) ?? '';
    }

    public function getType(): MessageTypeEnum
    {
        return $this->getAttribute(self::TYPE) ?? MessageTypeEnum::TEXT;
    }

    public function getProposalId(): ?int
    {
        return $this->getAttribute(self::PROPOSAL_ID);
    }

    public function getReadAt(): ?Carbon
    {
        return $this->getAttribute(self::READ_AT);
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

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, self::CONVERSATION_ID, Conversation::ID);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, self::SENDER_ID, User::ID);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(PriceProposal::class, self::PROPOSAL_ID, PriceProposal::ID);
    }
}