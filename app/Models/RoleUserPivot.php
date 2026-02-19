<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RoleUserPivot extends Pivot
{
    public const TABLE = 'role_user';
    public const ID = 'id';
    public const USER_ID = 'user_id';
    public const ROLE_ID = 'role_id';

    protected $table = self::TABLE;

    public function getId(): int
    {
        return $this->getAttribute(self::ID);
    }

    public function getUserId(): int
    {
        return $this->getAttribute(self::USER_ID);
    }

    public function getRoleId(): int
    {
        return $this->getAttribute(self::ROLE_ID);
    }

    public function getCreatedAt(): ?Carbon
    {
        return $this->getAttribute(self::CREATED_AT);
    }

    public function getUpdatedAt(): ?Carbon
    {
        return $this->getAttribute(self::UPDATED_AT);
    }
}