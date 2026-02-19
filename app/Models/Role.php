<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use App\Enums\Role\RoleNameEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    public const TABLE = 'roles';
    public const ID = 'id';
    public const NAME = 'name';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    protected $table = self::TABLE;

    protected $fillable = [
        self::NAME,
    ];

    protected $casts = [
        self::NAME => RoleNameEnum::class,
    ];

    public function getId(): int
    {
        return $this->getAttribute(self::ID);
    }
    
    public function getName(): RoleNameEnum
    {
        return $this->getAttribute(self::NAME);
    }

    public function getCreatedAt(): ?Carbon
    {
        return $this->getAttribute(self::CREATED_AT);
    }

    public function getUpdatedAt(): ?Carbon
    {
        return $this->getAttribute(self::UPDATED_AT);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class, 
            RoleUserPivot::TABLE, 
            RoleUserPivot::ROLE_ID, 
            RoleUserPivot::USER_ID
        );
    }
}