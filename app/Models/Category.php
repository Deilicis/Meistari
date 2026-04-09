<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use Auditable, HasFactory;

    public const TABLE = 'categories';
    public const ID = 'id';
    public const NAME = 'name';
    public const SLUG = 'slug';
    public const ICON = 'icon';
    public const PARENT_ID = 'parent_id';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    protected $table = self::TABLE;

    protected $fillable = [
        self::NAME,
        self::SLUG,
        self::ICON,
        self::PARENT_ID,
    ];

    public function getId(): int
    {
        return $this->getAttribute(self::ID);
    }

    public function getName(): string
    {
        return $this->getAttribute(self::NAME);
    }

    public function getSlug(): string
    {
        return $this->getAttribute(self::SLUG);
    }

    public function getIcon(): ?string
    {
        return $this->getAttribute(self::ICON);
    }

    public function getParentId(): ?int
    {
        return $this->getAttribute(self::PARENT_ID);
    }

    public function getCreatedAt(): ?Carbon
    {
        return $this->getAttribute(self::CREATED_AT);
    }

    public function getUpdatedAt(): ?Carbon
    {
        return $this->getAttribute(self::UPDATED_AT);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, self::PARENT_ID, self::ID);
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, self::PARENT_ID, self::ID);
    }

    public function jobRequests(): HasMany
    {
        return $this->hasMany(JobRequest::class, JobRequest::CATEGORY_ID, self::ID);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, Service::CATEGORY_ID, self::ID);
    }
}