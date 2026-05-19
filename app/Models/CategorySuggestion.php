<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CategorySuggestionStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategorySuggestion extends Model
{
    use SoftDeletes;

    public const TABLE = 'category_suggestions';
    public const ID = 'id';
    public const SUGGESTED_BY_USER_ID = 'suggested_by_user_id';
    public const NAME = 'name';
    public const PARENT_CATEGORY_ID = 'parent_category_id';
    public const NOTE = 'note';
    public const STATUS = 'status';
    public const REVIEWED_BY_USER_ID = 'reviewed_by_user_id';
    public const REVIEWED_AT = 'reviewed_at';
    public const REVIEW_NOTE = 'review_note';
    public const RESULTING_CATEGORY_ID = 'resulting_category_id';
    public const DELETED_AT = 'deleted_at';

    protected $table = self::TABLE;

    protected $fillable = [
        self::SUGGESTED_BY_USER_ID,
        self::NAME,
        self::PARENT_CATEGORY_ID,
        self::NOTE,
        self::STATUS,
        self::REVIEWED_BY_USER_ID,
        self::REVIEWED_AT,
        self::REVIEW_NOTE,
        self::RESULTING_CATEGORY_ID,
    ];

    protected $casts = [
        self::STATUS => CategorySuggestionStatusEnum::class,
        self::REVIEWED_AT => 'datetime',
    ];

    public function getId(): int
    {
        return $this->getAttribute(self::ID);
    }

    public function getSuggestedByUserId(): int
    {
        return $this->getAttribute(self::SUGGESTED_BY_USER_ID);
    }

    public function getName(): string
    {
        return $this->getAttribute(self::NAME);
    }

    public function getParentCategoryId(): ?int
    {
        return $this->getAttribute(self::PARENT_CATEGORY_ID);
    }

    public function getNote(): ?string
    {
        return $this->getAttribute(self::NOTE);
    }

    public function getStatus(): CategorySuggestionStatusEnum
    {
        return $this->getAttribute(self::STATUS);
    }

    public function getReviewedByUserId(): ?int
    {
        return $this->getAttribute(self::REVIEWED_BY_USER_ID);
    }

    public function getReviewedAt(): ?Carbon
    {
        return $this->getAttribute(self::REVIEWED_AT);
    }

    public function getReviewNote(): ?string
    {
        return $this->getAttribute(self::REVIEW_NOTE);
    }

    public function getResultingCategoryId(): ?int
    {
        return $this->getAttribute(self::RESULTING_CATEGORY_ID);
    }

    public function getCreatedAt(): ?Carbon
    {
        return $this->getAttribute(self::CREATED_AT);
    }

    public function isPending(): bool
    {
        return $this->getStatus() === CategorySuggestionStatusEnum::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->getStatus() === CategorySuggestionStatusEnum::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->getStatus() === CategorySuggestionStatusEnum::REJECTED;
    }

    public function isMerged(): bool
    {
        return $this->getStatus() === CategorySuggestionStatusEnum::MERGED;
    }

    public function suggestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, self::SUGGESTED_BY_USER_ID, User::ID);
    }

    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, self::PARENT_CATEGORY_ID, Category::ID);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, self::REVIEWED_BY_USER_ID, User::ID);
    }

    public function resultingCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, self::RESULTING_CATEGORY_ID, Category::ID);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, Service::PENDING_CATEGORY_SUGGESTION_ID, self::ID);
    }

    public function jobRequests(): HasMany
    {
        return $this->hasMany(JobRequest::class, JobRequest::PENDING_CATEGORY_SUGGESTION_ID, self::ID);
    }
}
