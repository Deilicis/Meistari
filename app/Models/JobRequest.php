<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use App\Enums\Job\JobStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobRequest extends Model
{
    use SoftDeletes, HasFactory;

    public const TABLE = 'job_requests';
    public const ID = 'id';
    public const USER_ID = 'user_id';
    public const CATEGORY_ID = 'category_id';
    public const SLUG = 'slug';
    public const TITLE = 'title';
    public const DESCRIPTION = 'description';
    public const BUDGET = 'budget';
    public const DEADLINE = 'deadline';
    public const LOCATION = 'location';
    public const IMAGES = 'images';
    public const STATUS = 'status';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const DELETED_AT = 'deleted_at';

    public const STORAGE_DISK = 'public';
    public const IMAGES_DIR   = 'job-requests';

    private const ARRAY_CAST = 'array';

    protected $table = self::TABLE;

    protected $fillable = [
        self::USER_ID,
        self::CATEGORY_ID,
        self::SLUG,
        self::TITLE,
        self::DESCRIPTION,
        self::BUDGET,
        self::DEADLINE,
        self::LOCATION,
        self::IMAGES,
        self::STATUS,
    ];

    protected $casts = [
        self::STATUS => JobStatusEnum::class,
        self::LOCATION => self::ARRAY_CAST,
        self::IMAGES => self::ARRAY_CAST,
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

    public function getDescription(): string
    {
        return $this->getAttribute(self::DESCRIPTION);
    }

    public function getBudget(): ?float
    {
        $budget = $this->getAttribute(self::BUDGET);
        return $budget !== null ? (float) $budget : null;
    }

    public function getLocation(): array
    {
        return $this->getAttribute(self::LOCATION) ?? [];
    }

    public function getLocationAsString(): string
    {
        return implode(', ', $this->getLocation());
    }

    public function getDeadline(): ?string
    {
        return $this->getAttribute(self::DEADLINE);
    }

    public function getStatus(): JobStatusEnum
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

    public function getImages(): array
    {
        return $this->getAttribute(self::IMAGES) ?? [];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, Review::JOB_REQUEST_ID, self::ID);
    }
}